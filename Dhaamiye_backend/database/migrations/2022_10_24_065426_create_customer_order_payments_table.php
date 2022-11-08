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
        Schema::create('customer_order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->references('id')->on('customer_orders');
            $table->foreignId('customer_id')->references('id')->on('customers');
            $table->foreignId('driver_id')->references('id')->on('drivers');
            $table->integer('payment_type')->comment('1:Mobile, 2:Cash')->nullable();
            $table->string('payment_id')->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->integer('status')->default(1)->comment('1:Pending, 2:Completed');
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
        Schema::dropIfExists('customer_order_payments');
    }
};
