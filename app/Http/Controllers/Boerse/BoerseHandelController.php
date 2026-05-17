<?php

namespace App\Http\Controllers\Boerse;

use App\Http\Controllers\Controller;
use App\Http\Requests\AktienKaufRequest;
use App\Http\Requests\AktienVerkaufRequest;
use App\Http\Requests\AktienRueckkaufRequest;
use App\Models\AktienBestand;
use App\Models\AktienTransaktion;
use App\Models\BoerseKasse;
use App\Models\Customer;
use App\Models\Payment;
use App\Services\BoerseAufgabenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoerseHandelController extends Controller
{
    private function aufgabenStatus(): array
    {
        return app(BoerseAufgabenService::class)->status();
    }

    public function index()
    {
        $betriebe       = Customer::where('buisness', 1)->whereNotNull('aktien_gesamt')->get();
        $aufgabenStatus = $this->aufgabenStatus();
        return view('boerse.handel.index', compact('betriebe', 'aufgabenStatus'));
    }

    /** Autocomplete-Suche für Kinder (Kauf-Flow). JSON: id + name */
    public function searchKinder(\Illuminate\Http\Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if (mb_strlen($q) < 1) {
            return response()->json([]);
        }
        $kinder = Customer::where('buisness', 0)
            ->where('name', 'LIKE', '%' . $q . '%')
            ->limit(20)
            ->get(['id', 'name']);
        return response()->json($kinder);
    }

    /**
     * Autocomplete-Suche unter den aktuellen Anteilseignern eines Betriebs.
     * Wird beim Verkauf + Rückkauf benutzt. JSON: id + name + stueck
     */
    public function searchInhaber(\Illuminate\Http\Request $request, Customer $customer)
    {
        $q = trim((string) $request->get('q', ''));
        $query = AktienBestand::where('buisness_id', $customer->id)
            ->where('stueck', '>', 0)
            ->with('kind');
        if (mb_strlen($q) >= 1) {
            $query->whereHas('kind', fn($w) => $w->where('name', 'LIKE', '%' . $q . '%'));
        }
        $result = $query->limit(20)->get()->map(fn($b) => [
            'id'     => $b->customer_id,
            'name'   => $b->kind?->name ?? '—',
            'stueck' => $b->stueck,
        ]);
        return response()->json($result);
    }

    public function kaufenForm(Customer $customer)
    {
        abort_unless($customer->hatAktien(), 404);
        $aufgabenStatus = $this->aufgabenStatus();
        return view('boerse.handel.kaufen', compact('customer', 'aufgabenStatus'));
    }

    public function kaufen(AktienKaufRequest $request, Customer $customer)
    {
        abort_unless($customer->hatAktien(), 404);

        $stueck   = $request->stueck;
        $kurs     = $customer->aktien_kurs;
        $summe    = $stueck * $kurs;
        $gebuehr  = (int) config('bank.aktien.kauf_gebuehr', 1);
        $gesamt   = $summe + $gebuehr;
        $kind     = Customer::findOrFail($request->customer_id);

        // Börsen-Konto muss eingerichtet sein
        $boerse = Customer::boerseBetrieb();
        if (!$boerse) {
            return back()->with(['type' => 'error',
                'Meldung' => '⚠️ Kein Börsen-Konto eingerichtet! Bitte den Admin kontaktieren (Admin → Börse → Einstellungen).']);
        }

        // Eigenbestand prüfen
        if ($customer->anteileEigen() < $stueck) {
            return back()->with(['type' => 'error',
                'Meldung' => "Das {$customer->name} hat nicht genug freie Anteile! Noch verfügbar: {$customer->anteileEigen()}."]);
        }

        // Soft-Limit Warnung (kein harter Stop) – inkl. Gebühr
        $maxInvest = config('bank.aktien.max_bargeld_invest', 50);
        if ($gesamt > $maxInvest && !$request->bestaetigt) {
            return back()
                ->withInput()
                ->with(['type' => 'warning',
                    'Meldung' => "Das sind {$gesamt} Radi (inkl. {$gebuehr} Radi Gebühr) — das ist viel Geld! Bitte nochmal bestätigen.",
                    'bestaetigung_noetig' => true]);
        }

        DB::transaction(function () use ($customer, $kind, $stueck, $kurs, $summe, $gebuehr, $boerse) {
            $transaktion = AktienTransaktion::create([
                'customer_id' => $kind->id,
                'buisness_id' => $customer->id,
                'typ'         => 'kauf',
                'stueck'      => $stueck,
                'kurs'        => $kurs,
                'summe'       => $summe,
                'boerse_rolle'=> 'haendler',
                'notiz'       => "Kauf für {$kind->name}" . ($gebuehr > 0 ? " · Gebühr {$gebuehr} Radi" : ''),
            ]);

            // Bargeld-Tracking: Kaufpreis kommt physisch an der Kasse an
            BoerseKasse::create([
                'typ'                   => 'kauf_einnahme',
                'betrag'                => $summe,
                'notiz'                 => "{$stueck} Anteile {$customer->name}, Kind: {$kind->name}",
                'aktien_transaktion_id' => $transaktion->id,
                'created_at'            => now(),
            ]);

            // Börse behält die Kaufgebühr als Einnahme (Lohn für Angestellte)
            if ($gebuehr > 0) {
                BoerseKasse::create([
                    'typ'                   => 'gebuehr_einnahme',
                    'betrag'                => $gebuehr,
                    'notiz'                 => "Kaufgebühr ({$stueck} Anteile {$customer->name}, Kind: {$kind->name})",
                    'aktien_transaktion_id' => $transaktion->id,
                    'created_at'            => now(),
                ]);
            }

            // Verknüpfte Kontoeinträge: Betrieb +summe, Börse −summe
            // Das Bargeld liegt physisch bei der Börse, gehört buchhalterisch dem Betrieb.
            $payBetrieb = Payment::create([
                'customer_id' => $customer->id,
                'amount'      => $summe,
                'comment'     => "Börse: {$stueck} Anteile verkauft à {$kurs} Radi ({$kind->name})",
                'user_id'     => auth()->id() ?? 1,
            ]);
            $payBoerse = Payment::create([
                'customer_id' => $boerse->id,
                'amount'      => -$summe,
                'comment'     => "Börse: Anteilskauf {$stueck}× {$customer->name} à {$kurs} Radi ({$kind->name})",
                'payment_id'  => $payBetrieb->id,
                'user_id'     => auth()->id() ?? 1,
            ]);
            $payBetrieb->update(['payment_id' => $payBoerse->id]);

            $bestand = AktienBestand::firstOrCreate(
                ['customer_id' => $kind->id, 'buisness_id' => $customer->id],
                ['stueck' => 0]
            );
            $bestand->increment('stueck', $stueck);
        });

        $hinweis = $gebuehr > 0
            ? "{$kind->name} hat {$stueck} Anteile am {$customer->name} gekauft. Bezahlt: {$gesamt} Radi bar ({$summe} + {$gebuehr} Gebühr). ✅"
            : "{$kind->name} hat {$stueck} Anteile am {$customer->name} gekauft ({$summe} Radi bar). ✅";

        return redirect('/boerse/handel')
            ->with(['type' => 'success', 'Meldung' => $hinweis]);
    }

    public function verkaufenForm(Customer $customer)
    {
        abort_unless($customer->hatAktien(), 404);
        $kassenstand    = BoerseKasse::kassenstand();
        $aufgabenStatus = $this->aufgabenStatus();
        return view('boerse.handel.verkaufen', compact('customer', 'kassenstand', 'aufgabenStatus'));
    }

    public function verkaufen(AktienVerkaufRequest $request, Customer $customer)
    {
        abort_unless($customer->hatAktien(), 404);

        $stueck     = $request->stueck;
        $kurs       = $customer->aktien_kurs;
        $summe      = $stueck * $kurs;
        $kind       = Customer::findOrFail($request->customer_id);
        $bestand    = AktienBestand::where('customer_id', $kind->id)
            ->where('buisness_id', $customer->id)->first();

        if (!$bestand || $bestand->stueck < $stueck) {
            return back()->with(['type' => 'error',
                'Meldung' => "{$kind->name} hat nicht genug Anteile! Bestand: " . ($bestand?->stueck ?? 0) . "."]);
        }

        // Börsen-Konto muss eingerichtet sein
        $boerse = Customer::boerseBetrieb();
        if (!$boerse) {
            return back()->with(['type' => 'error',
                'Meldung' => '⚠️ Kein Börsen-Konto eingerichtet! Bitte den Admin kontaktieren.']);
        }

        $kassenstand = BoerseKasse::kassenstand();
        if ($kassenstand < $summe) {
            return back()->with(['type' => 'error',
                'Meldung' => "Die Börse hat nicht genug Bargeld ({$kassenstand} Radi)! Bitte den Kassenwart fragen."]);
        }

        // Betrieb muss genug auf dem Konto haben, um die Anteile zurückzuzahlen
        if ($customer->balance < $summe) {
            return back()->with(['type' => 'error',
                'Meldung' => "Das Konto von {$customer->name} hat nicht genug Radi ({$customer->balance} Radi), um die Anteile zurückzukaufen!"]);
        }

        DB::transaction(function () use ($request, $customer, $kind, $bestand, $stueck, $kurs, $summe, $boerse) {
            $transaktion = AktienTransaktion::create([
                'customer_id' => $kind->id,
                'buisness_id' => $customer->id,
                'typ'         => 'verkauf',
                'stueck'      => $stueck,
                'kurs'        => $kurs,
                'summe'       => $summe,
                'boerse_rolle'=> 'haendler',
            ]);

            // Bargeld-Tracking: Bargeld verlässt physisch die Kasse
            BoerseKasse::create([
                'typ'                   => 'verkauf_auszahlung',
                'betrag'                => $summe,
                'notiz'                 => "{$stueck} Anteile {$customer->name}, Kind: {$kind->name}",
                'aktien_transaktion_id' => $transaktion->id,
                'created_at'            => now(),
            ]);

            // Verknüpfte Kontoeinträge: Betrieb −summe, Börse +summe
            // Der Betrieb gibt das Investitionsgeld zurück; die Börse empfängt es buchhalterisch.
            $payBoerse = Payment::create([
                'customer_id' => $boerse->id,
                'amount'      => $summe,
                'comment'     => "Börse: Anteilsrückkauf {$stueck}× {$customer->name} à {$kurs} Radi ({$kind->name})",
                'user_id'     => auth()->id() ?? 1,
            ]);
            $payBetrieb = Payment::create([
                'customer_id' => $customer->id,
                'amount'      => -$summe,
                'comment'     => "Börse: {$stueck} Anteile zurückgekauft à {$kurs} Radi ({$kind->name})",
                'payment_id'  => $payBoerse->id,
                'user_id'     => auth()->id() ?? 1,
            ]);
            $payBoerse->update(['payment_id' => $payBetrieb->id]);

            $bestand->decrement('stueck', $stueck);
        });

        return redirect('/boerse/handel')
            ->with(['type' => 'success',
                'Meldung' => "{$kind->name} hat {$stueck} Anteile verkauft und bekommt {$summe} Radi bar. 💵"]);
    }

    public function rueckkaufForm(Customer $customer)
    {
        abort_unless($customer->hatAktien(), 404);
        $aufgabenStatus = $this->aufgabenStatus();
        return view('boerse.handel.rueckkauf', compact('customer', 'aufgabenStatus'));
    }

    public function rueckkauf(AktienRueckkaufRequest $request, Customer $customer)
    {
        abort_unless($customer->hatAktien(), 404);

        $stueck  = $request->stueck;
        $kurs    = $customer->aktien_kurs;
        $summe   = $stueck * $kurs;
        $kind    = Customer::findOrFail($request->customer_id);
        $bestand = AktienBestand::where('customer_id', $kind->id)
            ->where('buisness_id', $customer->id)->first();

        if (!$bestand || $bestand->stueck < $stueck) {
            return back()->with(['type' => 'error',
                'Meldung' => "{$kind->name} hat nicht genug Anteile!"]);
        }

        // Eigenbestand darf aktien_gesamt nicht überschreiten
        if ($customer->anteileEigen() + $stueck > $customer->aktien_gesamt) {
            return back()->with(['type' => 'error',
                'Meldung' => 'Rückkauf würde den Eigenbestand über die Gesamtstückzahl treiben.']);
        }

        DB::transaction(function () use ($customer, $kind, $bestand, $stueck, $kurs, $summe) {
            $transaktion = AktienTransaktion::create([
                'customer_id' => $kind->id,
                'buisness_id' => $customer->id,
                'typ'         => 'rueckkauf',
                'stueck'      => $stueck,
                'kurs'        => $kurs,
                'summe'       => $summe,
                'boerse_rolle'=> 'haendler',
                'notiz'       => "Rückkauf durch {$customer->name}",
            ]);

            // Einnahme vom Betrieb
            BoerseKasse::create([
                'typ'                   => 'rueckkauf_einnahme',
                'betrag'                => $summe,
                'notiz'                 => "{$stueck} Anteile, Betrieb: {$customer->name}, Kind: {$kind->name}",
                'aktien_transaktion_id' => $transaktion->id,
                'created_at'            => now(),
            ]);
            // Sofortige Auszahlung an Kind (Netto 0)
            BoerseKasse::create([
                'typ'                   => 'rueckkauf_auszahlung',
                'betrag'                => $summe,
                'notiz'                 => "{$stueck} Anteile, Betrieb: {$customer->name}, Kind: {$kind->name}",
                'aktien_transaktion_id' => $transaktion->id,
                'created_at'            => now(),
            ]);

            $bestand->decrement('stueck', $stueck);
        });

        return redirect('/boerse/handel')
            ->with(['type' => 'success',
                'Meldung' => "{$customer->name} hat {$stueck} Anteile von {$kind->name} zurückgekauft. {$kind->name} bekommt {$summe} Radi bar. ✅"]);
    }
}

