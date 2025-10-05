<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('model', 100)->nullable();
            $table->integer('model_uid')->nullable();
            $table->integer('merchant_id')->nullable();
            $table->integer('buyer_id')->nullable();
            $table->string('description')->nullable();
            $table->string('controller', 100)->nullable();
            $table->string('action', 100)->nullable();
            $table->string('params')->nullable();
            $table->json('before_action')->nullable();
            $table->json('after_action')->nullable();
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
        Schema::dropIfExists('activities');
    }
}
