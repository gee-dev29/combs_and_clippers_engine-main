<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoothRentalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booth_rentals', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('store_id');
            $table->decimal("amount");
            $table->enum('payment_timeline', ['weekly', 'every two weeks', 'twice a month', 'monthly']);
            $table->string('payment_days');
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
        Schema::dropIfExists('booth_rentals');
    }
}