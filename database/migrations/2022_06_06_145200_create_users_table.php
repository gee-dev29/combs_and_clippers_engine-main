<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('merchant_code')->nullable();
            $table->integer('wallet_id')->nullable();
            $table->string('name');
            $table->string('firstName', 30)->nullable();
            $table->string('lastName', 30)->nullable();
            $table->string('phone')->nullable();
            $table->string('email', 64)->unique();
            $table->string('password')->nullable();
            $table->boolean('accountstatus')->default(true);
            $table->text('referral_code')->nullable();
            $table->string('profile_image_link')->nullable();
            $table->rememberToken();
            $table->integer('email_verified')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('account_type')->nullable()->default('Client');
            $table->string('specialization')->nullable();
            $table->string('sms_otp')->nullable();
            $table->string('token')->nullable();
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
        Schema::dropIfExists('users');
    }
}
