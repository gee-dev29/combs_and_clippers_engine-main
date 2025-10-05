<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ModifyWalletTransactionsEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN type ENUM('credit', 'debit', 'credit_unclaimed')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN type ENUM('credit', 'debit')");
    }
}
