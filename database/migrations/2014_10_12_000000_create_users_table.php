<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('fname')->nullable();
            $table->string('lname')->nullable();
            $table->string('email')->unique();
            $table->string('employee_id')->unique();
            $table->integer('mobile')->nullable();
            $table->integer('whatsapp_no')->unique();
            $table->integer('address')->unique();
            $table->integer('landmark')->unique();
            $table->integer('state')->unique();
            $table->integer('city')->unique();
            $table->integer('pin')->unique();
            $table->integer('aadhar_no')->unique();
            $table->integer('pan_no')->unique();
            $table->integer('type')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('otp')->nullable();
            $table->string('image')->nullable();
            $table->string('gender', 30)->nullable();
            $table->string('social_id')->nullable();
            $table->integer('is_verified')->comment('1: verified, 0: not verified')->default(0);
            $table->integer('status')->comment('1: active, 0: inactive')->default(1);
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
}
