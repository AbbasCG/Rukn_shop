<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default categories
        $categories = [
            ['name' => 'Abayas', 'slug' => 'abayas', 'description' => 'Traditional and modern abayas', 'name_translations' => ['nl' => "Abaya's", 'en' => 'Abayas', 'ar' => 'عبايات']],
            ['name' => 'Electric Mabkhara', 'slug' => 'electric-mabkhara', 'description' => 'Electric Mabkhara and incense burners', 'name_translations' => ['nl' => "Elektrische mabkhara's", 'en' => 'Electric mabkharas', 'ar' => 'مباخر كهربائية']],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'name_translations' => $category['name_translations'],
                'slug' => $category['slug'],
                'description' => $category['description'],
                'is_active' => true,
            ]);
        }

        // Create additional random categories
        Category::factory()->count(5)->create();
    }
}
