<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Deleting a product used to delete its lines from every past order
     * (order_items.product_id ... ON DELETE CASCADE), leaving orders whose
     * contents no longer matched their totals.
     *
     *  - order_items.product_name keeps the name as it was when ordered;
     *    existing rows are filled from the current product names.
     *  - product_id becomes nullable with ON DELETE SET NULL, so an order line
     *    survives the product being deleted.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('product_name')->nullable()->after('product_id');
        });

        DB::table('order_items')
            ->whereNull('product_name')
            ->update([
                'product_name' => DB::raw('(select name from products where products.id = order_items.product_id)'),
            ]);

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Lines whose product is gone cannot point at a product again.
        DB::table('order_items')->whereNull('product_id')->delete();

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->dropColumn('product_name');
        });
    }
};
