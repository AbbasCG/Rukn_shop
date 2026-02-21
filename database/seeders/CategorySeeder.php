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
            ['name' => 'Abayas', 'slug' => 'abayas', 'description' => 'Traditional and modern abayas'],
            ['name' => 'Electric Mabkhara', 'slug' => 'electric-mabkhara', 'description' => 'Electric Mabkhara and incense burners'],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => $category['slug'],
                'description' => $category['description'],
                'is_active' => true,
            ]);
        }

        // Create additional random categories
        Category::factory()->count(5)->create();
    }
}
