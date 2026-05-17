<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boerse_kasse', function (Blueprint $table) {
            $table->id();
            $table->enum('typ', [
                'kauf_einnahme',
                'verkauf_auszahlung',
                'rueckkauf_einnahme',
                'rueckkauf_auszahlung',
                'einlage',
                'entnahme',
                'abschluss_auszahlung',
            ]);
            $table->unsignedInteger('betrag');
            $table->string('notiz')->nullable();
            $table->unsignedBigInteger('aktien_transaktion_id')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('aktien_transaktion_id')->references('id')->on('aktien_transaktionen');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boerse_kasse');
    }
};

