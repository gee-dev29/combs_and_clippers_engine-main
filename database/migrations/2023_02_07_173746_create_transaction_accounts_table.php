<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('appointment_id')->nullable();
            $table->string('account_number');
            $table->string('account_name');
            $table->string('bank_code');
            $table->string('initiationTranRef')->nullable();
            $table->integer('status')->default(0);
            $table->string('account_id')->nullable();
            $table->string('provider')->nullable();
            $table->timestamp('expiresAt')->nullable();
            $table->float('amount_paid')->nullable();
            $table->integer('order_id')->nullable();
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
        Schema::dropIfExists('transaction_accounts');
    }
}
