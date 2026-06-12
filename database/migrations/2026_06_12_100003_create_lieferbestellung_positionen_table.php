<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lieferbestellung_positionen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bestellung_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('menge');
            $table->unsignedInteger('einzelpreis');
            $table->timestamps();

            $table->foreign('bestellung_id')->references('id')->on('lieferbestellungen')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lieferbestellung_positionen');
    }
};

