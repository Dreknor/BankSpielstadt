<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedInteger('aktien_startkurs')->nullable()->after('aktien_kurs');
            $table->timestamp('aktien_letzte_berechnung')->nullable()->after('aktien_startkurs');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['aktien_startkurs', 'aktien_letzte_berechnung']);
        });
    }
};

