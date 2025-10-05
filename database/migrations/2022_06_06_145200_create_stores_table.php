<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('merchant_id')->nullable();
            $table->string('store_name')->nullable();
            $table->integer('store_category')->nullable();
            $table->string('website')->nullable();
            $table->string('store_icon')->nullable();
            $table->string('store_banner')->nullable();
            $table->text('store_description')->nullable();
            $table->integer('featured')->default(0);
            $table->integer('approved')->default(0);
            $table->json('days_available')->nullable();
            $table->json('time_available')->nullable();
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
        Schema::dropIfExists('stores');
    }
}
