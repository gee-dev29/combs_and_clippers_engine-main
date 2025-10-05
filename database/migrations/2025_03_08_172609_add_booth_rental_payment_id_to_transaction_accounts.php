<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBoothRentalPaymentIdToTransactionAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_accounts', function (Blueprint $table) {
            $table->integer('booth_rental_payment_id')->nullable();
            $table->double('amount', 20, 2)->default(0);
            $table->double('processing_fee', 20, 2)->default(0);
            $table->double('total', 20, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_accounts', function (Blueprint $table) {
            $table->dropColumn('booth_rental_payment_id');
            $table->dropColumn('amount');
            $table->dropColumn('processing_fee');
            $table->dropColumn('total');
        });
    }
}
