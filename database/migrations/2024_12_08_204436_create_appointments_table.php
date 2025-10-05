<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('store_id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('merchant_id');
            $table->date('date');
            $table->time('time');
            $table->string('phone_number');
            $table->string('payment_details')->nullable();
            $table->decimal('tip', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('Pending');
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
        Schema::dropIfExists('appointments');
    }
}