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
        Schema::create('trucks', function (Blueprint $table) {
            $table->id();
            $table->string('truck_no');
            $table->foreignId('fuel_station_id')->references('id')->on('fuel_stations');
            $table->string('manufacturer');
            $table->date('manufactured_year')->nullable();
            $table->string('model');
            $table->string('color');
            $table->string('chassis_no');
            $table->string('engine_no');
            $table->string('mot_certificate_url')->nullable();
            $table->date('mot_certificate_expiry')->nullable();
            $table->string('insurance_certificate_url')->nullable();
            $table->date('insurance_certificate_expiry')->nullable();
            $table->string('truck_certificate_url')->nullable();
            $table->date('truck_certificate_expiry')->nullable();
            $table->integer('added_by')->nullable()->comment('1:Admin,2:Sub admin,4:Self');
            $table->integer('added_user')->references('user_id')->on('users')->nullable();
            $table->integer('updated_by')->nullable()->comment('1:Admin,2:Sub admin,4:Self');
            $table->foreignId('updated_user')->references('user_id')->on('users')->nullable();
            $table->integer('approval_by')->nullable()->comment('1:Admin,2:Sub admin,5:Fuel station');
            $table->foreignId('approval_user')->references('user_id')->on('users')->nullable();            $table->integer('reg_status')->comment('0:Pending ,1:Accepted, 2:Rejected');
            $table->integer('status')->default(1)->comment('1:Active, 2:Blocked');
            $table->integer('reg_status')->default(0)->comment('0:Pending,1:Accepted,2:Rejected');
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
        Schema::dropIfExists('trucks');
    }
};
