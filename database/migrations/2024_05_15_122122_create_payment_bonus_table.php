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
        Schema::create('payment_bonus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buissnes_id');
            $table->time('start');
            $table->time('end');
            $table->integer('bonus');
            $table->string('bonus_type');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('buissnes_id')->references('id')->on('customers');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_bonus');
    }
};
