<?php

namespace App\Console\Commands;

use App\Models\AktienKurs;
use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Phase 4 – Kurs-Synchronisation
 *
 * Vergleicht customers.aktien_kurs mit dem letzten aktien_kurse-Eintrag je Betrieb.
 * Bei kurs_nur_fixing=true ist customers.aktien_kurs die einzige Wahrheitsquelle
 * für den Handel; Abweichungen zur Kursverlaufs-Tabelle müssen korrigiert werden.
 *
 * Aufruf:
 *   php artisan aktien:sync-kurs
 *   php artisan aktien:sync-kurs --korrigieren
 */
class AktienKursSynchronisieren extends Command
{
    protected $signature = 'aktien:sync-kurs
                            {--korrigieren : customers.aktien_kurs auf den letzten aktien_kurse-Wert setzen}';

    protected $description = 'Gleicht customers.aktien_kurs mit dem letzten aktien_kurse-Eintrag ab (Phase 4 – Prio 10)';

    public function handle(): int
    {
        $korrigieren = $this->option('korrigieren');
        $this->info('=== Kurs-Synchronisation' . ($korrigieren ? ' (KORREKTUR-MODUS)' : ' (Report-Modus)') . ' ===');

        $betriebe = Customer::where('buisness', 1)
            ->whereNotNull('aktien_gesamt')
            ->get();

        $abweichungen = [];
        $korrekturen  = 0;

        foreach ($betriebe as $betrieb) {
            $letzterKurs = AktienKurs::where('buisness_id', $betrieb->id)
                ->orderBy('created_at', 'desc')
                ->value('kurs');

            if ($letzterKurs === null) {
                $this->line("  [SKIP] {$betrieb->name} – noch kein aktien_kurse-Eintrag.");
                continue;
            }

            $istKurs = (int) $betrieb->aktien_kurs;

            if ($istKurs !== (int) $letzterKurs) {
                $abweichungen[] = [
                    'id'          => $betrieb->id,
                    'betrieb'     => $betrieb->name,
                    'customers'   => $istKurs,
                    'aktien_kurse' => (int) $letzterKurs,
                    'differenz'   => $letzterKurs - $istKurs,
                ];

                if ($korrigieren) {
                    $betrieb->update(['aktien_kurs' => $letzterKurs]);
                    $korrekturen++;
                }
            }
        }

        if (empty($abweichungen)) {
            $this->info('✅ Alle Kurse sind synchron – keine Abweichungen.');
            return 0;
        }

        $this->warn(count($abweichungen) . ' Abweichung(en) gefunden:');
        $this->table(['Betrieb-ID', 'Betrieb', 'customers.aktien_kurs', 'aktien_kurse (letzter)', 'Differenz'], $abweichungen);

        if ($korrigieren) {
            $this->info("✅ {$korrekturen} Betrieb(e) korrigiert.");
        } else {
            $this->warn('Zum Korrigieren erneut mit --korrigieren ausführen.');
        }

        return 1;
    }
}
