<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('type', ['credit', 'debit'])->default('credit');
            $table->dateTime('posting_date')->nullable();
            $table->string('transcode', 50)->nullable();
            $table->string('tempcode', 50)->nullable();
            $table->string('customer_email', 50)->nullable();
            $table->string('merchant_email', 50)->nullable();
            $table->string('merchant_code')->nullable();
            $table->text('description')->nullable();
            $table->decimal('amount', 25)->nullable();
            $table->string('country', 5)->nullable();
            $table->string('currency', 10);
            $table->dateTime('startdate')->nullable();
            $table->dateTime('enddate')->nullable();
            $table->string('fulfill_days', 50)->nullable();
            $table->string('payment_gateway', 30)->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('trans_status', 50)->nullable();
            $table->dateTime('refunddate')->nullable();
            $table->dateTime('releasedate')->nullable();
            $table->dateTime('stoprefunddate')->nullable();
            $table->boolean('refunded')->nullable()->default(false);
            $table->boolean('extended')->nullable()->default(false);
            $table->boolean('requestextend')->nullable()->default(false);
            $table->boolean('requestrefund')->nullable()->default(false);
            $table->tinyInteger('confirmed_by_merchant')->nullable()->default(0);
            $table->dateTime('confirmed_date')->nullable();
            $table->dateTime('cancelled_date')->nullable();
            $table->dateTime('insert_date')->nullable();
            $table->tinyInteger('amountpaid')->nullable()->default(0);
            $table->dateTime('fufill_notice_date')->nullable();
            $table->decimal('paystack_fee', 15)->nullable();
            $table->decimal('RAVE_fee', 15)->nullable();
            $table->boolean('request_extend')->nullable()->default(false);
            $table->dateTime('stop_payment_date')->nullable();
            $table->text('reason_for_stopping')->nullable();
            $table->dateTime('refund_date')->nullable();
            $table->text('reason_for_stop_refund')->nullable();
            $table->dateTime('stop_refund_date')->nullable();
            $table->dateTime('arbitration_request_date')->nullable();
            $table->integer('order_id')->nullable();
            $table->boolean('request_refund')->nullable()->default(false);
            $table->integer('appid')->nullable();
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
        Schema::dropIfExists('transactions');
    }
}
