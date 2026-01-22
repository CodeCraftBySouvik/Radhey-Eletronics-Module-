<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('cat_id');
            $table->bigInteger('sub_cat_id');
            $table->bigInteger('collection_id');
            $table->string('name');
            $table->string('image');
            $table->text('short_desc');
            $table->longText('desc');
            $table->double('price', 10, 2);
            $table->double('offer_price', 10, 2);
            $table->string('slug');
            $table->text('meta_title');
            $table->text('meta_desc');
            $table->text('meta_keyword');
            $table->string('style_no');
            $table->tinyInteger('is_trending')->comment('1: yes, 0:no')->default(0);
            $table->tinyInteger('is_best_seller')->comment('1: yes, 0:no')->default(0);
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
        Schema::dropIfExists('products');
    }
}
