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
        Schema::create('individual_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('userID');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('verification_code')->nullable();
            $table->integer('is_verified')->default(value:0);
            $table->string('phone');
            $table->string('product');
            $table->string('country');
            $table->string('state');
            $table->string('city');
            $table->string('zipcode')->nullable();
            $table->string('password');
            $table->string('profile_photo')->nullable();
           // $table->string('password');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('individual_accounts');
    }
};
/*'userID',
        'firstname',
        'lastname',
        'email',
        'phonenumber',
        'products',
        'address',
        'country',
        'city',
        'state',
        'zipcode',
        'password'*/