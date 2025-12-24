<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        $this->command->info('👤 Creating test user...');
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('123456'),
            'email_verified_at' => now(),
        ]);

        //Ecommerce seeding
        $this->command->info('🛍️  Starting ecommerce data seeding...');
        $this->call(EcommerceSeeder::class);
    
        $this->command->info('✅ Database seeding completed successfully!');
    }
}
