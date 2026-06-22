<?php

namespace App\Http\Controllers\Boerse;

use App\Http\Controllers\Controller;
use App\Http\Requests\BoerseKasseRequest;
use App\Models\BoerseAufgabenLog;
use App\Models\BoerseKasse;
use App\Services\BoerseAufgabenService;
use Illuminate\Http\Request;

class BoerseKasseController extends Controller
{
    public function index(BoerseAufgabenService $service)
    {
        $kassenstand    = BoerseKasse::kassenstand();
        $transaktionen  = BoerseKasse::with('ausfuehrer')->latest('created_at')->take(50)->get();
        $warnung        = $kassenstand <= config('bank.aktien.kasse_warnschwelle', 50);
        $aufgabenStatus = $service->status();
        return view('boerse.kasse.index', compact('kassenstand', 'transaktionen', 'warnung', 'aufgabenStatus'));
    }

    public function einlage(BoerseKasseRequest $request, BoerseAufgabenService $service)
    {
        $person = \App\Models\Customer::findOrFail($request->ausgefuehrt_von);

        BoerseKasse::buchen([
            'typ'             => 'einlage',
            'betrag'          => (int) $request->betrag,
            'notiz'           => ($request->notiz ?: 'Bareinlage') . ' – ' . $person->name,
            'ausgefuehrt_von' => $person->id,
        ]);
        $service->clearCache();
        return redirect('/boerse/kasse')
            ->with(['type' => 'success', 'Meldung' => "{$request->betrag} Radi durch {$person->name} eingelegt. ✅"]);
    }

    public function entnahme(BoerseKasseRequest $request, BoerseAufgabenService $service)
    {
        $kassenstand = BoerseKasse::kassenstand();
        if ($kassenstand < $request->betrag) {
            return back()->with(['type' => 'error',
                'Meldung' => "Nicht genug Bargeld in der Börse! Kassenstand: {$kassenstand} Radi."]);
        }

        $person = \App\Models\Customer::findOrFail($request->ausgefuehrt_von);

        BoerseKasse::buchen([
            'typ'             => 'entnahme',
            'betrag'          => (int) $request->betrag,
            'notiz'           => ($request->notiz ?: 'Entnahme') . ' – ' . $person->name,
            'ausgefuehrt_von' => $person->id,
        ]);
        $service->clearCache();
        return redirect('/boerse/kasse')
            ->with(['type' => 'success', 'Meldung' => "{$request->betrag} Radi durch {$person->name} entnommen."]);
    }

    public function bestaetigen(Request $request, BoerseAufgabenService $service)
    {
        $request->validate([
            'mitarbeiter' => 'required|string|max:80',
        ], [
            'mitarbeiter.required' => 'Bitte deinen Namen eingeben.',
        ]);

        BoerseAufgabenLog::create([
            'aufgabe'     => 'kassenkontrolle',
            'mitarbeiter' => trim($request->mitarbeiter),
            'created_at'  => now(),
        ]);
        $service->clearCache();
        return redirect('/boerse/kasse')
            ->with(['type' => 'success', 'Meldung' => '✅ Kassenstand bestätigt! Gut gemacht, ' . trim($request->mitarbeiter) . '!']);
    }
}

