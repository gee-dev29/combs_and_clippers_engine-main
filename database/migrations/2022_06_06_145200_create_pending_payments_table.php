<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendingPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pending_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('initiated_by')->nullable();
            $table->integer('payment_status')->default(0)->comment('0 : pending, 1 : successful');
            $table->decimal('amount', 15)->nullable();
            $table->string('currency')->nullable();
            $table->string('payment_gateway')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
            $table->integer('payment_settlement_status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pending_payments');
    }
}
