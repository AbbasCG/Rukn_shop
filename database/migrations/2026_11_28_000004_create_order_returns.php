<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Returns and their credit notes (creditfacturen). A credit note takes the
 * next number of the invoice sequence (one unbroken series per year) and
 * refers to the order's invoice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('credit_note_number', 20)->unique();
            $table->decimal('items_amount', 10, 2);
            $table->decimal('shipping_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);           // refunded, incl. VAT
            $table->decimal('vat_rate', 5, 2);
            $table->decimal('vat_amount', 10, 2);
            $table->boolean('restocked')->default(false);
            $table->text('reason')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('order_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('price', 10, 2);           // unit price paid
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_return_items');
        Schema::dropIfExists('order_returns');
    }
};
