<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToUserStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_stores', function (Blueprint $table) {
            $table->integer("available_status")->nullable()->default(0);
            $table->integer('service_type_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_stores', function (Blueprint $table) {
            $table->dropColumn('available_status');
            $table->dropColumn('service_type_id');
        });
    }
}