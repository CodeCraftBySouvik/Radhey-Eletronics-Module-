<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_collections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('store_id')->nullable();
            $table->bigInteger('order_id')->nullable();
            $table->integer('collection_amount')->unsigned()->nullable()->default(12);
            $table->string('bank_name', 255)->nullable();
            $table->string('cheque_number', 255)->nullable();
            $table->string('cheque_date', 255)->nullable();
            $table->tinyInteger('payement_type')->comment('0: cheque, 1: cash');
            $table->string('image', 255)->nullable();
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
        Schema::dropIfExists('payment_collections');
    }
}
