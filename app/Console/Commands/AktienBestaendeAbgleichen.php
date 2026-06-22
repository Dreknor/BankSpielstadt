<?php

namespace App\Console\Commands;

use App\Models\AktienBestand;
use App\Models\AktienTransaktion;
use App\Models\Customer;
use Illuminate\Console\Command;

/**
 * Phase 4 – Bestands-Abgleich
 *
 * Rechnet den Soll-Bestand aus den Transaktionen (kauf - verkauf - rueckkauf - abschluss)
 * je (customer_id, buisness_id) neu und vergleicht ihn mit aktien_bestaende.stueck.
 *
 * Standard: reiner Report-Modus (keine Änderungen).
 * Mit --korrigieren: Fehlstände werden direkt gefixt.
 *
 * Aufruf:
 *   php artisan aktien:reconcile-bestaende
 *   php artisan aktien:reconcile-bestaende --korrigieren
 */
class AktienBestaendeAbgleichen extends Command
{
    protected $signature   = 'aktien:reconcile-bestaende {--korrigieren : Abweichungen direkt korrigieren}';
    protected $description = 'Gleicht aktien_bestaende mit den Transaktionsdaten ab (Phase 4)';

    public function handle(): int
    {
        $korrigieren = $this->option('korrigieren');
        $this->info('=== Bestands-Abgleich' . ($korrigieren ? ' (KORREKTUR-MODUS)' : ' (Report-Modus)') . ' ===');

        // Soll-Bestände aus Transaktionen aggregieren
        $solls = AktienTransaktion::whereNull('deleted_at')
            ->selectRaw("customer_id, buisness_id,
                SUM(CASE WHEN typ = 'kauf' THEN stueck ELSE 0 END)
              - SUM(CASE WHEN typ IN ('verkauf','rueckkauf','abschluss') THEN stueck ELSE 0 END)
                AS soll_stueck")
            ->groupBy('customer_id', 'buisness_id')
            ->get()
            ->keyBy(fn($r) => $r->customer_id . '_' . $r->buisness_id);

        $bestaende   = AktienBestand::all();
        $abweichungen = [];
        $korrekturen  = 0;

        // 1) Bestehende Datensätze prüfen
        foreach ($bestaende as $bestand) {
            $key  = $bestand->customer_id . '_' . $bestand->buisness_id;
            $soll = (int) ($solls[$key]->soll_stueck ?? 0);
            $ist  = (int) $bestand->stueck;

            if ($soll !== $ist) {
                $kind    = Customer::find($bestand->customer_id);
                $betrieb = Customer::find($bestand->buisness_id);
                $abweichungen[] = [
                    'id'        => $bestand->id,
                    'kind'      => $kind?->name ?? "ID {$bestand->customer_id}",
                    'betrieb'   => $betrieb?->name ?? "ID {$bestand->buisness_id}",
                    'ist'       => $ist,
                    'soll'      => $soll,
                    'differenz' => $soll - $ist,
                ];
                if ($korrigieren) {
                    $bestand->update(['stueck' => max(0, $soll)]);
                    $korrekturen++;
                }
            }
        }

        // 2) Bestände in TX, die noch keinen Datensatz haben
        foreach ($solls as $key => $row) {
            $soll = (int) $row->soll_stueck;
            if ($soll <= 0) continue;

            $existiert = $bestaende->contains(
                fn($b) => $b->customer_id == $row->customer_id && $b->buisness_id == $row->buisness_id
            );

            if (! $existiert) {
                $kind    = Customer::find($row->customer_id);
                $betrieb = Customer::find($row->buisness_id);
                $abweichungen[] = [
                    'id'        => '(fehlt)',
                    'kind'      => $kind?->name ?? "ID {$row->customer_id}",
                    'betrieb'   => $betrieb?->name ?? "ID {$row->buisness_id}",
                    'ist'       => 0,
                    'soll'      => $soll,
                    'differenz' => $soll,
                ];
                if ($korrigieren) {
                    AktienBestand::create([
                        'customer_id' => $row->customer_id,
                        'buisness_id' => $row->buisness_id,
                        'stueck'      => $soll,
                    ]);
                    $korrekturen++;
                }
            }
        }

        if (empty($abweichungen)) {
            $this->info('✅ Keine Abweichungen – alle Bestände sind konsistent.');
            return 0;
        }

        $this->warn(count($abweichungen) . ' Abweichung(en) gefunden:');
        $this->table(['Bestand-ID', 'Kind', 'Betrieb', 'Ist', 'Soll', 'Differenz'], $abweichungen);

        if ($korrigieren) {
            $this->info("✅ {$korrekturen} Bestand/Bestände korrigiert.");
        } else {
            $this->warn('ℹ️  Zum Korrigieren erneut mit --korrigieren ausführen.');
        }

        return 1;
    }
}
