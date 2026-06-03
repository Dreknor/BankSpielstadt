<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fotostudio_bilder', function (Blueprint $table) {
            $table->unsignedSmallInteger('anzeige_dauer')->nullable()->default('5')->after('anzeige_bis')
                ->comment('Anzeigedauer in Sekunden (null = 5 Sekunden)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fotostudio_bilder', function (Blueprint $table) {
            $table->dropColumn('anzeige_dauer');
        });
    }
};
