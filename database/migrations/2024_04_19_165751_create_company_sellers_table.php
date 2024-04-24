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
        Schema::create('company_sellers', function (Blueprint $table) {
            $table->id();
            $table->string('companySellerId');
            $table->string('companyname');
            $table->string('companyregnumber');
            $table->string('companyemail');
            $table->string('product');
            $table->string('product_category');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('verification_code')->nullable();
            $table->integer('is_verified')->default(value:0);
            $table->string('companyphone');
            $table->string('companyaddress');
            $table->string('country');
            $table->string('state');
            $table->string('city');
            $table->string('zipcode')->nullable();
            $table->string('password');
            $table->string('profile_photo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_sellers');
    }
};
