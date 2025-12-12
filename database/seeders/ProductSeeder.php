<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Make sure we have categories first
        // if (Category::count() === 0) {
        //     // Create some default categories
        //     Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
        //     Category::create(['name' => 'Clothing', 'slug' => 'clothing']);
        //     Category::create(['name' => 'Home & Garden', 'slug' => 'home-garden']);
        //     Category::create(['name' => 'Books', 'slug' => 'books']);
        //     Category::create(['name' => 'Sports', 'slug' => 'sports']);
        // }

        // Create 50 products
        Product::factory()->count(50)->create();

        // Create 5 out of stock products
        Product::factory()->outOfStock()->count(5)->create();

        // Create 3 inactive products
        Product::factory()->inactive()->count(3)->create();
    }
}
