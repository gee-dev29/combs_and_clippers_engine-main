<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('transcode', 50)->nullable();
            $table->string('customer_email', 50)->nullable();
            $table->string('merchant_email', 50)->nullable();
            $table->string('trans_status')->nullable();
            $table->dateTime('status_update_date')->nullable();
            $table->string('updatedby')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions_history');
    }
}
