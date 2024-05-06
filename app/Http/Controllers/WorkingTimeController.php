<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkingTimeRequest;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\WorkingTime;
use Barryvdh\Debugbar\Facades\Debugbar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkingTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create()
    {
        $lastWorking_time = session('customer')->working_times->last();

        if (!is_null($lastWorking_time)){
            $day = $lastWorking_time->end->dayOfWeek;
        } else {
            $day = (Carbon::now()->hour > 9)? Carbon::now()->subDay()->dayOfWeek : Carbon::now()->dayOfWeek;
        }

        return view('workingtimes.form',[
            'day' => $day,
            'buisnesses' => Customer::query()->buisness()->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(WorkingTimeRequest $request)
    {
        $customer = session('customer');

        $startdate = Carbon::today()->startOfWeek();
        $start_working = $startdate->copy()->addDays($request->day);
        $start_working->setHour($request->start_hour);
        $start_working->setMinutes($request->start_minute);

        $end_working = $startdate->copy()->addDays($request->day);
        $end_working->setHour($request->end_hour);
        $end_working->setMinutes($request->end_minute);

        if ($end_working->lessThan($start_working)){
            return redirect()->back()->with([
                'type' => 'danger',
                'Meldung' => "Die Endzeit darf nicht vor der Anfangszeit liegen"
            ]);
        }

        $duration = $start_working->diffInMinutes($end_working);

        if ($request->manager == 1){
            $StdLohn = config('bank.lohn.chef');
        } else {
            $StdLohn = config('bank.lohn.arbeiter');
        }
        $Lohn = floor(($duration / 60) * $StdLohn);

        $buisness = Customer::find($request->buisness);
        $customer = session('customer');

        if ($buisness->balance < $Lohn){
            return redirect()->back()->with([
                'type' => 'danger',
                'Meldung' => "Der Betrieb hat nicht genügend Geld um den Lohn zu bezahlen!"
            ]);
        }

        //Check Arbeitszeiten
        $working_times = $customer->working_times()
            ->where(function($query) use ($start_working,$end_working, $customer){
                $query->where('start', '<=', $start_working);
                $query->where('end', '>=', $start_working);
                $query->where('customer_id', '=', $customer->id);
            })
            ->orWhere(function($query) use ($start_working,$end_working, $customer){
                $query->where('start', '<=', $end_working);
                $query->where('end', '>=', $end_working);
                $query->where('customer_id', '=', $customer->id);

            })
            ->count();

        if ($working_times > 0){
            return redirect()->back()->with([
                'type' => 'danger',
                'Meldung' => "Es gibt bereits eine Arbeitszeit während dieses Zeitraumes. Der Arbeiter kann nicht an 2 Stellen gleichzeitig gearbeitet haben."
            ]);
        }

        $payment_buisness = new Payment([
            'customer_id' => $buisness->id,
            'amount' => 0 - $Lohn,
            'comment' => "Lohn: ". $customer->name.' ('.$start_working->format('d.m.Y H:i') . ' bis '.$end_working->format('H:i').' Uhr)',
            'user_id' => auth()->id()
        ]);

        $payment_customer = new Payment([
            'customer_id' => $customer->id,
            'amount' => $Lohn,
            'source_id' => $buisness->id,
            'comment' => "Lohn: ". $buisness->name.' ('.$start_working->format('d.m.Y H:i') . ' bis '.$end_working->format('H:i').' Uhr)',
            'user_id' => auth()->id()
        ]);

         $payment_buisness->save();
        $payment_customer->save();


        $anzahl = $customer->working_times()->whereDate('created_at', Carbon::today())->count();
        if ($anzahl >= config('bank.kostenfreie_berechnungen')){
            $payment_gebuehr = new Payment([
                'customer_id' => $customer->id,
                'amount' => 0 - config('bank.kosten_berechnungen'),
                'source_id' => NULL,
                'comment' => 'Gebühr Arbeitszeitberechnung',
                'user_id' => auth()->id()
            ]);

            $bank = Customer::where('name', 'Bank')->first();

            $payment_bank = new Payment([
                'customer_id' => $bank->id,
                'amount' => 0 + config('bank.kosten_berechnungen'),
                'source_id' => $customer->id,
                'comment' => 'Gebühr Arbeitszeitberechnung '.$customer->name,
                'user_id' => auth()->id()
            ]);

            $payment_bank->save();
            $payment_gebuehr->save();
        }

        $newWorking_time = new WorkingTime([
            'customer_id' => $customer->id,
            'buisness_id' => $buisness->id,
            'user_id'    => auth()->id(),
            'is_manger' => $request->manager,
            'start' => $start_working,
            'end' => $end_working,
            'payment_buisness' => $payment_buisness->id,
            'payment_customer' => $payment_customer->id,
        ]);
        $newWorking_time->save();


        return redirect(url('/'))->with([
            'type' => 'success',
            'Meldung' => "Der Lohn wurde berechnet (".$Lohn.' Radi) und auf das Konto überwiesen.'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WorkingTime  $workingTime
     * @return \Illuminate\Http\Response
     */
    public function show(WorkingTime $workingTime)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WorkingTime  $workingTime
     * @return \Illuminate\Http\Response
     */
    public function edit(WorkingTime $workingTime)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WorkingTime  $workingTime
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WorkingTime $workingTime)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WorkingTime  $workingTime
     * @return \Illuminate\Http\Response
     */
    public function destroy(WorkingTime $workingTime)
    {
        $workingTime->payment_customer()->delete();
        $workingTime->payment_buisness()->delete();

        $workingTime->delete();

        return redirect(url('/'))->with([
            'type' => 'warning',
            'Meldung' => 'Arbeitszeit wurde gelöscht.'
        ]);
    }
}
