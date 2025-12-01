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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('pending');  // pending, processing, shipped, cancelled
            $table->string('payment_status')->default('open');  // open, paid, failed, refunded
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();    // bijv. 'ideal', 'creditcard'
            $table->string('payment_reference')->nullable();    // payment id van Mollie/Stripe
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
