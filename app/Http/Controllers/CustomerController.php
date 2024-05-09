<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCustomerRequest;
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
            'key' => 'required|string|min:8'
        ]);

        $customer->update([
            'key' => $request->key
        ]);





        Session::remove('customer');
        Session::put('customer', Customer::find($customer->id));


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
        $payments = session('customer')->payments()->orderByDesc('created_at')->paginate(10);

        return view('customer.log', [
            'payments' => $payments,
            'working_times' => WorkingTime::where('customer_id', session('customer')->id)->get()
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('name');
        $filterResult = Customer::where('name', 'LIKE', '%'. $query. '%')->get();
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


}
