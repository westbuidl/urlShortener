<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   /* public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('is_active');
            // $table->string('product_image1')->nullable();
            //$table->string('product_image2')->nullable();
            //$table->string('product_image3')->nullable();
            //$table->string('product_image4')->nullable();
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
            //$table->string('active_state');
        });
    }
};
