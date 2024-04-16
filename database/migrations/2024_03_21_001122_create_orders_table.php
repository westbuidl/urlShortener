<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('userID');
            $table->text('cart');
            $table->text('address');
            $table->string('name');
            $table->string('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     *//*
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }*/
};
