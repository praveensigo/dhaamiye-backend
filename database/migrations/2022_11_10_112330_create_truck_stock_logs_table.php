<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTruckStockLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('truck_stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('truck_id')->references('id')->on('trucks');
            $table->foreignId('fuel_type_id')->references('id')->on('fuel_types');
            $table->decimal('stock',12,2);
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
        Schema::dropIfExists('truck_stock_logs');
    }
}
