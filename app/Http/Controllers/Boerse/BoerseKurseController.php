<?php

namespace App\Http\Controllers\Boerse;

use App\Http\Controllers\Controller;
use App\Models\AktienKurs;
use App\Models\AktienTransaktion;
use App\Models\BoerseAufgabenLog;
use App\Models\BoerseKasse;
use App\Models\Customer;
use App\Services\BoerseAufgabenService;

class BoerseKurseController extends Controller
{
    public function index(BoerseAufgabenService $service)
    {
        $betriebe       = Customer::where('buisness', 1)->whereNotNull('aktien_gesamt')->get();
        $aufgabenStatus = $service->status();
        return view('boerse.kurse.index', compact('betriebe', 'aufgabenStatus'));
    }

    public function verlauf(Customer $customer, BoerseAufgabenService $service)
    {
        abort_unless($customer->hatAktien(), 404);
        $kurse          = AktienKurs::where('buisness_id', $customer->id)
            ->latest('created_at')->take(24)->get()->reverse()->values();
        $aufgabenStatus = $service->status();
        return view('boerse.kurse.verlauf', compact('customer', 'kurse', 'aufgabenStatus'));
    }

    public function kurstafel(BoerseAufgabenService $service)
    {
        // Aufruf der Druckseite zählt automatisch als Bestätigung
        BoerseAufgabenLog::create([
            'aufgabe'    => 'kurstafel_ausgehaengt',
            'created_at' => now(),
        ]);
        $service->clearCache();

        $betriebe = Customer::where('buisness', 1)->whereNotNull('aktien_gesamt')->get();
        return view('boerse.bericht.kurstafel', compact('betriebe'));
    }

    public function kurstafelBestaetigen(BoerseAufgabenService $service)
    {
        BoerseAufgabenLog::create([
            'aufgabe'    => 'kurstafel_ausgehaengt',
            'created_at' => now(),
        ]);
        $service->clearCache();
        return redirect('/boerse/kurse')
            ->with(['type' => 'success', 'Meldung' => '✅ Kurstafel als ausgehängt bestätigt!']);
    }

    public function tagesabschluss(BoerseAufgabenService $service)
    {
        $betriebe       = Customer::where('buisness', 1)->whereNotNull('aktien_gesamt')->get();
        $kassenstand    = BoerseKasse::kassenstand();
        $transaktionen  = AktienTransaktion::with(['kind', 'betrieb'])
            ->whereDate('created_at', today())->latest()->get();
        $aufgabenStatus = $service->status();
        return view('boerse.bericht.tagesabschluss',
            compact('betriebe', 'kassenstand', 'transaktionen', 'aufgabenStatus'));
    }
}

