<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->json('rewards')->nullable();
            $table->json('payment_preferences')->nullable();
            $table->json('booking_preferences')->nullable();
            $table->json('availability')->nullable();
            $table->json('booking_limits')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'rewards',
                'payment_preferences',
                'booking_preferences',
                'availability',
                'booking_limits'
            ]);
        });
    }
}