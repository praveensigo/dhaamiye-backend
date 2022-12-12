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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title_en')->nullable();
            $table->string('title_so')->nullable();
            $table->mediumText('description_en')->nullable();
            $table->mediumText('description_so')->nullable();
            $table->integer('type')->default(1)->comment('1:All Users,2:Sub admins 3:Customers, 4:Drivers, 5:Fuel stations');
            $table->foreignId('user_id')->references('id')->on('users')->nullable();
            $table->foreignId('order_id')->nullable()->references('id')->on('customer_orders');
            $table->integer('added_by')->nullable()->comment('1:Admin, 2:Sub admin ,5:Fuel station');
            $table->foreignId('added_user')->references('id')->on('users')->nullable();
            $table->integer('status')->default(1)->comment('1:Active, 2:Blocked');
            $table->date('date')->nullable();
            $table->time('time')->nullable();
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
        Schema::dropIfExists('notifications');
    }
};
