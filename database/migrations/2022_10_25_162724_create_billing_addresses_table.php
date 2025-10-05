<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('recipient');
            $table->string('street');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('zip')->nullable();
            $table->string('phone')->nullable();
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->text('address')->nullable();
            $table->string('email')->nullable();
            $table->string('country')->nullable();
            $table->text('formatted_address')->nullable();
            $table->integer('address_code')->nullable();
            $table->string('city_code')->nullable();
            $table->string('state_code')->nullable();
            $table->string('country_code')->nullable();
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
        Schema::dropIfExists('billing_addresses');
    }
}
