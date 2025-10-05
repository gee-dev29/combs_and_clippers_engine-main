<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAmountProcessingFeeToBoothRentalPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booth_rental_payments', function (Blueprint $table) {
            $table->double('amount', 20, 2)->default(0);
            $table->double('processing_fee', 20, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booth_rental_payments', function (Blueprint $table) {
            $table->dropColumn('amount');
            $table->dropColumn('processing_fee');
        });
    }
}
