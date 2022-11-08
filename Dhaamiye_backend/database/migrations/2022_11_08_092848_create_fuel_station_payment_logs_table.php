<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFuelStationPaymentLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fuel_station_payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuel_station_id')->constrained();
            $table->foreignId('order_id')->references('id')->on('customer_orders');
            $table->decimal('amount');
            $table->integer('type')->comment('1:Credit, 2:Debit');
            $table->decimal('balance');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fuel_station_payment_logs');
    }
}
