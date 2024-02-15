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
        Schema::table('business_accounts', function (Blueprint $table) {
            //
           $table->string('verification_code')->nullable();
           $table->integer('is_verified')->default(value:0);
            $table->integer('product_status')->default(value:true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_accounts', function (Blueprint $table) {
            //
        });
    }
};
