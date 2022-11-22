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
        Schema::create('subadmin_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subadmin_id')->references('id')->on('sub_admins');
            $table->foreignId('module_id')->references('id')->on('modules');
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
        Schema::dropIfExists('subadmin_modules');
    }
};
