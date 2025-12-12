<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed categories first
        $this->call(CategorySeeder::class);

        // Seed products
        $this->call(ProductSeeder::class);

        // Create or update admin user
        User::updateOrCreate(
            ['email' => 'admin@test.nl'],
            [
                'name' => 'Admin User',
                'phone' => 612345678,
                'address' => 'Admin Street 1',
                'postal_code' => '1234 AB',
                'city' => 'Amsterdam',
                'country' => 'Netherlands',
                'role' => 'admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create or update test customer user
        User::updateOrCreate(
            ['email' => 'test@test.nl'],
            [
                'name' => 'Test User',
                'phone' => 612345678,
                'address' => 'Test Street 123',
                'postal_code' => '1234 AB',
                'city' => 'Amsterdam',
                'country' => 'Netherlands',
                'role' => 'customer',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create 10 additional random customer users
        User::factory()->customer()->count(10)->create();

        // Create 2 additional random admin users
        User::factory()->admin()->count(2)->create();
    }
}
