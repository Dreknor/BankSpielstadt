<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boerse_aufgaben_log', function (Blueprint $table) {
            $table->id();
            $table->enum('aufgabe', ['kassenkontrolle', 'kurstafel_ausgehaengt']);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boerse_aufgaben_log');
    }
};

