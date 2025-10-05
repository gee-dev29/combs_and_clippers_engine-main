<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appointment_services', function (Blueprint $table) {
            $table->decimal('original_price', 10, 2)->after('price'); 
            $table->decimal('discount_amount', 10, 2)->default(0)->after('original_price'); 
            $table->boolean('promo_applied')->default(false)->after('discount_amount'); 
            $table->unsignedBigInteger('promo_id')->nullable()->after('promo_applied'); 
            
       
            $table->foreign('promo_id')->references('id')->on('services_promos')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('appointment_services', function (Blueprint $table) {
            $table->dropForeign(['promo_id']);
            $table->dropColumn(['original_price', 'discount_amount', 'promo_applied', 'promo_id']);
        });
    }
};