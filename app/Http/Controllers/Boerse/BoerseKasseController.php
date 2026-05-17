<?php

namespace App\Http\Controllers\Boerse;

use App\Http\Controllers\Controller;
use App\Http\Requests\BoerseKasseRequest;
use App\Models\BoerseAufgabenLog;
use App\Models\BoerseKasse;
use App\Services\BoerseAufgabenService;

class BoerseKasseController extends Controller
{
    public function index(BoerseAufgabenService $service)
    {
        $kassenstand    = BoerseKasse::kassenstand();
        $transaktionen  = BoerseKasse::latest('created_at')->take(50)->get();
        $warnung        = $kassenstand <= config('bank.aktien.kasse_warnschwelle', 50);
        $aufgabenStatus = $service->status();
        return view('boerse.kasse.index', compact('kassenstand', 'transaktionen', 'warnung', 'aufgabenStatus'));
    }

    public function einlage(BoerseKasseRequest $request, BoerseAufgabenService $service)
    {
        BoerseKasse::create([
            'typ'        => 'einlage',
            'betrag'     => $request->betrag,
            'notiz'      => $request->notiz ?: 'Bareinlage',
            'created_at' => now(),
        ]);
        $service->clearCache();
        return redirect('/boerse/kasse')
            ->with(['type' => 'success', 'Meldung' => "{$request->betrag} Radi eingelegt. ✅"]);
    }

    public function entnahme(BoerseKasseRequest $request, BoerseAufgabenService $service)
    {
        $kassenstand = BoerseKasse::kassenstand();
        if ($kassenstand < $request->betrag) {
            return back()->with(['type' => 'error',
                'Meldung' => "Nicht genug Bargeld in der Börse! Kassenstand: {$kassenstand} Radi."]);
        }

        BoerseKasse::create([
            'typ'        => 'entnahme',
            'betrag'     => $request->betrag,
            'notiz'      => $request->notiz ?: 'Entnahme',
            'created_at' => now(),
        ]);
        $service->clearCache();
        return redirect('/boerse/kasse')
            ->with(['type' => 'success', 'Meldung' => "{$request->betrag} Radi entnommen."]);
    }

    public function bestaetigen(BoerseAufgabenService $service)
    {
        BoerseAufgabenLog::create([
            'aufgabe'    => 'kassenkontrolle',
            'created_at' => now(),
        ]);
        $service->clearCache();
        return redirect('/boerse/kasse')
            ->with(['type' => 'success', 'Meldung' => '✅ Kassenstand bestätigt! Gut gemacht.']);
    }
}

