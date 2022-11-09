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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name_en')->nullable();
            $table->string('name_so')->nullable();
            $table->string('image')->nullable();
            $table->string('email')->unique()->nullable();
            $table->foreignId('country_code_id')->nullable()->references('id')->on('country_codes');
            $table->string('mobile')->nullable()->unique();
            $table->string('password');
            $table->foreignId('role_id')->references('id')->on('roles');
            $table->integer('user_id')->nullable();
            $table->integer('status')->default(1)->comment('1:Active, 2:Blocked');
            $table->integer('reg_status')->default(0)->comment('0:Pending, 1:Accepted, 2:Rejected');
            $table->text('fcm')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
