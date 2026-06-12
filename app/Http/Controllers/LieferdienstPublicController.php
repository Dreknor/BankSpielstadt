<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lieferbestellung;
use App\Models\LieferbestellungPosition;
use App\Models\LieferdienstProdukt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LieferdienstPublicController extends Controller
{
    /** Öffentliche Bestell-Seite */
    public function show(Customer $customer)
    {
        abort_unless($customer->isLieferdienst(), 404);

        // Produkte, die dieser Lieferdienst anbietet, mit zugehörigem Betrieb laden
        $angebote = LieferdienstProdukt::with(['product.customer'])
            ->where('lieferdienst_id', $customer->id)
            ->get()
            ->filter(fn($a) => $a->product !== null && $a->product->active)
            ->groupBy(fn($a) => $a->product->customer->name ?? 'Unbekannter Betrieb');

        return view('lieferdienst.bestellen', [
            'lieferdienst' => $customer,
            'angebote'     => $angebote,
        ]);
    }

    /** Bestellung aufgeben */
    public function store(Request $request, Customer $customer)
    {
        abort_unless($customer->isLieferdienst(), 404);

        $request->validate([
            'besteller_name' => 'required|string|min:2|max:100',
            'lieferort'      => 'required|string|min:2|max:200',
            'positionen'     => 'required|array|min:1',
            'positionen.*.product_id' => 'required|integer|exists:products,id',
            'positionen.*.menge'      => 'required|integer|min:1|max:99',
        ], [
            'besteller_name.required' => 'Bitte deinen Namen eingeben.',
            'besteller_name.min'      => 'Der Name muss mindestens 2 Buchstaben haben.',
            'lieferort.required'      => 'Bitte den Lieferort angeben.',
            'lieferort.min'           => 'Der Lieferort muss mindestens 2 Zeichen haben.',
            'positionen.required'     => 'Bitte mindestens ein Produkt auswählen.',
            'positionen.min'          => 'Bitte mindestens ein Produkt auswählen.',
        ]);

        // Prüfen, dass nur angebotene Produkte bestellt werden
        $erlaubteIds = LieferdienstProdukt::where('lieferdienst_id', $customer->id)
            ->pluck('product_id')
            ->toArray();

        $positionen = collect($request->positionen)->filter(fn($p) => $p['menge'] > 0);

        foreach ($positionen as $pos) {
            abort_unless(in_array($pos['product_id'], $erlaubteIds), 422, 'Ungültiges Produkt.');
        }

        if ($positionen->isEmpty()) {
            return back()->withErrors(['positionen' => 'Bitte mindestens ein Produkt auswählen.']);
        }

        DB::transaction(function () use ($customer, $request, $positionen) {
            $gesamtbetrag = 0;
            $positionenDaten = [];

            foreach ($positionen as $pos) {
                $product = \App\Models\Product::findOrFail($pos['product_id']);
                $subtotal = $product->price * $pos['menge'];
                $gesamtbetrag += $subtotal;
                $positionenDaten[] = [
                    'product_id'  => $product->id,
                    'menge'       => $pos['menge'],
                    'einzelpreis' => $product->price,
                ];
            }

            // Lieferkosten dazurechnen
            if ($customer->lieferkosten) {
                $gesamtbetrag += $customer->lieferkosten;
            }

            $bestellung = Lieferbestellung::create([
                'lieferdienst_id' => $customer->id,
                'besteller_name'  => $request->besteller_name,
                'lieferort'       => $request->lieferort,
                'status'          => 'neu',
                'gesamtbetrag'    => $gesamtbetrag,
            ]);

            foreach ($positionenDaten as $pos) {
                LieferbestellungPosition::create(array_merge($pos, ['bestellung_id' => $bestellung->id]));
            }
        });

        return redirect()->route('lieferdienst.show', $customer)
            ->with(['type' => 'success', 'Meldung' => 'Deine Bestellung wurde aufgegeben! Der Lieferdienst bringt sie dir bald.']);
    }
}

