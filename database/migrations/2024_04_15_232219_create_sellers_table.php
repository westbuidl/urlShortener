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
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('sellerId');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email');
            $table->string('product');
            $table->string('product_category');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('verification_code')->nullable();
            $table->integer('is_verified')->default(value:0);
            $table->string('phone');
            $table->string('country');
            $table->string('state');
            $table->string('city');
            $table->string('zipcode')->nullable();
            $table->string('password');
            $table->string('profile_photo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
