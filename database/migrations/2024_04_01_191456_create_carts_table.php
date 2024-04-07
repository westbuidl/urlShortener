<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  /* public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('product_id')->nullable();
            $table->string('user_id')->nullable();
            $table->string('product_name')->nullable();
            $table->string('product_category')->nullable();
            $table->string('selling_price')->nullable();
            $table->string('quantity')->nullable();
            $table->string('categoryID')->nullable();
            $table->string('cart_id')->nullable();
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
