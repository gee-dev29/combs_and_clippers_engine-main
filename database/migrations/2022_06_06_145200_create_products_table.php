<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('store_id')->nullable();
            $table->integer('merchant_id')->nullable();
            $table->string('merchant_email', 100)->nullable();
            $table->string('productname', 100)->nullable();
            $table->string('description')->nullable();
            $table->string('product_slug', 50)->nullable();
            $table->decimal('price', 20)->nullable();
            $table->string('currency', 5)->nullable();
            $table->integer('deliveryperiod')->nullable();
            $table->string('link', 50)->nullable();
            $table->mediumText('html_link')->nullable();
            $table->text('image_url')->nullable();
            $table->double('height', 8, 2)->nullable();
            $table->double('weight', 8, 2)->nullable();
            $table->double('width', 8, 2)->nullable();
            $table->double('length', 8, 2)->nullable();
            $table->integer('quantity')->default(1);
            $table->text('other_images_url')->nullable();
            $table->string('video_link')->nullable();
            $table->string('SKU')->nullable();
            $table->string('barcode')->nullable();
            $table->string('product_type')->default('Physical');
            $table->unsignedInteger('category_id')->nullable();
            $table->unsignedInteger('box_size_id')->nullable();
            $table->text('attributes')->nullable();
            $table->string('product_code')->nullable();
            $table->boolean('active')->default(1);
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
        Schema::dropIfExists('products');
    }
}
