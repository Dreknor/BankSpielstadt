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
        Schema::create('working_times', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('buisness_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_manager')->nullable();
            $table->dateTime('start');
            $table->dateTime('end');
            $table->unsignedBigInteger('payment_customer');
            $table->unsignedBigInteger('payment_buisness');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('buisness_id')->references('id')->on('customers');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('payment_customer')->references('id')->on('payments');
            $table->foreign('payment_buisness')->references('id')->on('payments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('working_times');
    }
};
