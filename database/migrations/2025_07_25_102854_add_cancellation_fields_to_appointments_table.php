<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancellationFieldsToAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->text('cancel_reason')->nullable()->after('status');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancel_reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            
         
            // $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
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
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['cancel_reason', 'cancelled_by', 'cancelled_at']);
        });
    }
}