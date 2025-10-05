<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStoreIdNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_booth_progress', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->change();
        });

        Schema::table('user_grow_service_progress', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_booth_progress', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable(false)->change();
        });

        Schema::table('user_grow_service_progress', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable(false)->change();
        });
    }
}