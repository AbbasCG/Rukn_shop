<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Guest checkout.
     *  - orders.user_id becomes nullable: guests have no account. The foreign
     *    key also changes from CASCADE to SET NULL, so deleting a customer
     *    account no longer deletes their orders (the shop's records).
     *  - orders.access_token: a random secret per order. The private order
     *    link (/{locale}/orders/view/{order}/{token}) uses it, so guests can
     *    open their order without an account. Deliberately not a signed URL:
     *    those break when APP_KEY is rotated.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('access_token', 64)->nullable()->unique()->after('user_id');
        });

        DB::table('orders')->whereNull('access_token')->orderBy('id')->pluck('id')
            ->each(fn ($id) => DB::table('orders')->where('id', $id)->update(['access_token' => Str::random(40)]));

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Orders without an account cannot point at a user again.
        DB::table('orders')->whereNull('user_id')->delete();

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->dropUnique(['access_token']);
            $table->dropColumn('access_token');
        });
    }
};
