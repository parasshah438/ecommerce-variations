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
        //$this->command->info('🌱 Starting database seeding...');
        
        //Create admin user
        // $this->command->info('👤 Creating admin user...');
        // User::factory()->create([
        //     'name' => 'Admin User',
        //     'email' => 'admin@example.com',
        //     'password' => Hash::make('password123'),
        //     'email_verified_at' => now(),
        // ]);

        // Create test user
        $this->command->info('👤 Creating test user...');
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('123456'),
            'email_verified_at' => now(),
        ]);

        $this->call(TestProductSeeder::class);
        /*
        // Create additional sample users
        $this->command->info('👥 Creating sample users...');
        User::factory(8)->create();

        // Ecommerce seeding
        $this->command->info('🛍️  Starting ecommerce data seeding...');
        $this->call(EcommerceSeeder::class);
        
        // Test product with comprehensive variations
        $this->command->info('👔 Creating test product with full variations...');
        $this->call(TestProductSeeder::class);
        
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('   • Users: ' . User::count());
        $this->command->info('   • Brands: ' . \App\Models\Brand::count());
        $this->command->info('   • Categories: ' . \App\Models\Category::count());
        $this->command->info('   • Products: ' . \App\Models\Product::count());
        $this->command->info('   • Product Variations: ' . \App\Models\ProductVariation::count());
        $this->command->info('   • Product Images: ' . \App\Models\ProductImage::count());
        $this->command->info('   • Coupons: ' . \App\Models\Coupon::count());
        $this->command->info('');
        $this->command->info('🔐 Login credentials:');
        $this->command->info('   Admin: admin@example.com / password123');
        $this->command->info('   Test:  test@example.com / password123');
        */
    }
}
