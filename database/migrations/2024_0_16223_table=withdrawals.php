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

        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->string('withdrawal_id');
            $table->string('seller_id');
            $table->string('amount');
            $table->string('bank_name');
            $table->string('account_name');
            $table->string('account_number');
            $table->string('status');
            $table->string('initiated_at');
            $table->string('completed_at');
            $table->string('seller_type');
           
            $table->timestamps();
        });
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
