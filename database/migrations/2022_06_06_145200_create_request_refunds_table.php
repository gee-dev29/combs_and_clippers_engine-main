<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_refunds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('posting_date')->nullable();
            $table->string('transcode', 50)->nullable();
            $table->string('customer_email', 50)->nullable();
            $table->string('merchant_email', 50)->nullable();
            $table->text('description')->nullable();
            $table->decimal('amount', 25)->nullable();
            $table->date('startdate')->nullable();
            $table->date('enddate')->nullable();
            $table->dateTime('date_request');
            $table->dateTime('date_refunded')->nullable();
            $table->text('reasons')->nullable();
            $table->string('request_status', 50)->default('');
            $table->string('requester', 20);
            $table->text('reject_reason');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_refunds');
    }
}
