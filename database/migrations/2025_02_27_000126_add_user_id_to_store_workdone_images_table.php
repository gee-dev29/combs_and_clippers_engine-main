<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToStoreWorkdoneImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('store_workdone_images', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id'); // Add user_id
            $table->unsignedBigInteger('stores_id')->nullable()->change(); // Make stores_id nullable
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('store_workdone_images', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->unsignedBigInteger('stores_id')->nullable(false)->change(); // Revert stores_id to NOT NULL
        });
    }
}