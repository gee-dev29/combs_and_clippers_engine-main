<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('merchant_id');
            $table->string('invoice_number', 50)->nullable();
            $table->dateTime('billing_date')->nullable();
            $table->integer('status')->default(0);
            $table->string('currency', 10)->default('GBP');
            $table->decimal('amount', 12)->nullable();
            $table->string('plan', 100)->nullable();
            $table->dateTime('next_billing_date')->nullable();
            $table->timestamps();
            $table->integer('user_subscription_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_histories');
    }
}
