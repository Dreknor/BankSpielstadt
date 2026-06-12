<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lieferbestellungen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lieferdienst_id');
            $table->string('besteller_name', 100);
            $table->string('lieferort', 200);
            $table->enum('status', ['neu', 'in_bearbeitung', 'erledigt'])->default('neu');
            $table->unsignedBigInteger('mitarbeiter_id')->nullable();
            $table->unsignedInteger('gesamtbetrag')->default(0);
            $table->timestamps();

            $table->foreign('lieferdienst_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('mitarbeiter_id')->references('id')->on('customers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lieferbestellungen');
    }
};

