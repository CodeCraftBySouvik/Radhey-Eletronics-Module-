<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ip');

            $table->bigInteger('user_id');
            $table->bigInteger('store_id');
            $table->string('fname', 100)->nullable();
            $table->string('lname', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('alt_mobile', 20)->nullable();

            $table->bigInteger('billing_address_id')->default(0);
            $table->string('billing_address')->nullable();
            $table->string('billing_landmark')->nullable();
            $table->string('billing_country')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_pin')->nullable();

            $table->bigInteger('shipping_address_id')->default(0);
            $table->string('shipping_address')->nullable();
            $table->string('shipping_landmark')->nullable();
            $table->string('shipping_country')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_pin')->nullable();

            $table->double('amount', 10, 2)->default(0.00);
            $table->double('tax_amount', 10, 2)->default(0.00);
            $table->double('discount_amount', 10, 2)->default(0.00);
            $table->bigInteger('coupon_code_id')->default(0);
            $table->double('final_amount', 10, 2)->default(0.00);

            $table->string('gst_no', 50)->nullable();
            $table->tinyInteger('is_paid')->comment('1: paid, 0: not paid')->default(0);
            $table->bigInteger('txn_id')->default(0);

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
        Schema::dropIfExists('orders');
    }
}
