<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisputeResolutionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispute_resolutions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dispute_id');
            $table->string('transcode')->nullable();
            $table->text('merchant_comment')->nullable();
            $table->text('customer_comment')->nullable();
            $table->text('arbitrator_comment')->nullable();
            $table->text('resolution_desc')->nullable();
            $table->timestamp('sitting_date')->nullable();
            $table->timestamp('next_sitting_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dispute_resolutions');
    }
}
