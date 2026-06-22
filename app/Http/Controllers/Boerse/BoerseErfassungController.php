<?php

namespace App\Http\Controllers\Boerse;

use App\Http\Controllers\Controller;
use App\Http\Requests\BoerseBeobachtungRequest;
use App\Models\AktienKurs;
use App\Models\BoerseBeobachtung;
use App\Models\Customer;
use App\Services\BoerseAufgabenService;

class BoerseErfassungController extends Controller
{
    public function index(BoerseAufgabenService $service)
    {
        $betriebe       = Customer::where('buisness', 1)->whereNotNull('aktien_gesamt')->get();
        $aufgabenStatus = $service->status();
        return view('boerse.erfassung.index', compact('betriebe', 'aufgabenStatus'));
    }

    public function store(BoerseBeobachtungRequest $request, Customer $customer, BoerseAufgabenService $service)
    {
        abort_unless($customer->hatAktien(), 404);

        BoerseBeobachtung::create([
            'buisness_id' => $customer->id,
            'angestellte' => $request->angestellte,
            'notiz'       => $request->notiz,
            'created_at'  => now(),
        ]);

        // ── Kursberechnung ────────────────────────────────────────────────────
        // (C) kurs_nur_fixing = true → Beobachtung wird nur gesammelt; der Kurs
        //     ändert sich ausschließlich beim zentralen Fixing (Scheduler).
        //     So ist Sofort-Arbitrage durch gezielte Beobachtungs-Spams unmöglich.
        if (config('bank.aktien.kurs_nur_fixing', true)) {
            $service->clearCache();
            return redirect('/boerse/erfassung')
                ->with(['type' => 'success',
                    'Meldung' => "✅ Beobachtung für {$customer->name} gespeichert: {$request->angestellte} Angestellte. "
                               . "Der Kurs wird beim nächsten Fixing automatisch angepasst."]);
        }

        // Fallback: Direktberechnung nur wenn kurs_nur_fixing = false (Legacy/Debug)
        $alterKurs       = $customer->aktien_kurs ?? $customer->aktien_startkurs ?? 10;
        $normalAngest    = config('bank.aktien.angestellte_normal', 4);
        $maxSprungPct    = config('bank.aktien.max_sprung_prozent', 15);
        $minKurs         = config('bank.aktien.min_kurs', 1);

        $angestelltenDelta = max(-2, min(2, (int) $request->angestellte - $normalAngest));

        $maxSprung = max(1, (int) floor($alterKurs * $maxSprungPct / 100));
        $rohKurs   = $alterKurs + $angestelltenDelta;
        $neuerKurs = max($minKurs,
                        max($alterKurs - $maxSprung,
                            min($alterKurs + $maxSprung, $rohKurs)));

        $customer->update([
            'aktien_kurs'              => $neuerKurs,
            'aktien_letzte_berechnung' => now(),
        ]);

        AktienKurs::create([
            'buisness_id' => $customer->id,
            'kurs'        => $neuerKurs,
            'vorher'      => $alterKurs,
            'grund'       => "Beobachtung: {$request->angestellte} Angestellte"
                             . ($request->notiz ? " – {$request->notiz}" : ''),
            'created_at'  => now(),
        ]);
        // ─────────────────────────────────────────────────────────────────────

        $service->clearCache();

        $pfeil = $neuerKurs > $alterKurs ? '📈' : ($neuerKurs < $alterKurs ? '📉' : '➡️');
        return redirect('/boerse/erfassung')
            ->with(['type' => 'success',
                'Meldung' => "✅ Beobachtung für {$customer->name} gespeichert: {$request->angestellte} Angestellte. "
                           . "Neuer Kurs: {$neuerKurs} Radi {$pfeil} (vorher {$alterKurs} Radi)."]);
    }

    public function vorschau(Customer $customer)
    {
        abort_unless($customer->hatAktien(), 404);

        $alterKurs       = $customer->aktien_kurs ?? $customer->aktien_startkurs ?? 10;
        $maxSprungPct    = config('bank.aktien.max_sprung_prozent', 15);
        $minKurs         = config('bank.aktien.min_kurs', 1);
        $normalAngest    = config('bank.aktien.angestellte_normal', 4);
        $letzteBeob      = $customer->letzteBeobachtung();

        $angestelltenDelta = $letzteBeob !== null
            ? max(-2, min(2, $letzteBeob - $normalAngest))
            : 0;

        $maxSprung    = max(1, (int) floor($alterKurs * $maxSprungPct / 100));
        $rohKurs      = $alterKurs + $angestelltenDelta;
        $vorschauKurs = max($minKurs, max($alterKurs - $maxSprung, min($alterKurs + $maxSprung, $rohKurs)));

        $aufgabenStatus = app(BoerseAufgabenService::class)->status();
        return view('boerse.erfassung.vorschau', compact(
            'customer', 'alterKurs', 'vorschauKurs', 'letzteBeob',
            'angestelltenDelta', 'aufgabenStatus'
        ));
    }
}
