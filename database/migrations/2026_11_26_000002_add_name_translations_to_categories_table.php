<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-locale category names: {"nl": "...", "en": "...", "ar": "..."}.
     * `name` stays as the base name and the fallback for missing locales.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->json('name_translations')->nullable()->after('name');
        });

        // Fill in the store's two existing categories, only where they exist
        // and have no translations yet.
        $known = [
            'abayas' => ['nl' => "Abaya's", 'en' => 'Abayas', 'ar' => 'عبايات'],
            'electric-mabkhara' => ['nl' => "Elektrische mabkhara's", 'en' => 'Electric mabkharas', 'ar' => 'مباخر كهربائية'],
        ];

        foreach ($known as $slug => $names) {
            DB::table('categories')
                ->where('slug', $slug)
                ->whereNull('name_translations')
                ->update(['name_translations' => json_encode($names, JSON_UNESCAPED_UNICODE)]);
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('name_translations');
        });
    }
};
