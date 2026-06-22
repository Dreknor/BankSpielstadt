<?php

namespace App\Console\Commands;

use App\Models\AktienKurs;
use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Einmaliges Korrektur-Command:
 * Setzt alle customers.aktien_kurs < minKurs auf den Mindestkurs
 * und schreibt je einen dokumentierenden aktien_kurse-Eintrag.
 */
class AktienKursKorrigieren extends Command
{
    protected $signature   = 'aktien:kurs-korrigieren {--dry-run : Nur anzeigen, nichts ändern}';
    protected $description = 'Korrigiert aktien_kurs < Mindestkurs auf den konfigurierten Mindestkurs';

    public function handle(): int
    {
        $minKurs  = (int) config('bank.aktien.min_kurs', 4);
        $dryRun   = $this->option('dry-run');

        $this->info("Mindestkurs: {$minKurs} Radi" . ($dryRun ? ' [DRY-RUN – keine Änderungen]' : ''));

        $betriebe = Customer::where('buisness', 1)
            ->whereNotNull('aktien_gesamt')
            ->whereNull('deleted_at')
            ->get(['id', 'name', 'aktien_kurs']);

        $this->info("\nAlle Börsen-Betriebe:");
        $rows = [];
        $zuKorrigieren = [];

        foreach ($betriebe as $b) {
            $kurs     = (int) $b->aktien_kurs;
            $muss     = $kurs < $minKurs;
            $rows[]   = [$b->id, $b->name, $kurs . ' Radi', $muss ? "→ {$minKurs} Radi ⚠️" : '✅ OK'];
            if ($muss) $zuKorrigieren[] = $b;
        }

        $this->table(['ID', 'Betrieb', 'Aktueller Kurs', 'Aktion'], $rows);

        if (empty($zuKorrigieren)) {
            $this->info('✅ Alle Kurse sind bereits >= ' . $minKurs . ' Radi. Keine Korrekturen notwendig.');
            return 0;
        }

        $this->warn(count($zuKorrigieren) . ' Betrieb(e) müssen korrigiert werden.');

        if ($dryRun) {
            $this->info('DRY-RUN: Keine Änderungen vorgenommen. Ohne --dry-run erneut ausführen.');
            return 0;
        }

        DB::transaction(function () use ($zuKorrigieren, $minKurs) {
            foreach ($zuKorrigieren as $betrieb) {
                $alterKurs = (int) $betrieb->aktien_kurs;

                Customer::whereKey($betrieb->id)->update(['aktien_kurs' => $minKurs]);

                DB::table('aktien_kurse')->insert([
                    'buisness_id' => $betrieb->id,
                    'kurs'        => $minKurs,
                    'vorher'      => $alterKurs,
                    'grund'       => "Admin-Korrektur: Mindestkurs auf {$minKurs} Radi angehoben (war {$alterKurs} Radi)",
                    'created_at'  => now(),
                ]);

                $this->line("  ✅ {$betrieb->name}: {$alterKurs} → {$minKurs} Radi");
            }
        });

        $this->info("\n✅ " . count($zuKorrigieren) . ' Betrieb(e) korrigiert.');
        return 0;
    }
}

