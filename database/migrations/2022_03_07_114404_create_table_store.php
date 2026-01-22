<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableStore extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->string('store_name');
            $table->string('bussiness_name');
            $table->string('store_OCC_number');
            $table->integer('contact');
            $table->integer('whatsapp');
            $table->string('email');
            $table->string('address');
            $table->string('area');
            $table->string('state');
            $table->string('city');
            $table->string('pin');
            $table->string('no_order_reason');
            $table->tinyInteger('status')->comment('1: active, 0: inactive')->default(1);
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
        Schema::dropIfExists('table_store');
    }
}
