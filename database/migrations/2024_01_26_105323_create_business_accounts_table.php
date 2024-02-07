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
        Schema::create('business_accounts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('businessid');
            $table->string('businessname');
            $table->string('businessregnumber');
            $table->string('businessemail');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('verification_code')->nullable();
            $table->integer('is_verified')->default(value:0);
            $table->string('businessphone');
            $table->string('products');
            $table->string('businessaddress');
            $table->string('country');
            $table->string('city');
            $table->string('state');
            $table->string('zipcode');
            $table->string('password');
            $table->string('profile_photo')->nullable();

           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_accounts');
    }
};
/*'businessID',
        'businessname',
        'businessregnumber',
        'businessemail',
        'businessphonenumber',
        'products',
        'businessaddress',
        'country',
        'city',
        'state',
        'zipcode',
        'password'*/



        