<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('merchant_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('street');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country');
            $table->string('zip')->nullable();
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->text('address')->nullable();
            $table->text('formatted_address')->nullable();
            $table->integer('address_code')->nullable();
            $table->string('city_code')->nullable();
            $table->string('state_code')->nullable();
            $table->string('country_code')->nullable();
            $table->string('postal_code')->nullable();
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
        Schema::dropIfExists('store_addresses');
    }
}
