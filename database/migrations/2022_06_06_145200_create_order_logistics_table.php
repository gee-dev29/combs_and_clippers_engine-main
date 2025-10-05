<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderLogisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_logistics', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cart_id');
            $table->integer('order_id')->nullable();
            $table->integer('pickup_address_id')->nullable();
            $table->integer('delivery_address_id')->nullable();
            $table->string('pickup_order_id')->nullable();
            $table->string('fulfilment_request_id')->nullable();
            $table->string('channel_grouping_id')->nullable();
            $table->string('channel_reference_id')->nullable();
            $table->string('redis_key')->nullable();
            $table->string('rate_id')->nullable();
            $table->string('get_rates_key')->nullable();
            $table->string('kwik_key')->nullable();
            $table->string('delivery_note')->nullable();
            $table->string('type')->nullable();
            $table->string('estimated_days')->nullable();
            $table->string('delivery_status')->nullable();
            $table->string('currency')->nullable();
            $table->decimal('amount', 15)->nullable();
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
        Schema::dropIfExists('order_logistics');
    }
}
