<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBoothProgressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_booth_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('store_id');
            $table->enum('add_schedule_location', [0, 1])->default(0);
            $table->enum('setup_my_service', [0, 1])->default(0);
            $table->enum('setup_portfolio', [0, 1])->default(0);
            $table->enum('create_bio', [0, 1])->default(0);
            $table->enum('accept_payment', [0, 1])->default(0);

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
        Schema::dropIfExists('user_booth_progress');
    }
}