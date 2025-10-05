<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreWorkdoneImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_workdone_images', function (Blueprint $table) {
            $table->id();
            //$table->foreignId('stores_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('stores_id');
            $table->string('image_url');
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
        Schema::dropIfExists('store_workdone_images');
    }
}