<?php

namespace App\Http\Controllers\Betrieb;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Lieferbestellung;
use App\Models\LieferbestellungPosition;
use App\Models\LieferdienstProdukt;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LieferdienstController extends Controller
{
    private function betrieb(): Customer
    {
        return Customer::findOrFail(session('betrieb')->id);
    }

    /** Bestellungsübersicht in der Kasse */
    public function bestellungen()
    {
        $betrieb = $this->betrieb();

        abort_unless($betrieb->isLieferdienst(), 403, 'Dieser Betrieb ist kein Lieferdienst.');

        $bestellungen = Lieferbestellung::with(['positionen.product', 'mitarbeiter'])
            ->where('lieferdienst_id', $betrieb->id)
            ->orderByRaw("FIELD(status,'neu','in_bearbeitung','erledigt')")
            ->orderByDesc('created_at')
            ->get();

        $kinder = Customer::where('buisness', 0)->orderBy('name')->get(['id', 'name']);

        return view('betrieb.lieferdienst.bestellungen', compact('betrieb', 'bestellungen', 'kinder'));
    }

    /** Mitarbeiter einer Bestellung zuweisen */
    public function mitarbeiterZuweisen(Request $request, Lieferbestellung $bestellung)
    {
        $betrieb = $this->betrieb();
        abort_unless($bestellung->lieferdienst_id === $betrieb->id, 403);

        $request->validate([
            'mitarbeiter_id' => 'nullable|exists:customers,id',
        ]);

        $bestellung->update(['mitarbeiter_id' => $request->mitarbeiter_id ?: null]);

        return back()->with(['type' => 'success', 'Meldung' => 'Mitarbeiter zugewiesen.']);
    }

    /** Status einer Bestellung aktualisieren */
    public function statusAktualisieren(Request $request, Lieferbestellung $bestellung)
    {
        $betrieb = $this->betrieb();
        abort_unless($bestellung->lieferdienst_id === $betrieb->id, 403);

        $request->validate([
            'status' => 'required|in:neu,in_bearbeitung,erledigt',
        ]);

        $bestellung->update(['status' => $request->status]);

        $label = match($request->status) {
            'neu'            => 'Neu',
            'in_bearbeitung' => 'In Bearbeitung',
            'erledigt'       => 'Erledigt',
        };

        return back()->with(['type' => 'success', 'Meldung' => 'Status auf „' . $label . '" gesetzt.']);
    }

    /** Verwaltung: Welche Produkte liefert dieser Betrieb? */
    public function produkte()
    {
        $betrieb = $this->betrieb();
        abort_unless($betrieb->isLieferdienst(), 403, 'Dieser Betrieb ist kein Lieferdienst.');

        // Alle aktiven Produkte aller Betriebe (außer eigenem), gruppiert nach Betrieb
        $alleBetriebe = Customer::buisness()
            ->where('id', '!=', $betrieb->id)
            ->with(['products' => fn($q) => $q->where('active', true)->orderBy('name')])
            ->get()
            ->filter(fn($b) => $b->products->isNotEmpty());

        // Bereits ausgewählte Produkt-IDs
        $ausgewaehlt = LieferdienstProdukt::where('lieferdienst_id', $betrieb->id)
            ->pluck('product_id')
            ->toArray();

        return view('betrieb.lieferdienst.produkte', compact('betrieb', 'alleBetriebe', 'ausgewaehlt'));
    }

    /** Produkt-Auswahl speichern */
    public function produkteSpeichern(Request $request)
    {
        $betrieb = $this->betrieb();
        abort_unless($betrieb->isLieferdienst(), 403);

        $request->validate([
            'produkte'   => 'nullable|array',
            'produkte.*' => 'integer|exists:products,id',
        ]);

        $ids = $request->input('produkte', []);

        DB::transaction(function () use ($betrieb, $ids) {
            LieferdienstProdukt::where('lieferdienst_id', $betrieb->id)->delete();
            foreach ($ids as $productId) {
                LieferdienstProdukt::create([
                    'lieferdienst_id' => $betrieb->id,
                    'product_id'      => $productId,
                ]);
            }
        });

        return back()->with(['type' => 'success', 'Meldung' => count($ids) . ' Produkte für Lieferung gespeichert.']);
    }

    /** Lieferkosten setzen */
    public function lieferkostenSpeichern(Request $request)
    {
        $betrieb = $this->betrieb();
        abort_unless($betrieb->isLieferdienst(), 403);

        $request->validate([
            'lieferkosten' => 'required|integer|min:0|max:9999',
        ]);

        $betrieb->update(['lieferkosten' => $request->lieferkosten]);

        return back()->with(['type' => 'success', 'Meldung' => 'Lieferkosten auf ' . $request->lieferkosten . ' Radi gesetzt.']);
    }
}

