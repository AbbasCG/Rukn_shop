<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sizes and colours.
     *
     * products.size_system  null | lengths | letters | custom — how sizes are written
     * products.sizes        ordered list of sizes, e.g. ["52","54","56"]
     * products.colors       [{"key":"black","hex":"#111111","names":{"nl":"Zwart","en":"Black","ar":"أسود"}}]
     * product_variants      one row per size/colour combination, with its own stock
     *                       and an optional price (null = the product price)
     *
     * Products without sizes and colours keep using products.stock. For products
     * with variants, products.stock is kept as the sum of the variants' stock.
     * carts and order_items get the chosen variant; order_items also keep a
     * readable label ("54 · Black") as it was when ordered.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('size_system', 20)->nullable()->after('specifications');
            $table->json('sizes')->nullable()->after('size_system');
            $table->json('colors')->nullable()->after('sizes');
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('size', 50)->nullable();
            $table->string('color_key', 50)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'size', 'color_key']);
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('product_id')
                ->constrained('product_variants')->cascadeOnDelete();
        });

        // A cart may hold several variants of one product (52 and 54 of the
        // same abaya). The new unique index is added before the old one is
        // dropped: MySQL needs an index starting with user_id for its foreign key.
        Schema::table('carts', function (Blueprint $table) {
            $table->unique(['user_id', 'product_id', 'variant_id', 'status'], 'carts_user_product_variant_status_unique');
        });
        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'product_id', 'status']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('product_id')
                ->constrained('product_variants')->nullOnDelete();
            $table->string('variant_label')->nullable()->after('product_name');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['variant_id']);
            $table->dropColumn(['variant_id', 'variant_label']);
        });

        // Back to one line per product: keep the first variant line of each.
        $duplicates = \Illuminate\Support\Facades\DB::table('carts')
            ->selectRaw('min(id) as keep_id, user_id, product_id, status')
            ->groupBy('user_id', 'product_id', 'status')
            ->havingRaw('count(*) > 1')
            ->get();
        foreach ($duplicates as $row) {
            \Illuminate\Support\Facades\DB::table('carts')
                ->where(['user_id' => $row->user_id, 'product_id' => $row->product_id, 'status' => $row->status])
                ->where('id', '!=', $row->keep_id)
                ->delete();
        }

        Schema::table('carts', function (Blueprint $table) {
            $table->unique(['user_id', 'product_id', 'status']);
        });
        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique('carts_user_product_variant_status_unique');
            $table->dropForeign(['variant_id']);
            $table->dropColumn('variant_id');
        });

        Schema::dropIfExists('product_variants');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['size_system', 'sizes', 'colors']);
        });
    }
};
