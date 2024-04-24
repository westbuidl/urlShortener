<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *//*
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('productId');
            $table->string('sellerId');
            $table->string('product_name');
            $table->string('categoryID');
            $table->string('product_category');
            $table->string('cost_price');
            $table->string('selling_price');
            $table->string('quantityin_stock');
            $table->string('unit');
            $table->string('product_description');
            $table->string('product_image')->nullable();
            $table->integer('is_active');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       // Schema::dropIfExists('products');
    }
};
