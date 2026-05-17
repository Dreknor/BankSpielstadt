<?php

namespace App\Http\Controllers;

use App\Models\AktienBestand;
use App\Models\Customer;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $customer = Customer::find(session('customer')->id);

        // Aktien-Portfolio: welche Anteile hält der Kunde gerade?
        $portfolio = AktienBestand::with('betrieb')
            ->where('customer_id', $customer->id)
            ->where('stueck', '>', 0)
            ->get();

        return view('home', compact('customer', 'portfolio'));
    }
}
