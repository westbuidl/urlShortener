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
        Schema::create('product_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('productId');
            $table->string('SellerId');
            $table->string('product_name');
            $table->string('CategoryID');
            $table->string('product_catogory');
            $table->string('buyerId');
            $table->string('buyer_fullname');
            $table->string('rating');
            $table->string('feedback');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_product_feedback');
    }
};
