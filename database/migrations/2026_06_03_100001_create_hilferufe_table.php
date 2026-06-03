<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hilferufe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade'); // anfragender Betrieb
            $table->text('nachricht')->nullable();
            $table->string('status')->default('offen'); // offen | in_bearbeitung | erledigt
            $table->string('bearbeiter')->nullable();
            $table->text('notiz')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hilferufe');
    }
};

