<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *//*//
   public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('userID');
            $table->string('productID');
            $table->string('orderID');
            $table->string('productName');
           
            $table->string('productDescription');
            $table->string('amount');
            $table->string('quantity');
            $table->string('paymentMethod');
            $table->string('Discount');
            $table->string('shippingFee');
            $table->string('status')->nullable();
          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       // Schema::dropIfExists('orders');
    }
};

