<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('merchant_id');
            $table->text('merchantAddress')->nullable();
            $table->string('merchantPhone')->nullable();
            $table->string('merchantEmail')->nullable();
            $table->string('merchantName')->nullable();
            $table->integer('customer_id')->nullable();
            $table->string('customerName')->nullable();
            $table->string('customerEmail')->nullable();
            $table->string('customerPhone')->nullable();
            $table->text('customerAddress')->nullable();
            $table->decimal('totalcost', 15)->nullable();
            $table->decimal('vat', 15)->nullable();
            $table->integer('status')->default(0);
            $table->integer('confirmed')->default(0);
            $table->integer('items_count')->default(0);
            $table->integer('deliveryPeriod')->nullable();
            $table->string('currency')->nullable();
            $table->timestamp('startDate')->nullable();
            $table->timestamp('endDate')->nullable();
            $table->timestamps();
            $table->string('invoiceRef')->nullable();
            $table->string('paymentRef')->nullable();
            $table->text('payurl')->nullable();
            $table->integer('payment_status')->default(0);
            $table->string('invoiceType')->nullable();
            $table->decimal('subTotal', 15)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
