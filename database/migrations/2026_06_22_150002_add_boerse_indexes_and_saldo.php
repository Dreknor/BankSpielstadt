<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 – Integritaet & Auditierbarkeit der Boerse.
 *
 * - Indizes auf die heissen Abfragepfade (Kursverlauf, Transaktions- und
 *   Kassenauswertung), die bisher komplette Table-Scans verursacht haben.
 * - Laufsaldo (saldo_nach) auf der Boersen-Kasse: macht jede Bewegung
 *   nachvollziehbar und erlaubt es, Kassendifferenzen punktgenau zu finden.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aktien_kurse', function (Blueprint $table) {
            $table->index(['buisness_id', 'created_at'], 'aktien_kurse_betrieb_zeit_idx');
        });

        Schema::table('aktien_transaktionen', function (Blueprint $table) {
            $table->index(['customer_id', 'buisness_id'], 'aktien_tx_kind_betrieb_idx');
            $table->index(['buisness_id', 'typ'], 'aktien_tx_betrieb_typ_idx');
            $table->index('created_at', 'aktien_tx_zeit_idx');
        });

        Schema::table('boerse_kasse', function (Blueprint $table) {
            if (! Schema::hasColumn('boerse_kasse', 'saldo_nach')) {
                $table->integer('saldo_nach')->nullable()->after('betrag');
            }
            $table->index('typ', 'boerse_kasse_typ_idx');
            $table->index('aktien_transaktion_id', 'boerse_kasse_tx_idx');
            $table->index('created_at', 'boerse_kasse_zeit_idx');
        });
    }

    public function down(): void
    {
        Schema::table('aktien_kurse', function (Blueprint $table) {
            $table->dropIndex('aktien_kurse_betrieb_zeit_idx');
        });

        Schema::table('aktien_transaktionen', function (Blueprint $table) {
            $table->dropIndex('aktien_tx_kind_betrieb_idx');
            $table->dropIndex('aktien_tx_betrieb_typ_idx');
            $table->dropIndex('aktien_tx_zeit_idx');
        });

        Schema::table('boerse_kasse', function (Blueprint $table) {
            $table->dropIndex('boerse_kasse_typ_idx');
            $table->dropIndex('boerse_kasse_tx_idx');
            $table->dropIndex('boerse_kasse_zeit_idx');
            if (Schema::hasColumn('boerse_kasse', 'saldo_nach')) {
                $table->dropColumn('saldo_nach');
            }
        });
    }
};
