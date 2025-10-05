<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalletTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('wallet_id');
            $table->integer('withdrawal_id')->nullable();
            $table->enum('type', ['credit', 'debit'])->default('credit');
            $table->string('transaction_ref')->nullable();
            $table->text('narration')->nullable();
            $table->string('currency', 10)->default('NGN');
            $table->decimal('amount', 25);
            $table->string('status')->default('pending');
            $table->string('from_account_no')->nullable();
            $table->string('from_account_name')->nullable();
            $table->string('from_bank_name')->nullable();
            $table->string('from_bank_code')->nullable();
            $table->string('to_account_no')->nullable();
            $table->string('to_account_name')->nullable();
            $table->string('to_bank_name')->nullable();
            $table->string('to_bank_code')->nullable();
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
        Schema::dropIfExists('wallet_transactions');
    }
}
