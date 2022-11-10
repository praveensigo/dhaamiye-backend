<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTruckFuelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('truck_fuels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('truck_id')->references('id')->on('trucks');
            $table->foreignId('fuel_type_id')->references('id')->on('fuel_types');
            $table->decimal('capacity',12,2);
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
        Schema::dropIfExists('truck_fuels');
    }
}
