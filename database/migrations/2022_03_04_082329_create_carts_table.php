<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ip');

            $table->bigInteger('product_id');
            $table->string('product_name')->nullable();
            $table->string('product_image')->nullable();
            $table->string('product_slug')->nullable();
            $table->string('product_variation_id')->nullable();
            $table->double('price', 10, 2)->default(0.00);
            $table->double('offer_price', 10, 2)->default(0.00);
            $table->integer('qty')->default(1);

            $table->tinyInteger('status')->comment('1: active, 0: inactive')->default(1);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carts');
    }
}
