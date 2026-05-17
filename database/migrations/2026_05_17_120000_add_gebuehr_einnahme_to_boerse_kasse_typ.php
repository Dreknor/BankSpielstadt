<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE boerse_kasse MODIFY COLUMN typ ENUM(
            'kauf_einnahme',
            'verkauf_auszahlung',
            'rueckkauf_einnahme',
            'rueckkauf_auszahlung',
            'einlage',
            'entnahme',
            'abschluss_auszahlung',
            'gebuehr_einnahme'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE boerse_kasse MODIFY COLUMN typ ENUM(
            'kauf_einnahme',
            'verkauf_auszahlung',
            'rueckkauf_einnahme',
            'rueckkauf_auszahlung',
            'einlage',
            'entnahme',
            'abschluss_auszahlung'
        ) NOT NULL");
    }
};

