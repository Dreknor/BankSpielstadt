<?php

namespace App\Services;

use App\Models\BoerseAufgabenLog;
use App\Models\BoerseBeobachtung;
use App\Models\AktienKurs;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class BoerseAufgabenService
{
    /** Gibt den Gesamtstatus aller 3 Aufgaben zurück (mit 2-Minuten-Cache). */
    public function status(): array
    {
        return Cache::remember('boerse_aufgaben_status', 120, function () {
            return [
                'beobachtung'     => $this->pruefeBeobachtung(),
                'kassenkontrolle' => $this->pruefeLog('kassenkontrolle',
                    config('bank.aktien.aufgaben.kassenkontrolle_warn_min'),
                    config('bank.aktien.aufgaben.kassenkontrolle_alarm_min')),
                'kurstafel'       => $this->pruefeKurstafel(),
            ];
        });
    }

    /** Leert den Cache, damit beim nächsten Request frisch geprüft wird. */
    public function clearCache(): void
    {
        Cache::forget('boerse_aufgaben_status');
    }

    /** Anzahl der Aufgaben im Alarm-Zustand (für Admin-Badge). */
    public function alarmCount(): int
    {
        return collect($this->status())
            ->filter(fn($s) => $s['status'] === 'alarm')
            ->count();
    }

    // ── Einzelprüfungen ───────────────────────────────────────────────────────

    private function pruefeBeobachtung(): array
    {
        $aktive = Customer::where('buisness', 1)->whereNotNull('aktien_gesamt')->get();

        if ($aktive->isEmpty()) {
            return ['status' => 'ok', 'seit' => null, 'text' => 'Keine aktiven Betriebe'];
        }

        // Älteste letzte Beobachtung (über alle aktiven Betriebe)
        $aelteste = null;
        foreach ($aktive as $betrieb) {
            $letzte = BoerseBeobachtung::where('buisness_id', $betrieb->id)
                ->latest('created_at')->value('created_at');
            if ($letzte === null) {
                // Noch gar keine Beobachtung → sofort warnen
                return ['status' => 'alarm', 'seit' => null,
                    'text'   => "Für {$betrieb->name} wurde noch keine Beobachtung eingetragen!",
                    'minuten' => 999];
            }
            $ts = Carbon::parse($letzte);
            if ($aelteste === null || $ts->lt($aelteste)) {
                $aelteste = $ts;
            }
        }

        $minAgo = Carbon::now()->diffInMinutes($aelteste);
        $warnMin  = config('bank.aktien.aufgaben.beobachtung_warn_min');
        $alarmMin = config('bank.aktien.aufgaben.beobachtung_alarm_min');

        return $this->bewerte($minAgo, $warnMin, $alarmMin, $aelteste);
    }

    private function pruefeLog(string $aufgabe, int $warnMin, int $alarmMin): array
    {
        $letzte = BoerseAufgabenLog::where('aufgabe', $aufgabe)
            ->latest('created_at')->value('created_at');

        if ($letzte === null) {
            return ['status' => 'alarm', 'seit' => null,
                'text' => 'Noch nicht erledigt heute!', 'minuten' => 999];
        }

        $ts     = Carbon::parse($letzte);
        $minAgo = Carbon::now()->diffInMinutes($ts);
        return $this->bewerte($minAgo, $warnMin, $alarmMin, $ts);
    }

    private function pruefeKurstafel(): array
    {
        // Zeitpunkt der letzten Kursberechnung
        $letzteBerechnung = AktienKurs::latest('created_at')->value('created_at');

        if ($letzteBerechnung === null) {
            return ['status' => 'ok', 'seit' => null, 'text' => 'Noch keine Kursberechnung'];
        }

        // Letzte Kurstafel-Bestätigung
        $letzteBest = BoerseAufgabenLog::where('aufgabe', 'kurstafel_ausgehaengt')
            ->latest('created_at')->value('created_at');

        $berechnungTs = Carbon::parse($letzteBerechnung);

        // Bestätigung muss NACH der letzten Berechnung sein
        if ($letzteBest && Carbon::parse($letzteBest)->gte($berechnungTs)) {
            return ['status' => 'ok', 'seit' => Carbon::parse($letzteBest),
                'text' => 'Kurstafel aktuell', 'minuten' => 0];
        }

        $minAgo   = Carbon::now()->diffInMinutes($berechnungTs);
        $warnMin  = config('bank.aktien.aufgaben.kurstafel_warn_min');
        $alarmMin = config('bank.aktien.aufgaben.kurstafel_alarm_min');
        return $this->bewerte($minAgo, $warnMin, $alarmMin, $berechnungTs);
    }

    private function bewerte(int $minAgo, int $warnMin, int $alarmMin, ?Carbon $seit): array
    {
        if ($minAgo >= $alarmMin) {
            $status = 'alarm';
        } elseif ($minAgo >= $warnMin) {
            $status = 'warn';
        } else {
            $status = 'ok';
        }
        return ['status' => $status, 'seit' => $seit, 'minuten' => $minAgo];
    }
}


