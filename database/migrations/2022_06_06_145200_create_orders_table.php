<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->increments('id');
            $table->string('payurl', 1000)->nullable();
            $table->string('payment_gateway', 30)->nullable();
            $table->string('paymentRef', 255)->nullable();
            $table->string('externalRef', 255)->nullable();
            $table->string('orderRef', 30)->nullable();
            $table->double('totalprice', 10, 2);
            $table->double('shipping', 10, 2)->nullable();
            $table->integer('payment_status')->default(0);
            $table->integer('disbursement_status')->default(0);
            $table->dateTime('maxdeliverydate')->nullable();
            $table->dateTime('mindeliverydate')->nullable();
            $table->integer('merchant_id')->nullable();
            $table->integer('buyer_id')->nullable();
            $table->unsignedInteger('address_id')->nullable();
            $table->boolean('status')->nullable();
            $table->double('total', 10, 2);
            $table->string('currency', 20)->nullable();
            $table->integer('cart_id')->nullable();
            $table->string('delivery_type', 30)->default('Delivery');
            $table->string('cancellation_reason', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
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
