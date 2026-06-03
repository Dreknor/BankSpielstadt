<?php

namespace App\Http\Controllers\Betrieb;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Hilferuf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class HilfeController extends Controller
{
    private function betrieb(): Customer
    {
        return Customer::findOrFail(session('betrieb')->id);
    }

    /**
     * Alle Hilferufe anzeigen – nur für den Support-Betrieb.
     */
    public function index()
    {
        $betrieb = $this->betrieb();
        abort_unless($betrieb->isSupport(), 403, 'Nur der Support-Betrieb kann diese Seite sehen.');

        $hilferufe = Hilferuf::with('betrieb')
            ->orderByRaw("FIELD(status, 'offen', 'in_bearbeitung', 'erledigt')")
            ->orderByDesc('created_at')
            ->get();

        $anzahlOffen = $hilferufe->where('status', 'offen')->count();

        return view('betrieb.hilfe.index', compact('betrieb', 'hilferufe', 'anzahlOffen'));
    }

    /**
     * Neuen Hilferuf speichern (von jedem Betrieb auslösbar).
     */
    public function store(Request $request)
    {
        $betrieb = $this->betrieb();

        $request->validate([
            'nachricht' => 'nullable|string|max:500',
        ]);

        // Kein doppelter offener Hilferuf vom selben Betrieb
        $vorhanden = Hilferuf::where('customer_id', $betrieb->id)
            ->whereIn('status', ['offen', 'in_bearbeitung'])
            ->exists();

        if ($vorhanden) {
            return back()->with([
                'type'    => 'warning',
                'Meldung' => 'Du hast schon einen offenen Hilferuf! Die Helfer kommen gleich.',
            ]);
        }

        Hilferuf::create([
            'customer_id' => $betrieb->id,
            'nachricht'   => $request->input('nachricht') ?: null,
            'status'      => 'offen',
        ]);

        return back()->with([
            'type'    => 'success',
            'Meldung' => '🆘 Hilferuf abgeschickt! Ein Helfer kommt gleich zu euch.',
        ]);
    }

    /**
     * Status, Bearbeiter und Notiz eines Hilferufs aktualisieren (nur Support-Betrieb).
     */
    public function update(Request $request, Hilferuf $hilferuf)
    {
        $betrieb = $this->betrieb();
        abort_unless($betrieb->isSupport(), 403);

        $request->validate([
            'status'     => 'required|in:offen,in_bearbeitung,erledigt',
            'bearbeiter' => 'nullable|string|max:100',
            'notiz'      => 'nullable|string|max:500',
        ]);

        $hilferuf->update([
            'status'     => $request->input('status'),
            'bearbeiter' => $request->input('bearbeiter') ?: null,
            'notiz'      => $request->input('notiz') ?: null,
        ]);

        $label = match ($request->input('status')) {
            'in_bearbeitung' => 'Hilferuf wird jetzt bearbeitet.',
            'erledigt'       => '✅ Hilferuf als erledigt markiert.',
            default          => 'Hilferuf aktualisiert.',
        };

        return back()->with(['type' => 'success', 'Meldung' => $label]);
    }

    /**
     * Hilferuf von der Börse (session('boerse') statt session('betrieb')).
     */
    public function storeBoerse(Request $request)
    {
        $betrieb = \App\Models\Customer::boerseBetrieb();
        abort_unless($betrieb, 404, 'Kein Börsenbetrieb konfiguriert.');

        $request->validate(['nachricht' => 'nullable|string|max:500']);

        $vorhanden = Hilferuf::where('customer_id', $betrieb->id)
            ->whereIn('status', ['offen', 'in_bearbeitung'])
            ->exists();

        if ($vorhanden) {
            return back()->with([
                'type'    => 'warning',
                'Meldung' => 'Es läuft schon ein Hilferuf! Die Helfer kommen gleich.',
            ]);
        }

        Hilferuf::create([
            'customer_id' => $betrieb->id,
            'nachricht'   => $request->input('nachricht') ?: null,
            'status'      => 'offen',
        ]);

        return back()->with([
            'type'    => 'success',
            'Meldung' => '🆘 Hilferuf abgeschickt! Ein Helfer kommt gleich zu euch.',
        ]);
    }
}


