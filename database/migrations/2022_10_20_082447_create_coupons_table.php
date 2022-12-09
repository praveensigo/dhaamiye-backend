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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('coupon_name');
            $table->string('coupon_code')->unique();
            $table->decimal('amount', 12, 2);
            $table->integer('type')->default(1)->comment('1:Amount, 2:Percentage 3:Amount for additional fuel');
            $table->decimal('min_order_amount', 12, 2);
            $table->date('expiry_date');
            $table->integer('count');
            $table->integer('used_count')->default(0);
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
        Schema::dropIfExists('coupons');
    }
};
