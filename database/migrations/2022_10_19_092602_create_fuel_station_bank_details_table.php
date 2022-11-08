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
        Schema::create('fuel_station_bank_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuel_station_id')->references('id')->on('fuel_stations');
            $table->string('bank_name');
            $table->string('branch');
            $table->string('account_no')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->integer('account_type')->comment('1:Savings account, 2:Current account');
            $table->string('upi_id')->nullable();
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
        Schema::dropIfExists('fuel_station_bank_details');
    }
};
