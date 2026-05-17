<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fotostudio_bilder', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('titel')->nullable();
            $table->string('dateiname');       // Originalname
            $table->string('pfad');            // Speicherpfad auf public disk
            $table->boolean('sichtbar')->default(true);
            $table->timestamp('anzeige_von')->nullable(); // null = sofort aktiv
            $table->timestamp('anzeige_bis')->nullable(); // null = unbegrenzt
            $table->unsignedSmallInteger('reihenfolge')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fotostudio_bilder');
    }
};

