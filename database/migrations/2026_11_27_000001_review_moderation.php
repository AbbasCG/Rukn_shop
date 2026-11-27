<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reviews from verified buyers, published after the owner approves them.
 *
 *  - reviews.order_id links a review to the order it comes from (also for
 *    guest orders, so user_id becomes nullable). One review per product per
 *    order.
 *  - reviews.author_name is the name shown with the review; status is
 *    pending / approved / rejected. Reviews that already existed stay
 *    visible: they are marked approved.
 *  - orders.delivered_at (set when an order is first marked delivered) and
 *    orders.review_requested_at drive the review request email.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('author_name', 60)->nullable()->after('order_id');
            $table->string('status', 10)->default('pending')->after('comment')->index();
            $table->string('locale', 5)->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('locale');
            $table->unique(['order_id', 'product_id']);
        });

        // Accounts can be deleted; their reviews then stay, without the link.
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        DB::table('reviews')->orderBy('id')->get(['id', 'user_id', 'created_at'])->each(function ($review) {
            DB::table('reviews')->where('id', $review->id)->update([
                'status' => 'approved',
                'approved_at' => $review->created_at,
                'author_name' => DB::table('users')->where('id', $review->user_id)->value('name'),
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('review_requested_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivered_at', 'review_requested_at']);
        });

        // Reviews without an account cannot point at a user again.
        DB::table('reviews')->whereNull('user_id')->delete();

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->dropForeign(['order_id']);
            $table->dropUnique(['order_id', 'product_id']);
            $table->dropColumn(['order_id', 'author_name', 'status', 'locale', 'approved_at']);
        });
    }
};
