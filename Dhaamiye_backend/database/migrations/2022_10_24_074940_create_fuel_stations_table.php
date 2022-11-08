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
        Schema::create('fuel_stations', function (Blueprint $table) {
            $table->id();
            $table->string('place')->nullable();
            $table->string('latitude');
            $table->string('longitude');
            $table->string('address')->nullable();;
            $table->integer('added_by')->nullable()->comment('1:Admin, 2:Sub admin ,5:Self');
            $table->integer('added_user')->references('user_id')->on('users')->nullable();
            $table->integer('updated_by')->nullable()->comment('1:Admin, 2:Sub admin ,5:Self');
            $table->integer('updated_user')->references('user_id')->on('users')->nullable();
            $table->integer('status')->default(1)->comment('1:Active, 2:Blocked');
            $table->timestamp('deleted_at')->nullable();
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
        Schema::dropIfExists('fuel_stations');
    }
};
