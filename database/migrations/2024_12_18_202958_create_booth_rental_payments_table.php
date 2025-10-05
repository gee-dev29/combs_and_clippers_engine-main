<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoothRentalPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booth_rental_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_store_id');
            $table->unsignedInteger('booth_rental_id');
            $table->date('last_payment_date')->nullable();
            $table->date('next_payment_date');
            $table->enum('payment_status', ['paid', 'due', 'overdue', 'upcoming']);
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
        Schema::dropIfExists('booth_rental_payments');
    }
}