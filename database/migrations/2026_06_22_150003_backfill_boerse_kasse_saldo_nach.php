<?php

use App\Models\BoerseKasse;
use Illuminate\Database\Migrations\Migration;

/**
 * Phase 4 – Einmalige Datenmigration
 *
 * Befüllt saldo_nach für alle bestehenden boerse_kasse-Zeilen chronologisch.
 * Idempotent: überschreibt nur falsche oder fehlende Werte.
 */
return new class extends Migration
{
    public function up(): void
    {
        $saldo = 0;

        BoerseKasse::orderBy('id')->each(function (BoerseKasse $zeile) use (&$saldo) {
            if (in_array($zeile->typ, BoerseKasse::AUSGABE_TYPEN)) {
                $saldo -= $zeile->betrag;
            } else {
                $saldo += $zeile->betrag;
            }

            if ($zeile->saldo_nach !== $saldo) {
                $zeile->update(['saldo_nach' => $saldo]);
            }
        });
    }

    public function down(): void
    {
        // Kein echtes Rollback sinnvoll – saldo_nach-Spalte bleibt erhalten
        // und wurde bereits in der vorherigen Migration angelegt.
    }
};
