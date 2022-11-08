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
        Schema::create('customer_order_address', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->references('id')->on('customers');
            $table->foreignId('order_id')->references('id')->on('customer_orders');
            $table->foreignId('country_code_id')->nullable()->references('id')->on('country_codes');
            $table->string('phone')->nullable();
            $table->string('location')->nullable();
            $table->text('address')->nullable();
            $table->string('latitude');
            $table->string('longitude');
            $table->text('special_instructions')->nullable();
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
        Schema::dropIfExists('customer_order_address');
    }
};
