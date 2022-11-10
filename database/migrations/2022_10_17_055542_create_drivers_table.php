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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('passport_url')->nullable();
            $table->string('license_url')->nullable();
            $table->date('license_expiry')->nullable();
            $table->integer('added_by')->default(1)->nullable()->comment('1:Admin,2:Sub admin,4:Self');
            $table->foreignId('added_user')->nullable()->references('id')->on('users');
            $table->integer('updated_by')->nullable()->comment('1:Admin,2:Sub admin,4:Self');
            $table->foreignId('updated_user')->nullable()->references('id')->on('users');
            $table->integer('approval_by')->nullable()->comment('1:Admin,2:Sub admin,5:Fuel station');
            $table->integer('approval_user')->references('id')->on('users')->nullable();
            $table->foreignId('fuel_station_id')->nullable()->references('id')->on('fuel_stations');
            $table->foreignId('truck_id')->nullable()->references('id')->on('trucks');
            $table->integer('status')->default(1)->comment('1:Active, 2:Blocked');
            $table->integer('reg_status')->default(1)->comment('0:Pending, 1:Approved, 2:Rejected');
            $table->integer('online')->default(2)->comment('1:Online ,2:Offline');
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
        Schema::dropIfExists('drivers');
    }
};
