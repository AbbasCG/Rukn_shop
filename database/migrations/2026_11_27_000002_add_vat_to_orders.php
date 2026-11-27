<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The VAT rate and the VAT amount included in the order total, stored at
 * checkout so an order keeps its figures if the rate ever changes (and an
 * invoice can be made from them). Older orders keep NULL: unknown.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('vat_rate', 5, 2)->nullable();
            $table->decimal('vat_amount', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['vat_rate', 'vat_amount']);
        });
    }
};
