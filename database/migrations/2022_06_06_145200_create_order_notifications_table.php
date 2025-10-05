<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_transactions_id')->nullable();
            $table->string('order_ref')->nullable();
            $table->integer('buyer_id')->nullable();
            $table->integer('seller_id')->nullable();
            $table->string('headline')->nullable();
            $table->string('link')->nullable();
            $table->text('details')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('order_notifications');
    }
}
