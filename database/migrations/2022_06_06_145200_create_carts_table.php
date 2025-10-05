<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->increments('id');
            $table->unsignedInteger('buyer_id');
            $table->unsignedInteger('merchant_id')->nullable();
            $table->double('totalprice', 15, 2);
            $table->integer('status')->default(0);
            $table->integer('items_count');
            $table->string('currency');
            $table->timestamps();
            $table->integer('max_delivery_period')->nullable();
            $table->integer('min_delivery_period')->nullable();
            $table->dateTime('max_delivery_date')->nullable();
            $table->dateTime('min_delivery_date')->nullable();
            $table->double('shipping', 15, 2)->nullable();
            $table->double('total_sum', 15, 2)->nullable();
            $table->string('delivery_type', 30)->default('Delivery');
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
