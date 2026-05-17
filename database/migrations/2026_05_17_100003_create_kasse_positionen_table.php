<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kasse_positionen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaktion_id')->constrained('kasse_transaktionen')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('menge');
            $table->unsignedInteger('einzelpreis');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kasse_positionen');
    }
};

