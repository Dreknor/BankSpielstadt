<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boerse_aufgaben_log', function (Blueprint $table) {
            $table->string('mitarbeiter', 80)->nullable()->after('aufgabe');
        });
    }

    public function down(): void
    {
        Schema::table('boerse_aufgaben_log', function (Blueprint $table) {
            $table->dropColumn('mitarbeiter');
        });
    }
};

