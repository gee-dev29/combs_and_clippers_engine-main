<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->integer('trans_id')->nullable();
            $table->integer('cust_id')->nullable();
            $table->string('cust_email')->nullable();
            $table->string('cust_auth_code')->nullable();
            $table->string('cust_code')->nullable();
            $table->string('trans_ref')->nullable();
            $table->integer('last_four_digit')->nullable();
            $table->integer('amount')->nullable();
            $table->string('channel')->nullable();
            $table->string('card_type')->nullable();
            $table->string('currency')->nullable();
            $table->string('trans_status')->nullable();
            $table->string('gateway_res')->nullable();
            $table->string('ip')->nullable();
            $table->string('paid_at_res')->nullable();
            $table->string('created_at_res')->nullable();
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
        Schema::dropIfExists('payment_transactions');
    }
}
