<?php

namespace App\Http\Controllers\Betrieb;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\KasseTransaktion;
use App\Models\KassePosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class KasseController extends Controller
{
    private function betrieb(): Customer
    {
        return Customer::findOrFail(session('betrieb')->id);
    }

    public function index()
    {
        $betrieb  = $this->betrieb();
        Session::put('betrieb', $betrieb);
        $produkte = $betrieb->products()->where('active', true)->orderBy('name')->get();
        $kassenbestand = $betrieb->kassenbestand();
        return view('betrieb.kasse.index', compact('betrieb', 'produkte', 'kassenbestand'));
    }

    public function storeVerkauf(Request $request)
    {
        $request->validate([
            'produkte'         => 'required|array|min:1',
            'produkte.*.id'    => 'required|integer|exists:products,id',
            'produkte.*.menge' => 'required|integer|min:1',
        ]);

        $betrieb = $this->betrieb();

        DB::transaction(function () use ($request, $betrieb) {
            $total = 0;
            $positionen = [];

            foreach ($request->produkte as $item) {
                $product = $betrieb->products()->findOrFail($item['id']);
                $subtotal = $product->price * $item['menge'];
                $total += $subtotal;
                $positionen[] = [
                    'product_id'   => $product->id,
                    'menge'        => $item['menge'],
                    'einzelpreis'  => $product->price,
                ];
            }

            $transaktion = KasseTransaktion::create([
                'customer_id' => $betrieb->id,
                'type'        => 'verkauf',
                'amount'      => $total,
                'comment'     => 'Verkauf: ' . collect($positionen)->map(
                    fn($p) => $p['menge'] . '×' . \App\Models\Product::find($p['product_id'])->name
                )->implode(', '),
            ]);

            foreach ($positionen as $pos) {
                KassePosition::create(array_merge($pos, ['transaktion_id' => $transaktion->id]));
            }
        });

        return redirect('/betrieb/kasse')->with(['type' => 'success', 'Meldung' => 'Verkauf wurde gebucht!']);
    }

    public function storeEinlage(Request $request)
    {
        $request->validate([
            'amount'  => 'required|integer|min:1',
            'comment' => 'nullable|string|max:200',
        ]);

        $betrieb = $this->betrieb();

        KasseTransaktion::create([
            'customer_id' => $betrieb->id,
            'type'        => 'einlage',
            'amount'      => $request->amount,
            'comment'     => $request->comment ?: 'Bareinlage',
        ]);

        return redirect('/betrieb/kasse')->with(['type' => 'success', 'Meldung' => 'Einlage von ' . $request->amount . ' Radi gebucht!']);
    }

    public function storeEntnahme(Request $request)
    {
        $request->validate([
            'amount'  => 'required|integer|min:1',
            'comment' => 'required|string|max:200',
        ]);

        $betrieb = $this->betrieb();

        if ($betrieb->kassenbestand() < $request->amount) {
            return back()->with(['type' => 'error', 'Meldung' => 'Nicht genug Geld in der Kasse! Kassenbestand: ' . $betrieb->kassenbestand() . ' Radi.']);
        }

        KasseTransaktion::create([
            'customer_id' => $betrieb->id,
            'type'        => 'entnahme',
            'amount'      => $request->amount,
            'comment'     => $request->comment,
        ]);

        return redirect('/betrieb/kasse')->with(['type' => 'success', 'Meldung' => $request->amount . ' Radi entnommen.']);
    }
}


