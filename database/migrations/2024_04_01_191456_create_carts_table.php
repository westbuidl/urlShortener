<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('productId')->nullable();
            $table->string('buyerId')->nullable();
            $table->string('product_name')->nullable();
            $table->string('product_image')->nullable();
            $table->string('product_category')->nullable();
            $table->string('selling_price')->nullable();
            $table->string('total_price')->nullable();
            $table->string('quantity')->nullable();
            $table->string('categoryID')->nullable();
            $table->string('cartId')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
