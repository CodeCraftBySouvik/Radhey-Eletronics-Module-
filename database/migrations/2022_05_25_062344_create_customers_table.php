<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->bigInteger('mobile')->nullable();
            $table->bigInteger('whatsapp_no')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_landmark')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_city')->nullable();
            $table->integer('billing_pin')->nullable();
            $table->string('billing_country')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('shipping_landmark')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_city')->nullable();
            $table->integer('shipping_pin')->nullable();
            $table->string('shipping_country')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('gst_file')->nullable();
            $table->integer('credit_limit')->nullable();
            $table->integer('credit_days')->nullable();
            $table->timestamp('email_verified_at')->nullable();
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
        Schema::dropIfExists('customers');
    }
}
