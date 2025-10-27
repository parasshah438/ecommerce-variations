<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and gadgets',
                'is_active' => true,
                'children' => [
                    ['name' => 'Mobile Phones', 'description' => 'Smartphones and accessories'],
                    ['name' => 'Laptops', 'description' => 'Laptops and notebooks'],
                    ['name' => 'Headphones', 'description' => 'Audio devices and headphones'],
                ]
            ],
            [
                'name' => 'Clothing',
                'description' => 'Fashion and apparel',
                'is_active' => true,
                'children' => [
                    ['name' => 'Men\'s Clothing', 'description' => 'Clothing for men'],
                    ['name' => 'Women\'s Clothing', 'description' => 'Clothing for women'],
                    ['name' => 'Kids Clothing', 'description' => 'Clothing for children'],
                ]
            ],
            [
                'name' => 'Home & Garden',
                'description' => 'Home decor and garden supplies',
                'is_active' => true,
                'children' => [
                    ['name' => 'Furniture', 'description' => 'Home furniture'],
                    ['name' => 'Kitchen', 'description' => 'Kitchen appliances and tools'],
                    ['name' => 'Garden Tools', 'description' => 'Gardening equipment'],
                ]
            ],
            [
                'name' => 'Sports & Outdoors',
                'description' => 'Sports equipment and outdoor gear',
                'is_active' => true,
                'children' => [
                    ['name' => 'Fitness Equipment', 'description' => 'Gym and fitness gear'],
                    ['name' => 'Outdoor Gear', 'description' => 'Camping and hiking equipment'],
                ]
            ],
            [
                'name' => 'Books',
                'description' => 'Books and literature',
                'is_active' => true,
                'children' => [
                    ['name' => 'Fiction', 'description' => 'Fiction books and novels'],
                    ['name' => 'Non-Fiction', 'description' => 'Educational and reference books'],
                ]
            ]
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);
            
            $categoryData['slug'] = Str::slug($categoryData['name']);
            $parent = Category::create($categoryData);

            foreach ($children as $childData) {
                $childData['parent_id'] = $parent->id;
                $childData['slug'] = Str::slug($childData['name']);
                $childData['is_active'] = true;
                Category::create($childData);
            }
        }
    }
}
