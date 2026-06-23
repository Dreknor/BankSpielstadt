<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\WorkingTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminArbeitszeitController extends Controller
{
    /**
     * Übersicht: alle Personen (keine Betriebe) mit Arbeitszeit-Statistik.
     * Suche über GET-Parameter 'suche'.
     */
    public function index(Request $request)
    {
        $suche = $request->get('suche', '');

        // Alle nicht-Betriebe laden (mit eager-load working_times inkl. Betrieb)
        $query = Customer::where(function ($q) {
            $q->where('buisness', 0)->orWhereNull('buisness');
        })->with(['working_times.buisness']);

        if ($suche !== '') {
            $query->where('name', 'like', '%' . $suche . '%');
        }

        $customers = $query->get();

        // Pro Kunde die Statistik berechnen
        $statistiken = $customers->map(function (Customer $customer) {
            $times = $customer->working_times;

            if ($times->isEmpty()) {
                return [
                    'customer'         => $customer,
                    'tage_gesamt'      => 0,
                    'minuten_gesamt'   => 0,
                    'tage_unter_90min' => 0,
                    'tage_details'     => collect(),
                ];
            }

            // Gruppieren nach Datum (nur Datum, keine Uhrzeit)
            $nachTag = $times->groupBy(fn($wt) => $wt->start->toDateString());

            $tageDetails = $nachTag->map(function ($eintraege, $datum) {
                $minuten = $eintraege->sum('duration');
                return [
                    'datum'    => $datum,
                    'minuten'  => $minuten,
                    'eintraege' => $eintraege,
                    'warnung'  => $minuten < 90,
                ];
            })->sortByDesc('datum')->values();

            return [
                'customer'         => $customer,
                'tage_gesamt'      => $tageDetails->count(),
                'minuten_gesamt'   => $times->sum('duration'),
                'tage_unter_90min' => $tageDetails->where('warnung', true)->count(),
                'tage_details'     => $tageDetails,
            ];
        });

        // Sortierung: zuerst Personen mit Warnungen (tage_unter_90min > 0), dann alphabetisch
        $statistiken = $statistiken->sortByDesc('tage_unter_90min')->values();

        return view('admin.arbeitszeiten.index', [
            'statistiken' => $statistiken,
            'suche'       => $suche,
        ]);
    }

    /**
     * Detail-Ansicht: Arbeitszeit-Historie einer einzelnen Person.
     */
    public function person(Customer $customer)
    {
        $times = $customer->working_times()->with('buisness')->orderBy('start', 'desc')->get();

        // Gruppieren nach Datum
        $nachTag = $times->groupBy(fn($wt) => $wt->start->toDateString());

        $tageDetails = $nachTag->map(function ($eintraege, $datum) {
            $minuten = $eintraege->sum('duration');
            return [
                'datum'    => Carbon::parse($datum),
                'minuten'  => $minuten,
                'stunden'  => floor($minuten / 60),
                'restmin'  => $minuten % 60,
                'eintraege' => $eintraege->sortBy('start'),
                'warnung'  => $minuten < 90,
            ];
        })->sortByDesc('datum')->values();

        $gesamtMinuten = $times->sum('duration');

        return view('admin.arbeitszeiten.person', [
            'customer'      => $customer,
            'tageDetails'   => $tageDetails,
            'gesamtMinuten' => $gesamtMinuten,
            'gesamtStunden' => floor($gesamtMinuten / 60),
            'gesamtRestMin' => $gesamtMinuten % 60,
        ]);
    }

    /**
     * Bearbeitungsformular für einen einzelnen Eintrag (Admin).
     */
    public function editForm(Customer $customer, WorkingTime $wt)
    {
        $betriebe = Customer::query()->buisness()->get();
        return view('admin.arbeitszeiten.edit', compact('customer', 'wt', 'betriebe'));
    }

    /**
     * Speichert die Korrektur: löscht alte Zahlungen, erstellt neue.
     */
    public function update(Request $request, Customer $customer, WorkingTime $wt)
    {
        $request->validate([
            'datum'        => 'required|date',
            'start_hour'   => 'required|integer|min:0|max:23',
            'start_minute' => 'required|integer|min:0|max:59',
            'end_hour'     => 'required|integer|min:0|max:23',
            'end_minute'   => 'required|integer|min:0|max:59',
            'buisness_id'  => 'required|integer|exists:customers,id',
            'manager'      => 'required|integer|min:0|max:1',
        ]);

        $neuerLohn = 0;

        // Überschneidung mit anderen Arbeitszeiten des Kunden prüfen (aktuellen Eintrag ausschließen)
        $start_check = Carbon::parse($request->datum)->setHour($request->start_hour)->setMinute($request->start_minute)->setSecond(0);
        $end_check   = Carbon::parse($request->datum)->setHour($request->end_hour)->setMinute($request->end_minute)->setSecond(0);

        $ueberschneidung = WorkingTime::where('customer_id', $customer->id)
            ->where('id', '!=', $wt->id)
            ->where('start', '<', $end_check)
            ->where('end', '>', $start_check)
            ->first();

        if ($ueberschneidung !== null) {
            return redirect()->back()->withInput()->with([
                'type'    => 'danger',
                'Meldung' => 'Die korrigierte Arbeitszeit überschneidet sich mit einem anderen Eintrag ('
                    . $ueberschneidung->start->format('H:i') . ' – '
                    . $ueberschneidung->end->format('H:i') . ' Uhr). Bitte korrigiere die Zeiten.',
            ]);
        }

        DB::transaction(function () use ($request, $customer, $wt, &$neuerLohn) {
            $start   = Carbon::parse($request->datum)->setHour($request->start_hour)->setMinute($request->start_minute)->setSecond(0);
            $end     = Carbon::parse($request->datum)->setHour($request->end_hour)->setMinute($request->end_minute)->setSecond(0);
            $betrieb = Customer::findOrFail($request->buisness_id);

            $dauer   = max(0, $start->diffInMinutes($end));
            $StdLohn = $request->manager == 1
                ? (int) config('bank.lohn.chef', 7)
                : (int) config('bank.lohn.mitarbeiter', 6);
            $neuerLohn = (int) floor(($dauer / 60) * $StdLohn);

            // Alte Zahlungen soft-löschen
            if ($wt->payment_customer) {
                Payment::find($wt->payment_customer)?->delete();
            }
            if ($wt->payment_buisness) {
                Payment::find($wt->payment_buisness)?->delete();
            }

            // Neue Zahlungen anlegen
            $payBetrieb = Payment::create([
                'customer_id' => $betrieb->id,
                'amount'      => -$neuerLohn,
                'comment'     => "Lohn (Admin-Korr.): {$customer->name} ({$start->format('d.m.Y H:i')}–{$end->format('H:i')})",
                'user_id'     => auth()->id() ?? 1,
            ]);
            $payCustomer = Payment::create([
                'customer_id' => $customer->id,
                'amount'      => $neuerLohn,
                'source_id'   => $betrieb->id,
                'comment'     => "Lohn (Admin-Korr.): {$betrieb->name} ({$start->format('d.m.Y H:i')}–{$end->format('H:i')})",
                'user_id'     => auth()->id() ?? 1,
                'payment_id'  => $payBetrieb->id,
            ]);
            $payBetrieb->update(['payment_id' => $payCustomer->id]);

            // Arbeitszeit aktualisieren
            $wt->update([
                'start'            => $start,
                'end'              => $end,
                'is_manager'       => $request->manager,
                'buisness_id'      => $betrieb->id,
                'payment_customer' => $payCustomer->id,
                'payment_buisness' => $payBetrieb->id,
                'user_id'          => auth()->id() ?? 1,
            ]);
        });

        return redirect()->route('admin.arbeitszeiten.person', $customer)
            ->with(['type' => 'success', 'Meldung' => "Arbeitszeit korrigiert. Neuer Lohn: {$neuerLohn} Radi."]);
    }

    /**
     * Löscht einen Arbeitszeit-Eintrag inkl. zugehöriger Zahlungen (Admin).
     */
    public function destroy(Customer $customer, WorkingTime $wt)
    {
        DB::transaction(function () use ($wt) {
            if ($wt->payment_customer) {
                Payment::find($wt->payment_customer)?->delete();
            }
            if ($wt->payment_buisness) {
                Payment::find($wt->payment_buisness)?->delete();
            }
            $wt->delete();
        });

        return redirect()->route('admin.arbeitszeiten.person', $customer)
            ->with(['type' => 'warning', 'Meldung' => 'Arbeitszeit-Eintrag wurde gelöscht.']);
    }
}
