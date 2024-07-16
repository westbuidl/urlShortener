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
        Schema::table('company_sellers', function (Blueprint $table) {
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();  
            $table->string('bank_name')->nullable(); 
            $table->string('accrued_profit')->nullable(); 
            $table->string('platform_fee')->nullable(); 
            
            //
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_sellers', function (Blueprint $table) {
            //
        });
    }
};
