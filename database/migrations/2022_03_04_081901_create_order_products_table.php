<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateOrderProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('order_id');

            $table->bigInteger('product_id');
            $table->string('product_name')->nullable();
            $table->string('product_image')->nullable();
            $table->string('product_slug')->nullable();
            $table->string('product_variation_id')->nullable();
            $table->double('price', 10, 2)->default(0.00);
            $table->double('offer_price', 10, 2)->default(0.00);
            $table->integer('qty')->default(1);
            $table->tinyInteger('status')->comment('1: new, 2: confirmed, 3: shipped, 4: delivered, 5: cancelled')->default(1);
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
        Schema::dropIfExists('order_products');
    }
}
