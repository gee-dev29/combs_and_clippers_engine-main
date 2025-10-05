<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToBoothRentals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booth_rentals', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('store_id');
            $table->decimal("amount")->default(0)->change();
            $table->string('payment_days')->nullable()->change();
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
            $table->dropColumn('user_id');
            $table->decimal("amount")->change();
            $table->string('payment_days')->nullable(False)->change();
        });
    }
}
