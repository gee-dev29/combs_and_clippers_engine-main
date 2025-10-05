<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('invoiceID')->nullable();
            $table->integer('productID')->nullable();
            $table->string('productname')->nullable();
            $table->text('image_url')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('currency')->nullable();
            $table->decimal('unitPrice', 15)->nullable();
            $table->decimal('totalCost', 15)->nullable();
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
        Schema::dropIfExists('invoice_items');
    }
}
