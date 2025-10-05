<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('subscription_id');
            $table->string('ext_trans_id')->nullable();
            $table->string('internal_trans_id')->nullable();
            $table->string('status')->nullable();
            $table->tinyInteger('active')->default(0);
            $table->tinyInteger('auto_renew')->default(1);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->string('customer')->nullable();
            $table->string('session')->nullable();
            $table->string('invoice')->nullable();
            $table->string('subscription')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_subscriptions');
    }
}
