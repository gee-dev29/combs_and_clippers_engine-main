<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        if (!Schema::hasTable('sub_roles')) {
            Schema::create('sub_roles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('role_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('display_name');
                $table->string('description')->nullable();
                $table->timestamps();

                $table->unique(['role_id', 'name']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       
        if (Schema::hasTable('sub_roles')) {
            Schema::dropIfExists('sub_roles');
        }
    }
}