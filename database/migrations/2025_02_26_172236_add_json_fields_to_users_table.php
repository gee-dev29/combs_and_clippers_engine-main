<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJsonFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('rewards')->nullable()->after('email');
            $table->json('payment_preferences')->nullable()->after('rewards');
            $table->json('booking_preferences')->nullable()->after('payment_preferences');
            $table->json('availability')->nullable()->after('booking_preferences');
            $table->json('booking_limits')->nullable()->after('availability');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rewards', 'payment_preferences', 'booking_preferences', 'availability', 'booking_limits']);
        });
    }
}