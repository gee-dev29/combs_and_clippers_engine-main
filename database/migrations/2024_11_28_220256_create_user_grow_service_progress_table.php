<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserGrowServiceProgressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_grow_service_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('store_id');
            $table->enum('create_profile_link', [0, 1])->default(0);
            $table->enum('setup_referal_reward', [0, 1])->default(0);
            $table->enum('setup_loyalty_reward', [0, 1])->default(0);
            $table->enum('schedule_protection', [0, 1])->default(0);
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
        Schema::dropIfExists('user_grow_service_progress');
    }
}