<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInternalTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('internal_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('merchant_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->integer('order_id')->nullable();
            $table->enum('type', ['credit', 'debit'])->default('credit');
            $table->string('transaction_ref')->nullable();
            $table->text('narration')->nullable();
            $table->string('currency', 10);
            $table->decimal('amount', 25)->nullable();
            $table->string('payment_status')->nullable();
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
        Schema::dropIfExists('internal_transactions');
    }
}
