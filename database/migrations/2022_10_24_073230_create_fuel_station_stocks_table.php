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
        Schema::create('fuel_station_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuel_station_id')->references('id')->on('fuel_stations');
            $table->foreignId('fuel_type_id')->references('id')->on('fuel_types');
            $table->decimal('stock',12,2)->default(0);
            $table->decimal('price',12,2);
            $table->integer('added_by')->nullable()->comment('1:Admin, 2:Sub admin ,5:Fuel station');
            $table->foreignId('added_user')->references('id')->on('users')->nullable();
            $table->integer('status')->default(1)->comment('1:Active, 2:Blocked');
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
        Schema::dropIfExists('fuel_station_stocks');
    }
};
