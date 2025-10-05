<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('wallet_id');
            $table->decimal('amount', 25);
            $table->decimal('amount_requested', 25)->default(0);
            $table->decimal('fee', 25)->default(0);
            $table->string('narration')->nullable();
            $table->string('account_number');
            $table->string('account_name');
            $table->string('bank_name');
            $table->string('bank_code');
            $table->string('transferRef')->nullable();
            $table->integer('withdrawal_status')->default(0);
            $table->boolean('is_internal')->default(0);
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
        Schema::dropIfExists('withdrawals');
    }
}
