<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   /* public function up()
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->string('wishlistId')->unique();
            $table->string('buyerId');
            $table->string('productId');
            $table->string('product_image')->nullable();
            $table->string('product_name');
            $table->string('product_category');
            $table->decimal('selling_price', 8, 2);
            $table->string('categoryID');
            $table->timestamps();
            
            //$table->foreign('buyerId')->references('id')->on('buyers')->onDelete('cascade');
            //$table->foreign('productId')->references('id')->on('products')->onDelete('cascade');
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
