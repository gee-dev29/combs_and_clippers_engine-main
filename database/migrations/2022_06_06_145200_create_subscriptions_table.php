<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->string('plan');
            $table->string('description')->nullable();
            $table->string('currency')->default('GBP');
            $table->double('price', 8, 2);
            $table->integer('invoice_period')->default(1);
            $table->string('invoice_interval')->default('month');
            $table->integer('trial_period')->default(0);
            $table->string('trial_interval')->default('day');
            $table->timestamps();
            $table->string('stripe_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
