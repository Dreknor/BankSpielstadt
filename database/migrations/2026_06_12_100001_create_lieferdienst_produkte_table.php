<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lieferdienst_produkte', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lieferdienst_id');
            $table->unsignedBigInteger('product_id');
            $table->timestamps();

            $table->foreign('lieferdienst_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unique(['lieferdienst_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lieferdienst_produkte');
    }
};

