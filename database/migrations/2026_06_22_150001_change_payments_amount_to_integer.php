<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1 – Integer-Geldmodell.
 *
 * In der Spielstadt gibt es nur ganze Radi-Scheine (1, 5, 10, 20). Geldbeträge
 * als FLOAT zu speichern erzeugt Rundungs- und Summierungsfehler bei jeder
 * Bilanz. Diese Migration rundet bestehende Beträge und stellt die Spalte auf
 * einen ganzzahligen Typ um.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        // Nur für MySQL/MariaDB – andere Treiber überspringen (z. B. SQLite in Tests).
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        // 1) Bestehende Bruchwerte kaufmännisch auf ganze Radi runden.
        DB::statement('UPDATE `payments` SET `amount` = ROUND(`amount`)');

        // 2) Spaltentyp auf vorzeichenbehafteten Integer umstellen
        //    (negative Beträge = Belastung / Gegenbuchung).
        DB::statement('ALTER TABLE `payments` MODIFY `amount` INT NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement('ALTER TABLE `payments` MODIFY `amount` DOUBLE NOT NULL DEFAULT 0');
    }
};
