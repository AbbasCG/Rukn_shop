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
            ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Electronic devices and gadgets'],
            ['name' => 'Clothing', 'slug' => 'clothing', 'description' => 'Fashion and apparel items'],
            ['name' => 'Home & Garden', 'slug' => 'home-garden', 'description' => 'Home decor and gardening supplies'],
            ['name' => 'Books', 'slug' => 'books', 'description' => 'Physical and digital books'],
            ['name' => 'Sports', 'slug' => 'sports', 'description' => 'Sports equipment and accessories'],
            ['name' => 'Beauty', 'slug' => 'beauty', 'description' => 'Beauty and personal care products'],
            ['name' => 'Toys & Games', 'slug' => 'toys-games', 'description' => 'Toys, games, and puzzles'],
            ['name' => 'Food & Beverages', 'slug' => 'food-beverages', 'description' => 'Food, drinks, and snacks'],
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
