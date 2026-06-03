<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Hilferuf;
use App\Models\KasseTransaktion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBetriebController extends Controller
{
    /** PIN-Übersicht und PIN setzen */
    public function pinIndex()
    {
        $betriebe = Customer::buisness()->get();
        return view('admin.betrieb_pin', compact('betriebe'));
    }

    public function pinStore(Request $request)
    {
        $request->validate([
            'betrieb_id' => 'required|exists:customers,id',
            'pin'        => 'required|string|min:4|max:20',
        ]);

        $betrieb = Customer::findOrFail($request->betrieb_id);
        abort_if(! $betrieb->is_buisness(), 403);
        $betrieb->update(['betrieb_pin' => $request->pin]);

        return back()->with(['type' => 'success', 'Meldung' => 'PIN für ' . $betrieb->name . ' gesetzt.']);
    }

    /** Kassenjournal eines Betriebs (Admin) */
    public function kasse(Request $request, Customer $customer)
    {
        abort_if(! $customer->is_buisness(), 404);

        $datum = $request->input('datum', Carbon::today()->toDateString());

        $transaktionen = KasseTransaktion::with('positionen.product')
            ->where('customer_id', $customer->id)
            ->whereDate('created_at', $datum)
            ->orderByDesc('created_at')
            ->get();

        $summen = [
            'einlagen'  => $transaktionen->where('type', 'einlage')->sum('amount'),
            'verkaeufe' => $transaktionen->where('type', 'verkauf')->sum('amount'),
            'entnahmen' => $transaktionen->where('type', 'entnahme')->sum('amount'),
        ];

        $kassenbestand = $customer->kassenbestand();
        $betrieb = $customer;

        return view('admin.betrieb_kasse', compact('betrieb', 'transaktionen', 'summen', 'kassenbestand', 'datum'));
    }

    /** Buchung löschen (Admin) */
    public function deleteTransaktion(KasseTransaktion $transaktion)
    {
        $betrieb = $transaktion->customer;
        $transaktion->positionen()->delete();
        $transaktion->delete();

        return back()->with(['type' => 'warning', 'Meldung' => 'Buchung gelöscht.']);
    }

    /** Produkte eines Betriebs (Admin, nur lesen) */
    public function produkte(Customer $customer)
    {
        abort_if(! $customer->is_buisness(), 404);
        $produkte = $customer->products()->withTrashed()->orderBy('name')->get();
        $betrieb  = $customer;
        return view('admin.betrieb_produkte', compact('betrieb', 'produkte'));
    }

    /** Fotostudio-Einstellungen anzeigen */
    public function fotostudio(Customer $customer)
    {
        abort_if(! $customer->is_buisness(), 404);
        $betrieb = $customer;
        $bilder  = $customer->fotostudioBilder()->orderBy('reihenfolge')->orderBy('created_at')->get();
        return view('admin.betrieb_fotostudio', compact('betrieb', 'bilder'));
    }

    /** Börse-Einstellungen eines Betriebs (Admin) */
    public function boerse(Customer $customer)
    {
        abort_if(! $customer->is_buisness(), 404);
        $betrieb      = $customer;
        $boerseBetrieb = Customer::boerseBetrieb();
        return view('admin.betrieb_boerse', compact('betrieb', 'boerseBetrieb'));
    }

    /** Betrieb als Börse markieren / Markierung entfernen */
    public function boerseStore(Request $request, Customer $customer)
    {
        abort_if(! $customer->is_buisness(), 404);
        $request->validate(['aktion' => 'required|in:aktivieren,deaktivieren']);

        if ($request->input('aktion') === 'aktivieren') {
            abort_if(! $customer->betrieb_pin, 422, 'Der Betrieb benötigt zuerst einen Betriebs-PIN.');

            // Bisherige Börse-Markierung zurücksetzen (nur einer möglich)
            Customer::where('is_boerse', true)->update(['is_boerse' => false]);
            $customer->update(['is_boerse' => true]);

            return back()->with([
                'type'    => 'success',
                'Meldung' => $customer->name . ' ist jetzt als Börse markiert. Mitarbeiter kommen per Betriebs-PIN ins Börsen-Frontend.',
            ]);
        }

        // Deaktivieren
        $customer->update(['is_boerse' => false]);
        return back()->with([
            'type'    => 'warning',
            'Meldung' => 'Börse-Markierung für ' . $customer->name . ' entfernt.',
        ]);
    }

    /** Fotostudio aktivieren / deaktivieren / Token erneuern */
    public function fotostudioStore(Request $request, Customer $customer)
    {
        abort_if(! $customer->is_buisness(), 404);
        $request->validate(['aktion' => 'required|in:aktivieren,deaktivieren,token_neu']);

        switch ($request->input('aktion')) {
            case 'aktivieren':
                $customer->update([
                    'is_fotostudio'    => true,
                    'fotostudio_token' => Str::random(48),
                ]);
                return back()->with(['type' => 'success', 'Meldung' => 'Fotostudio für ' . $customer->name . ' aktiviert.']);

            case 'deaktivieren':
                $customer->update([
                    'is_fotostudio'    => false,
                    'fotostudio_token' => null,
                ]);
                return back()->with(['type' => 'warning', 'Meldung' => 'Fotostudio für ' . $customer->name . ' deaktiviert.']);

            case 'token_neu':
                $customer->update(['fotostudio_token' => Str::random(48)]);
                return back()->with(['type' => 'success', 'Meldung' => 'Neuer Slideshow-Link generiert.']);
        }

        return back();
    }

    /** Hilferufe-Übersicht (Admin) */
    public function hilferufe(Request $request)
    {
        $status = $request->input('status', '');
        $datum  = $request->input('datum', '');

        $query = Hilferuf::with('betrieb')->orderByRaw("FIELD(status,'offen','in_bearbeitung','erledigt')")->orderByDesc('created_at');

        if ($status !== '') {
            $query->where('status', $status);
        }
        if ($datum !== '') {
            $query->whereDate('created_at', $datum);
        }

        $hilferufe = $query->get();

        $stats = [
            'gesamt'         => Hilferuf::count(),
            'offen'          => Hilferuf::where('status', 'offen')->count(),
            'in_bearbeitung' => Hilferuf::where('status', 'in_bearbeitung')->count(),
            'erledigt'       => Hilferuf::where('status', 'erledigt')->count(),
        ];

        return view('admin.hilferufe', compact('hilferufe', 'stats', 'status', 'datum'));
    }

    /** Einzelnen Hilferuf löschen (Admin) */
    public function hilferufeDelete(Hilferuf $hilferuf)
    {
        $betriebName = $hilferuf->betrieb->name ?? '–';
        $hilferuf->delete();
        return back()->with(['type' => 'warning', 'Meldung' => 'Hilferuf von „' . $betriebName . '" gelöscht.']);
    }

    /** Alle erledigten Hilferufe löschen (Admin) */
    public function hilferufeLeeren()
    {
        $count = Hilferuf::where('status', 'erledigt')->count();
        Hilferuf::where('status', 'erledigt')->delete();
        return back()->with(['type' => 'success', 'Meldung' => $count . ' erledigte Hilferufe gelöscht.']);
    }

    /** Support-Betrieb-Einstellungen anzeigen */
    public function support(Customer $customer)
    {
        abort_if(! $customer->is_buisness(), 404);
        $betrieb       = $customer;
        $supportBetrieb = Customer::supportBetrieb();
        return view('admin.betrieb_support', compact('betrieb', 'supportBetrieb'));
    }

    /** Support-Betrieb aktivieren / deaktivieren */
    public function supportStore(Request $request, Customer $customer)
    {
        abort_if(! $customer->is_buisness(), 404);
        $request->validate(['aktion' => 'required|in:aktivieren,deaktivieren']);

        if ($request->input('aktion') === 'aktivieren') {
            abort_if(! $customer->betrieb_pin, 422, 'Der Betrieb benötigt zuerst einen Betriebs-PIN.');
            Customer::where('is_support', true)->update(['is_support' => false]);
            $customer->update(['is_support' => true]);
            return back()->with([
                'type'    => 'success',
                'Meldung' => $customer->name . ' ist jetzt der Support-Betrieb. Andere Betriebe können dort Hilfe anfordern.',
            ]);
        }

        $customer->update(['is_support' => false]);
        return back()->with([
            'type'    => 'warning',
            'Meldung' => 'Support-Markierung für ' . $customer->name . ' entfernt.',
        ]);
    }
}

