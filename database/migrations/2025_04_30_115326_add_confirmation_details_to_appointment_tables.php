<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConfirmationDetailsToAppointmentTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
           
            if (!Schema::hasColumn('appointments', 'merchant_confirmed_at')) {
                $table->timestamp('merchant_confirmed_at')->nullable();
            }
            
            if (!Schema::hasColumn('appointments', 'client_confirmed_at')) {
                $table->timestamp('client_confirmed_at')->nullable();
            }
            
            if (!Schema::hasColumn('appointments', 'merchant_confirmed_by')) {
                $table->unsignedBigInteger('merchant_confirmed_by')->nullable();
            }
            
            if (!Schema::hasColumn('appointments', 'client_confirmed_by')) {
                $table->unsignedBigInteger('client_confirmed_by')->nullable();
            }
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
           
            if (Schema::hasColumn('appointments', 'merchant_confirmed_at')) {
                $table->dropColumn('merchant_confirmed_at');
            }
            
            if (Schema::hasColumn('appointments', 'client_confirmed_at')) {
                $table->dropColumn('client_confirmed_at');
            }
            
            if (Schema::hasColumn('appointments', 'merchant_confirmed_by')) {
                $table->dropColumn('merchant_confirmed_by');
            }
            
            if (Schema::hasColumn('appointments', 'client_confirmed_by')) {
                $table->dropColumn('client_confirmed_by');
            }
        });
    }
}