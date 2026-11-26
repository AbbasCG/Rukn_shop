<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Track & trace: carrier, tracking number, an optional custom tracking
 * link, and when the order was first marked as shipped (the shipping
 * email is sent once, at that moment).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_carrier', 20)->nullable();
            $table->string('tracking_number', 64)->nullable();
            $table->string('tracking_url', 500)->nullable();
            $table->timestamp('shipped_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tracking_carrier', 'tracking_number', 'tracking_url', 'shipped_at']);
        });
    }
};
