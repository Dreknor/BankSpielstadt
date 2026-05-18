<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boerse_kasse', function (Blueprint $table) {
            $table->foreignId('ausgefuehrt_von')
                  ->nullable()
                  ->after('notiz')
                  ->constrained('customers')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('boerse_kasse', function (Blueprint $table) {
            $table->dropForeign(['ausgefuehrt_von']);
            $table->dropColumn('ausgefuehrt_von');
        });
    }
};

