<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('driver_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->references('id')->on('drivers');
            $table->integer('type')->default(1)->comment('1.Credit 2.Debit');
            $table->foreignId('order_id')->nullable()->references('id')->on('customer_orders');
            $table->decimal('amount');
            $table->integer('payment_type')->comment('1.Mobile 2.Cash');
            $table->string('payment_id')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('driver_payments');
    }
}
