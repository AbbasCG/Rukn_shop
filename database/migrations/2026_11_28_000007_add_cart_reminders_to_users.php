<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Shop language last used (for emails sent later, like reminders).
            $table->string('locale', 5)->nullable();
            // "You left something in your cart" emails: on unless turned off.
            $table->boolean('cart_reminders')->default(true);
            // Which cart (its last change) the reminders were for, and how many were sent.
            $table->timestamp('cart_reminder_for')->nullable();
            $table->unsignedTinyInteger('cart_reminders_sent')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['locale', 'cart_reminders', 'cart_reminder_for', 'cart_reminders_sent']);
        });
    }
};
