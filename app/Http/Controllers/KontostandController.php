<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class KontostandController extends Controller
{
    public function kontostand()
    {
        return view('kontostand.kontostand');
    }

    public function auth()
    {

        $customer = Customer::where('key', request('key'))->first();

        if($customer == null){
            return redirect()->back()->with([
                'type'=>'danger',
                'Meldung'=> 'Kunde nicht gefunden.'
            ]);
        }

        return view('kontostand.show',[
            'user' => $customer,
            'kontostand' => $customer->balance,

        ]);

    }
}
