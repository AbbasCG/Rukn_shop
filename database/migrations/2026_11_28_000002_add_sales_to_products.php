<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // A price reduction: "percent" (sale_value = % off) or "price"
            // (sale_value = the reduced price), optionally between two dates.
            $table->string('sale_type', 10)->nullable()->after('price');
            $table->decimal('sale_value', 10, 2)->nullable()->after('sale_type');
            $table->timestamp('sale_starts_at')->nullable()->after('sale_value');
            $table->timestamp('sale_ends_at')->nullable()->after('sale_starts_at');
            // For the 30-day rule on "was" prices (warning in the admin).
            $table->timestamp('regular_price_raised_at')->nullable()->after('sale_ends_at');
            $table->timestamp('last_sale_ended_at')->nullable()->after('regular_price_raised_at');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            // Reduced price of a size/colour that has its own price ("price" sales).
            $table->decimal('sale_price', 10, 2)->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('sale_price');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sale_type', 'sale_value', 'sale_starts_at', 'sale_ends_at', 'regular_price_raised_at', 'last_sale_ended_at']);
        });
    }
};
