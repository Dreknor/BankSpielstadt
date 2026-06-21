<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('kann_einzahlen')->default(true)->after('is_admin');
            $table->boolean('kann_auszahlen')->default(true)->after('kann_einzahlen');
            $table->boolean('kann_arbeitszeit')->default(true)->after('kann_auszahlen');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['kann_einzahlen', 'kann_auszahlen', 'kann_arbeitszeit']);
        });
    }
};

