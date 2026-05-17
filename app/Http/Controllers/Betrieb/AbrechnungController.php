<?php

namespace App\Http\Controllers\Betrieb;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\KasseTransaktion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AbrechnungController extends Controller
{
    private function betrieb(): Customer
    {
        return Customer::findOrFail(session('betrieb')->id);
    }

    public function index(Request $request)
    {
        $betrieb = $this->betrieb();
        Session::put('betrieb', $betrieb);

        $datum = $request->input('datum', Carbon::today()->toDateString());

        $transaktionen = KasseTransaktion::with('positionen.product')
            ->where('customer_id', $betrieb->id)
            ->whereDate('created_at', $datum)
            ->orderByDesc('created_at')
            ->get();

        $summen = [
            'einlagen'  => $transaktionen->where('type', 'einlage')->sum('amount'),
            'verkaeufe' => $transaktionen->where('type', 'verkauf')->sum('amount'),
            'entnahmen' => $transaktionen->where('type', 'entnahme')->sum('amount'),
        ];

        $kassenbestand = $betrieb->kassenbestand();

        return view('betrieb.abrechnung.index', compact('betrieb', 'transaktionen', 'summen', 'kassenbestand', 'datum'));
    }

    public function print(Request $request)
    {
        $betrieb = $this->betrieb();

        $datum = $request->input('datum', Carbon::today()->toDateString());

        $transaktionen = KasseTransaktion::with('positionen.product')
            ->where('customer_id', $betrieb->id)
            ->whereDate('created_at', $datum)
            ->orderBy('created_at')
            ->get();

        $summen = [
            'einlagen'  => $transaktionen->where('type', 'einlage')->sum('amount'),
            'verkaeufe' => $transaktionen->where('type', 'verkauf')->sum('amount'),
            'entnahmen' => $transaktionen->where('type', 'entnahme')->sum('amount'),
        ];

        $kassenbestand = $betrieb->kassenbestand();

        return view('betrieb.abrechnung.print', compact('betrieb', 'transaktionen', 'summen', 'kassenbestand', 'datum'));
    }
}

