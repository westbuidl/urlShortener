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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('buyerId');
            $table->string('productId');
            $table->string('orderId');
            $table->string('productName');
            $table->string('productImage');
            $table->string('amount');
            $table->string('quantity');
            $table->string('paymentMethod');
            $table->string('paymentReference');
            $table->string('Discount')->nullable();
            $table->string('shippingFee')->nullable();
            $table->string('order_status')->nullable();
            $table->string('currency')->nullable();
            $table->string('channel')->nullable();
            $table->integer('payment_id')->nullable();
            $table->string('country_code')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('grand_price')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('billing_address')->nullable();
            $table->timestamps();
          
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

