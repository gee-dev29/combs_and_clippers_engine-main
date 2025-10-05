<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageBoxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_boxes', function (Blueprint $table) {
            $table->id();
            $table->string('box_size_id');
            $table->string('name');
            $table->text('description_image_url');
            $table->float('height');
            $table->float('width');
            $table->float('length');
            $table->float('max_weight');
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
        Schema::dropIfExists('package_boxes');
    }
}
