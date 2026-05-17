<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boerse_beobachtungen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buisness_id');
            $table->unsignedInteger('angestellte');
            $table->string('notiz')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('buisness_id')->references('id')->on('customers');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boerse_beobachtungen');
    }
};

