<?php

namespace App\Http\Controllers;

use App\Imports\CustomerImport;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\WorkingTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{

    public function import(){
        return view('admin.import');
    }

    public function storeImport(Request $request){
        $request->validate([
            'file' => 'required|mimes:xlsx'
        ]);

        $file = $request->file('file');

        Excel::import(new CustomerImport, $file);

        return redirect()->back()->with([
            'type' => 'success',
            'Meldung' => 'Kunden erfolgreich importiert'
        ]);
    }

    public function delete(){
        $buisnesses = Customer::where('buisness', 1)->where('export', 1)->get();
        $betroffen = "";
        foreach ($buisnesses as $buisness){
            $sum = $buisness->payments()
                ->whereNot('comment', 'LIKE', 'Kredit')
                ->whereNot('comment', 'LIKE', 'Startkapital')->sum('amount');

            if ($sum > $buisness->startkapital){
                $buisness->payments()->where('comment', 'LIKE', 'Startkapital')->delete();
                if ($betroffen != ""){
                    $betroffen .= ', '.$buisness->name;
                } else {
                    $betroffen = $buisness->name;
                }

            }
        }

        return redirect()->back()->with([
            'type' => 'warning',
            'Meldung' => 'Folgenden Betrieben wurde das Startkapital entfernt: '.$betroffen
        ]);
    }
    public function export(){
        return view('admin.export',[
            'customers' => Customer::where('buisness',1)->where('export', 1)->get()
        ]);
    }

    public function makeStartkapital(){
        $customers = Customer::doesntHave('payments')->get();
        $payments = [];

        foreach ($customers as $customer){
            $payment = [
                'customer_id' => $customer->id,
                'amount' => $customer->is_buisness()? $customer->startkapital : config('bank.startkapital'),
                'user_id' => auth()->id(),
                'comment' => 'Startkapital'
            ];

            $payments[]=$payment;
        }

        Payment::insert($payments);

        return redirect()->back()->with([
            'type' => 'success',
            'Meldung' => 'Startkapital wurde erstellt'
        ]);
    }

    public function gebuehr(){
        $customers = Customer::all();
        $bank = Customer::where('name', 'Bank')->first();
        $rathaus = Customer::where('name', 'LIKE','%Rathaus%')->first();

        $newPayment = new Payment([
            'customer_id' => $bank->id,
            'amount' => $customers->count(),
            'user_id' => auth()->id(),
            'comment' => 'Kontoführungsgebühr'
        ]);
        $newPayment->save();
        $newPayment = new Payment([
            'customer_id' => $rathaus->id,
            'amount' => $customers->count(),
            'user_id' => auth()->id(),
            'comment' => 'Steuern'
        ]);
        $newPayment->save();

        foreach ($customers as $customer){

            //Kontoführung
            if ($customer->name != "Bank"){

                $newPayment = new Payment([
                    'customer_id' => $customer->id,
                    'amount' => 0- config('bank.konto_gebuehr'),
                    'user_id' => auth()->id(),
                    'comment' => 'Kontoführungsgebühr'
                ]);
                $newPayment->save();

                if ($customer->kredit > 0){
                    $newPayment = new Payment([
                        'customer_id' => $customer->id,
                        'amount' => 0 - ceil(($customer->kredit /100 * config('bank.zinsen'))),
                        'user_id' => auth()->id(),
                        'comment' => 'Zinsen Kredit'
                    ]);
                    $newPayment->save();

                    $newPayment = new Payment([
                        'customer_id' => $bank->id,
                        'amount' => ceil(($customer->kredit /100 * config('bank.zinsen'))),
                        'user_id' => auth()->id(),
                        'comment' => 'Zinsen Kredit '. $customer->name
                    ]);
                    $newPayment->save();

                }
            }

            //steuern
            if ($customer->name != "Rathaus") {

                if (!$customer->is_buisness()) {
                    $newPayment = new Payment([
                        'customer_id' => $customer->id,
                        'amount' => 0 - config('bank.steuern'),
                        'user_id' => auth()->id(),
                        'comment' => 'Steuer '.Carbon::today()->format('d.m.Y')
                    ]);
                    $newPayment->save();
                } else {
                    $gewinn = $customer->daily_balance();


                    if ($gewinn > 0){
                        $newPayment = new Payment([
                            'customer_id' => $customer->id,
                            'amount' => 0 - ceil($gewinn/100* config('bank.gewinn_steuer')),
                            'user_id' => auth()->id(),
                            'comment' => 'Gewinnsteuer (Gewinn: '.$gewinn.' Radi)'
                        ]);
                        $newPayment->save();
                        $newPayment = new Payment([
                            'customer_id' => $rathaus->id,
                            'amount' => 0 + ceil($gewinn/100 * config('bank.gewinn_steuer')),
                            'user_id' => auth()->id(),
                            'comment' => 'Gewinnsteuer '.$customer->name
                        ]);
                        $newPayment->save();
                    }
                }
            }
        }

        return redirect()->back()->with([
           'type' => 'success',
           'Meldung' => $customers->count()." Radi Gebühr wurden kassiert"
        ]);
    }

    public function index(){

        $customer = Customer::where('buisness', 0)->orWhere('buisness', null)
            ->with('payments')->orderBy('balance', 'desc')->paginate(25);
        $buisness_count = Customer::where('buisness', 1)->count();
        $working_times_today = WorkingTime::where('start', '>=', Carbon::today()->startOfDay()->toDateTimeString())->withSum('payment_customer','amount')->get();
        $working_times_today_count = $working_times_today->count();
        $lohn = $working_times_today->sum('payment_customer_sum_amount');

        return view('admin.dashboard')->with([
            'customer_count' => Customer::where('buisness', 0)->orWhere('buisness', null)->count(),
            'buisness_count' => $buisness_count,
            'working_times_today_count' => $working_times_today_count,
            'working_times' => WorkingTime::count(),
            'lohn'  => $lohn,
            'buisnesses' => Customer::where('buisness', 1)->with('payments')->get()->sortByDesc('balance'),
            'customers' => $customer->sortByDesc('balance')
        ]);
    }
}
