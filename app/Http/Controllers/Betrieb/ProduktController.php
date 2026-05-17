<?php

namespace App\Http\Controllers\Betrieb;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ProduktController extends Controller
{
    private function betrieb(): Customer
    {
        return Customer::findOrFail(session('betrieb')->id);
    }

    public function index()
    {
        $betrieb  = $this->betrieb();
        $produkte = $betrieb->products()->withTrashed()->orderBy('name')->get();
        $kassenbestand = $betrieb->kassenbestand();
        return view('betrieb.produkte.index', compact('betrieb', 'produkte', 'kassenbestand'));
    }

    public function create()
    {
        $betrieb = $this->betrieb();
        $kassenbestand = $betrieb->kassenbestand();
        return view('betrieb.produkte.create', compact('betrieb', 'kassenbestand'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'price' => 'required|integer|min:1',
        ]);

        $this->betrieb()->products()->create([
            'name'   => $data['name'],
            'price'  => $data['price'],
            'active' => true,
        ]);

        return redirect('/betrieb/produkte')->with(['type' => 'success', 'Meldung' => 'Produkt wurde gespeichert!']);
    }

    public function edit(Product $product)
    {
        $this->authorizeProduct($product);
        $betrieb = $this->betrieb();
        $kassenbestand = $betrieb->kassenbestand();
        return view('betrieb.produkte.edit', compact('betrieb', 'product', 'kassenbestand'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeProduct($product);
        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'price'  => 'required|integer|min:1',
            'active' => 'boolean',
        ]);

        $product->update([
            'name'   => $data['name'],
            'price'  => $data['price'],
            'active' => $request->boolean('active'),
        ]);

        return redirect('/betrieb/produkte')->with(['type' => 'success', 'Meldung' => 'Produkt aktualisiert!']);
    }

    public function destroy(Product $product)
    {
        $this->authorizeProduct($product);
        $product->delete();
        return redirect('/betrieb/produkte')->with(['type' => 'warning', 'Meldung' => 'Produkt gelöscht.']);
    }

    private function authorizeProduct(Product $product): void
    {
        abort_if($product->customer_id !== session('betrieb')->id, 403);
    }
}

