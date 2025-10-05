<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVfdWebhooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vfd_webhooks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reference')->nullable();
            $table->float('amount')->nullable();
            $table->string('account_no')->nullable();
            $table->string('from_account_no')->nullable();
            $table->string('from_account_name')->nullable();
            $table->string('from_bankcode')->nullable();
            $table->string('narration')->nullable();
            $table->string('session_id')->nullable();
            $table->string('trans_date')->nullable();
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
        Schema::dropIfExists('vfd_webhooks');
    }
}
