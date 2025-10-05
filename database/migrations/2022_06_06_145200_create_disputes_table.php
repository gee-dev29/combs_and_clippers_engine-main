<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisputesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->integer('merchant_id')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('merchant_email')->nullable();
            $table->string('dispute_referenceid')->nullable();
            // $table->string('transcode')->nullable();
            $table->string('dispute_category')->nullable();
            $table->string('dispute_option')->nullable();
            $table->text('dispute_description')->nullable();
            $table->integer('dispute_status')->default(0);
            // $table->string('arbitrator_name')->nullable();
            // $table->text('arbitrator_profile')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('resolution_date')->nullable();
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
        Schema::dropIfExists('disputes');
    }
}
