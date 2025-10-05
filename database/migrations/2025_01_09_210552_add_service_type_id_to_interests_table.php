<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceTypeIdToInterestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('interests', function (Blueprint $table) {
            $table->unsignedBigInteger('service_type_id')->nullable()->after('image_link');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('interests', function (Blueprint $table) {
            $table->dropColumn('service_type_id');
        });
    }
}