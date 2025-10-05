<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFavoriteStylistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('favorite_stylists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');     // The user marking the stylist as favorite
            $table->unsignedBigInteger('stylist_id'); // The stylist being marked as favorite
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
        Schema::dropIfExists('favorite_stylists');
    }
}