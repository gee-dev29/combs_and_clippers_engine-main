<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('delivery_time')->nullable();
            $table->string('currency', 3)->default('GBP');
            $table->decimal('delivery_fee', 10)->default(0);
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
        Schema::dropIfExists('delivery_settings');
    }
};
