<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCustomerRequest;
use App\Models\AktienTransaktion;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\WorkingTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CustomerController extends Controller
{


    public function createCustomer(){
        return view('customer.create');
    }

    public function setKey(Request $request){

       $customer = session('customer');
        if($customer == null){
            return redirect()->back()->with([
                'type'=>'danger',
                'Meldung'=> 'Kunde nicht gefunden.'
            ]);
        }

        if($customer->key != null){
            return redirect()->back()->with([
                'type'=>'danger',
                'Meldung'=> 'Bereits ein Schlüssel angemeldet.'
            ]);
        }

        $request->validate([
            'key' => 'required|string|min:8|unique:customers,key'
        ], [
            'key.unique' => 'Dieser Key ist bereits vergeben.',
        ]);

        $customer->update([
            'key' => $request->key
        ]);




        return redirect(url('/'))->with([
            'type'=>'success',
            'Meldung'=> 'Schlüssel wurde gespeichert.'
        ]);
    }


    public function store(CreateCustomerRequest $request){
        $newCustomer = new Customer($request->validated());
        $newCustomer->startkapital = ($request->startkapital) ? $request->startkapital : config('bank.startkapital');
        $newCustomer->save();

            $payment = new Payment([
                'customer_id' => $newCustomer->id,
                'amount' => ($request->startkapital) ? $request->startkapital : config('bank.startkapital'),
                'user_id' => auth()->id(),
                'comment' => 'Startkapital'
            ]);
            $payment->save();




        return redirect('/')->with([
           'type'   => 'success',
           'Meldung'=> 'Kunde wurde erstellt'
        ]);
    }

    public function choose(){
        return view('customer.choose');
    }

    public function log(){
        $customer = session('customer');

        $payments = $customer->payments()->orderByDesc('created_at')->paginate(10);

        $working_times = WorkingTime::where('customer_id', $customer->id)->get();

        // Aktien-Transaktionen des Kunden (Käufe & Verkäufe)
        $aktienTransaktionen = AktienTransaktion::with('betrieb')
            ->where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->get();

        return view('customer.log', compact('payments', 'working_times', 'aktienTransaktionen'));
    }

    public function search(Request $request)
    {
        $query = $request->get('name');
        $filterResult = Customer::where('name', 'LIKE', '%' . $query . '%')
            ->orWhere('key', 'LIKE', '%' . $query . '%')
            ->get();
        return response()->json($filterResult);
    }



    public function new(Request $request){
        Session::remove('customer');

        return redirect(url('choose/customer'));
    }


    public function setCustomer(Request $request, Customer $customer){
        Session::put('customer', $customer);

        return redirect(url('/'));
    }

    /**
     * Wird aufgerufen, wenn das Suchformular abgeschickt wird (z. B. automatisch durch NFC-Chip).
     * Findet einen Kunden anhand des exakten Keys und wählt ihn direkt aus.
     */
    public function chooseByKey(Request $request){
        $suche = trim($request->input('suche', ''));

        if ($suche === '') {
            return redirect(url('choose/customer'))->with([
                'type'    => 'warning',
                'Meldung' => 'Bitte einen Namen oder Chip scannen.',
            ]);
        }

        $customer = Customer::where('key', $suche)->first();

        if ($customer) {
            Session::put('customer', $customer);
            return redirect(url('/'));
        }

        return redirect(url('choose/customer'))->with([
            'type'    => 'error',
            'Meldung' => 'Kein Kunde mit diesem Chip gefunden. Bitte den Namen in der Liste auswählen.',
        ]);
    }


}
