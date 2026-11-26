<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The storefront language the order was placed in (nl/en/ar), so emails
     * about the order are sent in the customer's language.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('locale', 5)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
};
