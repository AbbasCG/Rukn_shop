<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Invoice numbers: one unbroken sequence per year (2026-0001, 2026-0002…),
 * as Dutch tax rules require. invoice_counters holds the last number used
 * per year; an order gets its number when it is paid online or first
 * shipped, so cancelled orders never use one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_counters', function (Blueprint $table) {
            $table->unsignedSmallInteger('year')->primary();
            $table->unsignedInteger('last_number')->default(0);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('invoice_number', 20)->nullable()->unique();
            $table->timestamp('invoiced_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['invoice_number']);
            $table->dropColumn(['invoice_number', 'invoiced_at']);
        });

        Schema::dropIfExists('invoice_counters');
    }
};
