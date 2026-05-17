<?php

namespace App\Http\Controllers\Boerse;

use App\Http\Controllers\Controller;
use App\Models\AktienKurs;
use App\Services\BoerseAufgabenService;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BoerseController extends Controller
{
    public function loginForm()
    {
        if (session('boerse')) {
            return redirect('/boerse');
        }
        return view('boerse.login');
    }

    public function login(Request $request)
    {
        $request->validate(['pin' => 'required|string']);

        if ($request->pin !== config('bank.aktien.boerse_pin')) {
            return back()->with(['type' => 'error', 'Meldung' => 'PIN falsch. Bitte nochmal versuchen.']);
        }

        Session::put('boerse', now()->toDateTimeString());
        return redirect('/boerse');
    }

    public function logout()
    {
        Session::forget('boerse');
        return redirect('/boerse/login');
    }

    public function dashboard(BoerseAufgabenService $service)
    {
        $betriebe       = Customer::where('buisness', 1)->whereNotNull('aktien_gesamt')->get();
        $aufgabenStatus = $service->status();
        return view('boerse.dashboard', compact('betriebe', 'aufgabenStatus'));
    }

    public function hilfe()
    {
        return view('boerse.hilfe');
    }

    public function hilfeDrucken()
    {
        return view('boerse.hilfe_drucken');
    }

    // ── Öffentliches Kurs-Display (kein Login nötig) ───────────────────────

    public function anzeige()
    {
        $betriebe = $this->ladeAnzeigedaten();
        return view('boerse.anzeige', compact('betriebe'));
    }

    public function anzeigedaten()
    {
        return response()->json($this->ladeAnzeigedaten());
    }

    private function ladeAnzeigedaten(): array
    {
        return Customer::where('buisness', 1)
            ->whereNotNull('aktien_gesamt')
            ->get()
            ->map(function (Customer $b) {
                // Die 20 neuesten Kurseinträge, ältester zuerst (für Sparkline)
                $verlauf = AktienKurs::where('buisness_id', $b->id)
                    ->latest('created_at')
                    ->take(20)
                    ->get()
                    ->reverse()
                    ->values();

                $preise      = $verlauf->pluck('kurs')->toArray();
                $kursJetzt   = $b->aktien_kurs ?? 0;
                $kursFrueher = count($preise) >= 2 ? $preise[count($preise) - 2] : $kursJetzt;
                $aenderung   = $kursJetzt - $kursFrueher;

                return [
                    'id'               => $b->id,
                    'name'             => $b->name,
                    'kurs'             => $kursJetzt,
                    'kurs_frueher'     => $kursFrueher,
                    'aenderung'        => $aenderung,
                    'aenderung_pct'    => $kursFrueher > 0
                                            ? round($aenderung / $kursFrueher * 100, 1)
                                            : 0,
                    'richtung'         => $aenderung > 0 ? 'hoch'
                                        : ($aenderung < 0 ? 'runter' : 'gleich'),
                    'verlauf_preise'   => $preise,
                    'anteile_verkauft' => $b->anteileVerkauft(),
                    'anteile_gesamt'   => $b->aktien_gesamt ?? 0,
                    'anteile_frei'     => $b->anteileEigen(),
                ];
            })
            ->toArray();
    }
}
