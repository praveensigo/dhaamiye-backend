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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('fuel_delivery_range', 12, 2);
            $table->decimal('commision', 12, 2);
            $table->decimal('tax', 12, 2);
            $table->decimal('min_fuel_level', 12, 2);
            $table->foreignId('country_code_id')->references('id')->on('country_codes');
            $table->string('mobile');
            $table->string('email');
            $table->string('android_version_driver');
            $table->string('android_version_customer');
            $table->string('ios_version_driver');
            $table->string('ios_version_customer');
            $table->integer('maintenance_customer')->default(0);
            $table->integer('maintenance_driver')->default(0);
            $table->text('maintenance_reason_customer_en')->nullable();
            $table->text('maintenance_reason_customer_so')->nullable();;
            $table->text('maintenance_reason_driver_en')->nullable();;
            $table->text('maintenance_reason_driver_so')->nullable();;
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
        Schema::dropIfExists('settings');
    }
};
