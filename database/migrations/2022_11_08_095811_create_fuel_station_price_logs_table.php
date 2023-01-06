<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFuelStationPriceLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fuel_station_price_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuel_station_id')->references('id')->on('fuel_stations');
            $table->foreignId('fuel_type_id')->references('id')->on('fuel_types');
            $table->decimal('price',12,2);
            $table->integer('added_by')->nullable()->comment('1:Admin, 2:Sub admin 5:Fuel station');
            $table->integer('added_user')->nullable()->references('id')->on('users');
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
        Schema::dropIfExists('fuel_station_price_logs');
    }
}
