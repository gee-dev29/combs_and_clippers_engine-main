<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('merchant_id');
            $table->string('invoice_number', 50)->nullable();
            $table->dateTime('billing_date')->nullable();
            $table->integer('status')->default(0);
            $table->string('currency', 10)->default('GBP');
            $table->decimal('amount', 12)->nullable();
            $table->string('plan', 100)->nullable();
            $table->dateTime('next_billing_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_invoices');
    }
}
