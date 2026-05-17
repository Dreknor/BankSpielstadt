<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aktien_transaktionen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('buisness_id');
            $table->enum('typ', ['kauf', 'verkauf', 'rueckkauf', 'dividende', 'abschluss']);
            $table->unsignedInteger('stueck')->default(0);
            $table->unsignedInteger('kurs');
            $table->unsignedInteger('summe');
            $table->string('boerse_rolle')->default('haendler'); // haendler|admin
            $table->unsignedBigInteger('payment_id')->nullable(); // nur bei dividende
            $table->string('notiz')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('buisness_id')->references('id')->on('customers');
            $table->foreign('payment_id')->references('id')->on('payments');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktien_transaktionen');
    }
};

