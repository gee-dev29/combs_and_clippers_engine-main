<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInviteCodeToBoothRentalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booth_rentals', function (Blueprint $table) {
            $table->string('invite_code')->nullable()->after('store_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booth_rentals', function (Blueprint $table) {
            $table->dropColumn('invite_code');
        });
    }
}