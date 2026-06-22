<?php

namespace App\Console\Commands;

use App\Models\BoerseKasse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Phase 4 – Kassen-Abgleich
 *
 * Vergleicht den berechneten Börsen-Kassenstand mit dem letzten saldo_nach-Wert.
 * Kann fehlende saldo_nach-Werte rückwirkend befüllen und auf Wunsch eine
 * dokumentierte Ausgleichsbuchung erstellen.
 *
 * Aufruf:
 *   php artisan aktien:reconcile-kasse
 *   php artisan aktien:reconcile-kasse --befuellen
 *   php artisan aktien:reconcile-kasse --befuellen --ausgleich
 */
class BoerseKasseAbgleichen extends Command
{
    protected $signature = 'aktien:reconcile-kasse
                            {--befuellen : saldo_nach für alle Einträge rückwirkend befüllen/korrigieren}
                            {--ausgleich : Korrekturbuchung erstellen wenn Differenz verbleibt}';

    protected $description = 'Vergleicht Börsen-Kassenstand mit saldo_nach und kann Laufsaldo rückberechnen (Phase 4)';

    public function handle(): int
    {
        $befuellen = $this->option('befuellen');
        $ausgleich = $this->option('ausgleich');

        $modi = array_filter([
            $befuellen ? 'BEFÜLL-MODUS' : null,
            $ausgleich ? 'AUSGLEICH-MODUS' : null,
        ]);

        $this->info('=== Börsen-Kassen-Abgleich' . ($modi ? ' (' . implode(', ', $modi) . ')' : ' (Report-Modus)') . ' ===');

        // Aktuellen Kassenstand aus allen Einnahme-/Ausgabe-Einträgen berechnen
        $istStand     = BoerseKasse::kassenstand();
        $anzahlGesamt = BoerseKasse::count();
        $fehlende     = BoerseKasse::whereNull('saldo_nach')->count();

        $letzterEintrag = BoerseKasse::orderBy('id', 'desc')->first();
        $letztesSaldo   = $letzterEintrag?->saldo_nach;

        $this->line("Buchungen gesamt:      {$anzahlGesamt}");
        $this->line("Ohne saldo_nach:       {$fehlende}");
        $this->line("Berechneter Stand:     {$istStand} Radi");
        $this->line('Letztes saldo_nach:    ' . ($letztesSaldo !== null ? "{$letztesSaldo} Radi" : '(nicht gesetzt)'));

        if ($fehlende > 0) {
            $this->warn("{$fehlende} Einträge ohne saldo_nach.");
        }

        // --- Rückberechnung ---
        if ($befuellen) {
            $this->backfillSaldoNach();
            // Danach neu lesen
            $letzterEintrag = BoerseKasse::orderBy('id', 'desc')->first();
            $letztesSaldo   = $letzterEintrag?->saldo_nach;
        }

        // --- Differenzprüfung ---
        if ($letztesSaldo !== null && $letztesSaldo !== $istStand) {
            $differenz = $istStand - $letztesSaldo;
            $this->warn("⚠️  Abweichung: Kassenstand {$istStand} Radi ≠ letztes saldo_nach {$letztesSaldo} Radi (Δ {$differenz})");

            if ($ausgleich) {
                $this->ausgleichBuchen($differenz);
            } else {
                $this->info('Zum Erstellen einer Korrekturbuchung --ausgleich hinzufügen.');
            }
            return 1;
        }

        $this->info('✅ Kassenstand und saldo_nach stimmen überein.');
        return 0;
    }

    private function backfillSaldoNach(): void
    {
        $this->info('Rückberechne saldo_nach für alle Einträge...');

        $saldo    = 0;
        $korrigiert = 0;

        DB::transaction(function () use (&$saldo, &$korrigiert) {
            BoerseKasse::orderBy('id')->each(function (BoerseKasse $zeile) use (&$saldo, &$korrigiert) {
                if (in_array($zeile->typ, BoerseKasse::AUSGABE_TYPEN)) {
                    $saldo -= $zeile->betrag;
                } else {
                    $saldo += $zeile->betrag;
                }

                if ($zeile->saldo_nach !== $saldo) {
                    $zeile->update(['saldo_nach' => $saldo]);
                    $korrigiert++;
                }
            });
        });

        $this->info("✅ {$korrigiert} Einträge befüllt/korrigiert. Endsaldo: {$saldo} Radi.");
    }

    private function ausgleichBuchen(int $differenz): void
    {
        $typ    = $differenz > 0 ? 'einlage' : 'entnahme';
        $betrag = abs($differenz);

        BoerseKasse::buchen([
            'typ'    => $typ,
            'betrag' => $betrag,
            'notiz'  => 'Korrekturbuchung Kassen-Abgleich – ' . now()->format('d.m.Y H:i'),
        ]);

        $this->info("✅ Korrekturbuchung erstellt: {$typ} {$betrag} Radi.");
    }
}

