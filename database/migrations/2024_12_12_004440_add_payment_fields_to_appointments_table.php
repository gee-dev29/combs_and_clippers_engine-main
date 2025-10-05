<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentFieldsToAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->integer('address_id')->nullable();
            $table->string('payment_gateway')->nullable();
            $table->string('payment_url', 1000)->nullable();
            $table->string('payment_ref')->nullable();
            $table->string('appointment_ref')->nullable();
            $table->string('currency', 5)->default('NGN')->after('payment_details');
            $table->integer('payment_status')->default(0);
            $table->integer('disbursement_status')->default(0);
            $table->string('reason_for_cancelation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['address_id', 'payment_gateway', 'payment_url', 'payment_ref', 'appointment_ref', 'currency', 'payment_status', 'disbursement_status', 'reason_for_cancelation']);
        });
    }
}
