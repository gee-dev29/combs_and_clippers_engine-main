<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stylist_id'); // The stylist issuing the voucher
            $table->unsignedBigInteger('user_id');   // The user receiving the voucher
            $table->string('code')->unique();        // Unique voucher code
            $table->decimal('discount', 8, 2);       // Discount amount
            $table->timestamp('expiry_date');        // Expiration date of the voucher
            $table->boolean('is_used')->default(false);
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
        Schema::dropIfExists('vouchers');
    }
}