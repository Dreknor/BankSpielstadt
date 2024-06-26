<?php

namespace App\Http\Controllers;

use App\Http\Requests\KreditRequest;
use App\Http\Requests\PaymentRequest;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    public function ueberweisung(){
        return view('customer.ueberweisung',[
            'buisnesses' => Customer::where('buisness', 1)->orderBy('name')->get()
        ]);
    }

    public function storeUeberweisung(Request $request)
    {
        if (!session('customer')){
            return redirect()->back()->with([
                'type' => 'error',
                'Meldung' => 'Kein Kunde ausgewählt.'
            ]);
        }

        $request->validate([
            'buisness' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:1',
            'reason' => 'required'
        ]);

        if ($request->amount > session('customer')->balance){
            return redirect()->back()->with([
                'type' => 'error',
                'Meldung' => 'Kontostand zu niedrig!'
            ]);
        }

        $empfaenger = Customer::find($request->buisness);

        $payment_from = new Payment([
            'customer_id' => session('customer')->id,
            'amount' => 0-$request->amount,
            'user_id' => auth()->id(),
            'comment' => 'Überweisung an '. $empfaenger->name.': '.$request->reason
        ]);

        $payment_from->save();


        $payment = new Payment([
            'customer_id' => $request->buisness,
            'amount' => $request->amount,
            'user_id' => auth()->id(),
            'comment' => 'Überweisung von '. session('customer')->name.': '.$request->reason,
            'payment_id' => $payment_from->id
        ]);

        $payment->save();

        $payment_from->update([
            'payment_id' => $payment->id
        ]);

        return redirect(url('/'))->with([
            'type' => 'success',
            'Meldung' => 'Überweisung gespeichert.'
        ]);
    }

    public function einzahlen(){
        return view('customer.einzahlen');
    }

    public function storeEinzahlen(PaymentRequest $request){

            $Einzahlung =$request->amount;

            if ($Einzahlung < session('customer')->kredit){
                session('customer')->update([
                    'kredit' => session('customer')->kredit - $Einzahlung
                ]);
                $payment = new Payment([
                    'customer_id' => session('customer')->id,
                    'amount' => 0,
                    'user_id' => auth()->id(),
                    'comment' => 'Rückzahlung Kredit: '. $Einzahlung. ' Radi'
                ]);

                $payment->save();
            } else {

                if (session('customer')->kredit > 0) {

                    $Einzahlung -= session('customer')->kredit;

                    $payment = new Payment([
                        'customer_id' => session('customer')->id,
                        'amount' => 0,
                        'user_id' => auth()->id(),
                        'comment' => 'Rückzahlung Kredit: '. session('customer')->kredit. " Radi"
                    ]);
                    $payment->save();

                    if ($Einzahlung>0){
                        $payment = new Payment([
                            'customer_id' => session('customer')->id,
                            'amount' => $Einzahlung,
                            'user_id' => auth()->id(),
                            'comment' => 'Einzahlung'
                        ]);

                        $payment->save();
                    }

                    session('customer')->update([
                        'kredit' => null
                    ]);


                } else {

                    $payment = new Payment([
                        'customer_id' => session('customer')->id,
                        'amount' => $Einzahlung,
                        'user_id' => auth()->id(),
                        'comment' => 'Einzahlung'
                    ]);

                    $payment->save();
                }
            }






        return redirect(url('/'))->with([
            'type' => 'success',
            'Meldung' => 'Einzahlung gespeichert. Nimm das Geld entgegen und zähle es nach.'
        ]);
    }

    public function auszahlen(){
        return view('customer.auszahlen');
    }


    public function kredit(){
        return view('customer.kredit');
    }

    public function storeKredit(KreditRequest $request){

        session('customer')->update([
           'kredit' => $request->kredit
        ]);

        $payment = new Payment([
            'customer_id' => session('customer')->id,
            'amount' => $request->kredit,
            'user_id' => auth()->id(),
            'comment' => 'Kredit'
        ]);

        $payment->save();

        return redirect(url('/'))->with([
            'type' => 'success',
            'Meldung' => 'Kredit gespeichert.'
        ]);
    }

    public function storeAuszahlen(PaymentRequest $request){

        if ($request->amount > session('customer')->balance){
            return redirect()->back()->with([
                'type' => 'error',
                'Meldung' => 'Kontostand zu niedrig! Radis nicht auszahlen.'
            ]);
        }

        $payment = new Payment([
            'customer_id' => session('customer')->id,
            'amount' => 0-$request->amount,
            'user_id' => auth()->id(),
            'comment' => 'Auszahlung'
        ]);

        $payment->save();

        return redirect(url('/'))->with([
            'type' => 'success',
            'Meldung' => 'Auszahlung gespeichert. Gibt das Geld dem Kunden.'
        ]);
    }

    public function delete(Request $request, Payment $payment){

        if ($payment->partnerPayment()->exists()){
            $payment->partnerPayment->delete();
        }

        $payment->delete();

        return redirect(url('/'))->with([
            'type' => 'warning',
            'Meldung' => 'Zahlung wurde gelöscht.'
        ]);
    }
}
