<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('services', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->integer('merchant_id');
			$table->string('name', 255);
			$table->text('description');
			$table->string('slug', 255);
			$table->string('price_type');
			$table->decimal('price', 20);
			$table->string('currency', 5)->default('GBP');
			$table->string('image_url', 255);
			$table->integer('status')->default(1);
			$table->string('duration');
			$table->string('buffer')->nullable();
			$table->string('payment_preference');
			$table->integer('deposit')->nullable();
			$table->string('location');
			$table->decimal('home_service_charge', 20)->nullable();
			$table->boolean('allow_cancellation')->default(0);
			$table->string('allowed_cancellation_period')->nullable();
			$table->boolean('allow_rescheduling')->default(0);
			$table->string('allowed_rescheduling_period')->nullable();
			$table->boolean('booking_reminder')->default(0);
			$table->string('booking_reminder_period')->nullable();
			$table->boolean('limit_early_booking')->default(0);
			$table->string('early_booking_limit_period')->nullable();
			$table->boolean('limit_late_booking')->default(0);
			$table->string('late_booking_limit_period')->nullable();
			$table->string('checkout_label')->default('Book Now');
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
		Schema::drop('services');
	}

}
