<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentDisbursementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_disbursements', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->nullable();
            $table->string('transferRef')->nullable();
            $table->string('traceID')->nullable();
            $table->string('orderPaymentRef')->nullable();
            $table->string('fromAcc')->nullable();
            $table->string('toAcc')->nullable();
            $table->string('toAcc_bankcode')->nullable();
            $table->decimal('amount', 17, 3)->nullable();
            $table->text('narration')->nullable();
            $table->string('responseCode')->nullable();
            $table->string('responseMessage')->nullable();
            $table->string('statusMessage')->nullable();
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
        Schema::dropIfExists('payment_disbursements');
    }
}
