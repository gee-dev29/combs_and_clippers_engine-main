<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesPromosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services_promos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->decimal('discount_amount', 8, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('status')->default(false);
            $table->timestamps();

            //$table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services_promos');
    }
}