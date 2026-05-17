<?php

namespace App\Http\Controllers;

use App\Models\Customer;
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
}

