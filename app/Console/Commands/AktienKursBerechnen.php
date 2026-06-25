<?php

namespace App\Console\Commands;

use App\Models\AktienKurs;
use App\Models\BoerseBeobachtung;
use App\Models\Customer;
use App\Models\KasseTransaktion;
use App\Services\BoerseAufgabenService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AktienKursBerechnen extends Command
{
    protected $signature   = 'aktien:kurs-berechnen';
    protected $description = 'Berechnet stündlich den Aktienkurs aller aktiven Betriebe';

    public function handle(): int
    {
        $betriebe = Customer::where('buisness', 1)->whereNotNull('aktien_gesamt')->get();

        if ($betriebe->isEmpty()) {
            $this->info('Keine aktiven Börsen-Betriebe gefunden.');
            return 0;
        }

        $teiler         = config('bank.aktien.kurs_teiler', 20);
        $maxSprungPct   = config('bank.aktien.max_sprung_prozent', 15);
        $minKurs        = config('bank.aktien.min_kurs', 1);
        $normalAngest   = config('bank.aktien.angestellte_normal', 4);
        $anteileMaxDelta = config('bank.aktien.anteile_max_delta', 2);

        foreach ($betriebe as $betrieb) {
            $alterKurs = $betrieb->aktien_kurs ?? $betrieb->aktien_startkurs ?? 10;
            $since     = $betrieb->aktien_letzte_berechnung ?? Carbon::now()->subHour();

            // ── Umsatz-Delta ──────────────────────────────────────────────
            $umsatz = KasseTransaktion::where('customer_id', $betrieb->id)
                ->where('type', 'verkauf')
                ->where('created_at', '>', $since)
                ->sum('amount');

            $entnahmen = KasseTransaktion::where('customer_id', $betrieb->id)
                ->where('type', 'entnahme')
                ->where('created_at', '>', $since)
                ->sum('amount');

            $umsatzDelta = (int) floor(($umsatz - $entnahmen) / $teiler);

            // ── Angestellten-Faktor ───────────────────────────────────────
            $letzteBeob = BoerseBeobachtung::where('buisness_id', $betrieb->id)
                ->latest('created_at')
                ->value('angestellte');

            $angestelltenDelta = 0;
            if ($letzteBeob !== null) {
                $angestelltenDelta = max(-2, min(2, $letzteBeob - $normalAngest));
            }

            // ── Anteile-Faktor ────────────────────────────────────────────
            // Wie viel Prozent der Anteile wurden verkauft?
            // 0 % → −max, 50 % → 0, 100 % → +max
            $anteilePct    = ($betrieb->aktien_gesamt > 0)
                ? $betrieb->anteileVerkauft() / $betrieb->aktien_gesamt
                : 0;
            $anteileDelta  = (int) round(($anteilePct - 0.5) * $anteileMaxDelta * 2);
            $anteileDelta  = max(-$anteileMaxDelta, min($anteileMaxDelta, $anteileDelta));

            // ── Neuer Kurs ────────────────────────────────────────────────
            $rohKurs   = $alterKurs + $umsatzDelta + $angestelltenDelta + $anteileDelta;
            $maxSprung = (int) floor($alterKurs * $maxSprungPct / 100);
            if ($maxSprung < 1) $maxSprung = 1;

            $neuerKurs = max($alterKurs - $maxSprung, min($alterKurs + $maxSprung, $rohKurs));
            $neuerKurs = max($minKurs, $neuerKurs);


            if ($neuerKurs > $minKurs + 1) {
                $randomFactor = rand(-1, 1); // Random value between -1 and 1
                $neuerKurs += $randomFactor;
            }



            // ── Speichern ─────────────────────────────────────────────────
            AktienKurs::create([
                'buisness_id' => $betrieb->id,
                'kurs'        => $neuerKurs,
                'vorher'      => $alterKurs,
                'grund'       => 'Stündliche Berechnung',
                'created_at'  => now(),
            ]);

            $betrieb->update([
                'aktien_kurs'              => $neuerKurs,
                'aktien_letzte_berechnung' => now(),
            ]);

            $this->info("Betrieb {$betrieb->name}: {$alterKurs} → {$neuerKurs} Radi"
                . " (Umsatz-Δ: {$umsatzDelta}, Angest.-Δ: {$angestelltenDelta}, Anteile-Δ: {$anteileDelta})");
        }

        // Cache leeren damit Dashboard sofort aktualisiert wird
        app(BoerseAufgabenService::class)->clearCache();

        return 0;
    }
}

