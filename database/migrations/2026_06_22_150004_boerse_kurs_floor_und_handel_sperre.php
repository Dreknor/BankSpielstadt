<?php

use App\Models\AktienKurs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 4 – Zwei kombinierte Änderungen:
 *
 * 1. customers.boerse_handel_gesperrt — neues Boolean-Flag (default false).
 *    Wird gesetzt, um einzelne Kinder/Betriebe vom Börsenhandel auszuschließen.
 *
 * 2. Kurskorrektur — Alle customers.aktien_kurs < 4 werden auf 4 Radi gesetzt
 *    (neuer Mindestkurs), und ein dokumentierender aktien_kurse-Eintrag wird
 *    geschrieben. Idempotent (WHERE aktien_kurs < 4 AND aktien_gesamt IS NOT NULL).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Handelssperre-Spalte ───────────────────────────────────────────
        if (! Schema::hasColumn('customers', 'boerse_handel_gesperrt')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->boolean('boerse_handel_gesperrt')->default(false)->after('is_boerse');
            });
        }

        // ── 2. Kurskorrektur auf Minimum 4 Radi ──────────────────────────────
        $minKurs = (int) config('bank.aktien.min_kurs', 4);

        $betriebe = DB::table('customers')
            ->whereNotNull('aktien_gesamt')
            ->where('aktien_kurs', '<', $minKurs)
            ->whereNull('deleted_at')
            ->get(['id', 'name', 'aktien_kurs']);

        foreach ($betriebe as $betrieb) {
            $alterKurs = (int) $betrieb->aktien_kurs;

            DB::table('customers')->where('id', $betrieb->id)
                ->update(['aktien_kurs' => $minKurs]);

            DB::table('aktien_kurse')->insert([
                'buisness_id' => $betrieb->id,
                'kurs'        => $minKurs,
                'vorher'      => $alterKurs,
                'grund'       => "Kurskorrektur: Mindestkurs auf {$minKurs} Radi gesetzt (war {$alterKurs} Radi)",
                'created_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('boerse_handel_gesperrt');
        });
        // Kurskorrektur ist nicht rückgängig zu machen
    }
};



