<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('uri');
            $table->string('method', 6);
            $table->text('params')->nullable();
            $table->string('api_key', 40);
            $table->string('ip_address', 45);
            $table->integer('time');
            $table->float('rtime', 10, 0)->nullable();
            $table->string('authorized', 1);
            $table->smallInteger('response_code')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
}
