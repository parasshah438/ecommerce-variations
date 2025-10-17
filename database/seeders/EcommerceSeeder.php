<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\ProductVariationImage;
use App\Models\VariationStock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EcommerceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating brands...');
        $this->createBrands();
        
        $this->command->info('Creating categories...');
        $this->createCategories();
        
        $this->command->info('Creating attributes and values...');
        $this->createAttributes();
        
        $this->command->info('Creating 1000 products with variations...');
        $this->createProducts();
        
        $this->command->info('Creating coupons...');
        $this->createCoupons();
        
        $this->command->info('Ecommerce seeding completed successfully!');
    }

    private function createBrands(): void
    {
        $brands = [
            'Samsung', 'Apple', 'Sony', 'LG', 'Dell', 'HP', 'Lenovo', 'Asus',
            'Nike', 'Adidas', 'Puma', 'Reebok', 'Under Armour', 'New Balance',
            'Zara', 'H&M', 'Forever 21', 'Gap', 'Levi\'s', 'Calvin Klein',
            'IKEA', 'Ashley', 'West Elm', 'CB2', 'Pottery Barn',
            'KitchenAid', 'Cuisinart', 'Ninja', 'Hamilton Beach',
            'L\'Oreal', 'Maybelline', 'MAC', 'Urban Decay', 'Sephora',
            'Neutrogena', 'Cetaphil', 'The Ordinary', 'Clinique',
            'Canon', 'Nikon', 'Fujifilm', 'GoPro', 'DJI'
        ];

        foreach ($brands as $brandName) {
            Brand::create([
                'name' => $brandName,
                'slug' => Str::slug($brandName)
            ]);
        }
    }

    private function createCategories(): void
    {
        $categories = [
            'Electronics' => [
                'Smartphones', 'Laptops', 'Tablets', 'Smart Watches', 'Headphones',
                'Cameras', 'Gaming', 'Smart Home', 'Audio & Video'
            ],
            'Fashion' => [
                'Men\'s Clothing', 'Women\'s Clothing', 'Kids\' Clothing', 'Shoes',
                'Accessories', 'Bags & Wallets', 'Jewelry', 'Watches'
            ],
            'Home & Kitchen' => [
                'Furniture', 'Home Decor', 'Kitchen Appliances', 'Cookware',
                'Bedding', 'Bath', 'Storage & Organization', 'Cleaning Supplies'
            ],
            'Beauty & Personal Care' => [
                'Makeup', 'Skincare', 'Hair Care', 'Personal Care', 'Fragrances',
                'Men\'s Grooming', 'Nail Care', 'Beauty Tools'
            ],
            'Sports & Fitness' => [
                'Exercise Equipment', 'Sports Wear', 'Outdoor Gear', 'Team Sports',
                'Water Sports', 'Winter Sports', 'Fitness Accessories'
            ],
            'Books & Media' => [
                'Fiction', 'Non-Fiction', 'Educational', 'Children\'s Books',
                'E-books', 'Audiobooks', 'Movies & TV', 'Music'
            ]
        ];

        foreach ($categories as $parentName => $children) {
            $parent = Category::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'parent_id' => null
            ]);

            foreach ($children as $childName) {
                Category::create([
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'parent_id' => $parent->id
                ]);
            }
        }
    }

    private function createAttributes(): void
    {
        // Size attribute
        $size = Attribute::create(['name' => 'Size', 'slug' => 'size']);
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', '28', '30', '32', '34', '36', '38', '40', '42'];
        foreach ($sizes as $s) {
            AttributeValue::create(['attribute_id' => $size->id, 'value' => $s, 'code' => strtolower($s)]);
        }

        // Color attribute
        $color = Attribute::create(['name' => 'Color', 'slug' => 'color']);
        $colors = [
            'Red', 'Blue', 'Green', 'Black', 'White', 'Gray', 'Navy', 'Brown',
            'Pink', 'Purple', 'Yellow', 'Orange', 'Beige', 'Khaki', 'Maroon',
            'Turquoise', 'Coral', 'Mint', 'Lavender', 'Gold', 'Silver'
        ];
        foreach ($colors as $c) {
            AttributeValue::create(['attribute_id' => $color->id, 'value' => $c, 'code' => strtolower($c)]);
        }

        // Material attribute
        $material = Attribute::create(['name' => 'Material', 'slug' => 'material']);
        $materials = [
            'Cotton', 'Polyester', 'Silk', 'Denim', 'Leather', 'Wool', 'Linen',
            'Bamboo', 'Modal', 'Viscose', 'Nylon', 'Spandex', 'Acrylic'
        ];
        foreach ($materials as $m) {
            AttributeValue::create(['attribute_id' => $material->id, 'value' => $m, 'code' => strtolower($m)]);
        }

        // Storage attribute (for electronics)
        $storage = Attribute::create(['name' => 'Storage', 'slug' => 'storage']);
        $storages = ['16GB', '32GB', '64GB', '128GB', '256GB', '512GB', '1TB', '2TB'];
        foreach ($storages as $s) {
            AttributeValue::create(['attribute_id' => $storage->id, 'value' => $s, 'code' => strtolower($s)]);
        }

        // Memory attribute (for electronics)
        $memory = Attribute::create(['name' => 'RAM', 'slug' => 'ram']);
        $memories = ['4GB', '8GB', '16GB', '32GB', '64GB'];
        foreach ($memories as $m) {
            AttributeValue::create(['attribute_id' => $memory->id, 'value' => $m, 'code' => strtolower($m)]);
        }

        // Screen Size attribute
        $screenSize = Attribute::create(['name' => 'Screen Size', 'slug' => 'screen-size']);
        $screenSizes = ['13"', '14"', '15"', '17"', '21"', '24"', '27"', '32"', '43"', '55"', '65"'];
        foreach ($screenSizes as $s) {
            AttributeValue::create(['attribute_id' => $screenSize->id, 'value' => $s, 'code' => strtolower(str_replace('"', 'inch', $s))]);
        }
    }

    private function createProducts(): void
    {
        $categories = Category::whereNotNull('parent_id')->get();
        $brands = Brand::all();
        $sizeValues = AttributeValue::whereHas('attribute', fn($q) => $q->where('slug', 'size'))->pluck('id')->toArray();
        $colorValues = AttributeValue::whereHas('attribute', fn($q) => $q->where('slug', 'color'))->pluck('id')->toArray();
        $materialValues = AttributeValue::whereHas('attribute', fn($q) => $q->where('slug', 'material'))->pluck('id')->toArray();
        $storageValues = AttributeValue::whereHas('attribute', fn($q) => $q->where('slug', 'storage'))->pluck('id')->toArray();
        $ramValues = AttributeValue::whereHas('attribute', fn($q) => $q->where('slug', 'ram'))->pluck('id')->toArray();
        $screenValues = AttributeValue::whereHas('attribute', fn($q) => $q->where('slug', 'screen-size'))->pluck('id')->toArray();

        $productTemplates = $this->getProductTemplates();

        for ($i = 1; $i <= 1000; $i++) {
            $template = $productTemplates[array_rand($productTemplates)];
            $category = $categories->random();
            $brand = $brands->random();
            
            $basePrice = rand(100, 10000);
            $mrp = $basePrice + rand(100, 2000);
            
            $productName = $template['name'] . ' ' . $brand->name . ' ' . $this->generateProductSuffix();
            
            $product = Product::create([
                'name' => $productName,
                'slug' => Str::slug($productName) . '-' . $i,
                'description' => $this->generateDescription($template['description'], $brand->name),
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'price' => $basePrice,
                'mrp' => $mrp,
                'active' => rand(0, 10) > 1, // 90% active products
            ]);

            // Create product images
            $this->createProductImages($product);

            // Create variations based on category
            $this->createProductVariations($product, $category, [
                'size' => $sizeValues,
                'color' => $colorValues,
                'material' => $materialValues,
                'storage' => $storageValues,
                'ram' => $ramValues,
                'screen' => $screenValues,
            ]);

            if ($i % 100 == 0) {
                $this->command->info("Created $i products...");
            }
        }
    }

    private function getProductTemplates(): array
    {
        return [
            // Electronics
            ['name' => 'Smartphone', 'description' => 'Latest smartphone with advanced features and sleek design'],
            ['name' => 'Laptop', 'description' => 'High-performance laptop perfect for work and entertainment'],
            ['name' => 'Tablet', 'description' => 'Portable tablet with stunning display and long battery life'],
            ['name' => 'Smart Watch', 'description' => 'Intelligent wearable with health tracking and notifications'],
            ['name' => 'Wireless Headphones', 'description' => 'Premium wireless headphones with noise cancellation'],
            ['name' => 'Gaming Console', 'description' => 'Next-generation gaming console with immersive graphics'],
            ['name' => 'Smart TV', 'description' => 'Ultra HD smart TV with streaming capabilities'],
            ['name' => 'Camera', 'description' => 'Professional camera for capturing stunning photos and videos'],
            
            // Fashion
            ['name' => 'T-Shirt', 'description' => 'Comfortable and stylish t-shirt for everyday wear'],
            ['name' => 'Jeans', 'description' => 'Classic denim jeans with perfect fit and durability'],
            ['name' => 'Sneakers', 'description' => 'Comfortable sneakers perfect for sports and casual wear'],
            ['name' => 'Hoodie', 'description' => 'Cozy hoodie with soft fabric and modern design'],
            ['name' => 'Dress', 'description' => 'Elegant dress perfect for special occasions'],
            ['name' => 'Jacket', 'description' => 'Stylish jacket to keep you warm and fashionable'],
            ['name' => 'Backpack', 'description' => 'Durable backpack with multiple compartments'],
            ['name' => 'Watch', 'description' => 'Classic timepiece with elegant design'],
            
            // Home & Kitchen
            ['name' => 'Coffee Maker', 'description' => 'Premium coffee maker for perfect brew every morning'],
            ['name' => 'Blender', 'description' => 'Powerful blender for smoothies and food preparation'],
            ['name' => 'Sofa', 'description' => 'Comfortable sofa perfect for living room relaxation'],
            ['name' => 'Dining Table', 'description' => 'Elegant dining table for family gatherings'],
            ['name' => 'Bed Sheet Set', 'description' => 'Luxurious bed sheets for comfortable sleep'],
            ['name' => 'Cookware Set', 'description' => 'Professional cookware set for culinary excellence'],
            ['name' => 'Vacuum Cleaner', 'description' => 'Efficient vacuum cleaner for spotless cleaning'],
            ['name' => 'Air Purifier', 'description' => 'Advanced air purifier for clean and fresh air'],
            
            // Beauty & Personal Care
            ['name' => 'Face Cream', 'description' => 'Nourishing face cream for healthy and glowing skin'],
            ['name' => 'Shampoo', 'description' => 'Premium shampoo for strong and shiny hair'],
            ['name' => 'Perfume', 'description' => 'Luxury fragrance with long-lasting scent'],
            ['name' => 'Lipstick', 'description' => 'High-quality lipstick with vibrant colors'],
            ['name' => 'Foundation', 'description' => 'Perfect coverage foundation for flawless complexion'],
            ['name' => 'Moisturizer', 'description' => 'Hydrating moisturizer for soft and smooth skin'],
            
            // Sports & Fitness
            ['name' => 'Yoga Mat', 'description' => 'Premium yoga mat for comfortable practice'],
            ['name' => 'Dumbbells', 'description' => 'Professional dumbbells for strength training'],
            ['name' => 'Running Shoes', 'description' => 'High-performance running shoes for athletes'],
            ['name' => 'Fitness Tracker', 'description' => 'Smart fitness tracker with health monitoring'],
            ['name' => 'Sports Bottle', 'description' => 'Insulated sports bottle for hydration'],
            ['name' => 'Exercise Bike', 'description' => 'Indoor exercise bike for cardio workouts'],
        ];
    }

    private function generateProductSuffix(): string
    {
        $suffixes = [
            'Pro', 'Max', 'Ultra', 'Premium', 'Elite', 'Advanced', 'Plus', 'Lite',
            'Standard', 'Essential', 'Classic', 'Modern', 'Deluxe', 'Sport',
            '2024', '2025', 'Series', 'Collection', 'Edition'
        ];
        
        return $suffixes[array_rand($suffixes)];
    }

    private function generateDescription(string $baseDescription, string $brandName): string
    {
        $features = [
            'Premium quality materials ensure long-lasting durability.',
            'Innovative design meets modern functionality.',
            'Carefully crafted with attention to detail.',
            'Perfect blend of style and performance.',
            'Trusted quality you can rely on.',
            'Advanced technology for superior results.',
            'Ergonomic design for maximum comfort.',
            'Eco-friendly and sustainable materials.',
            'Easy to use and maintain.',
            'Backed by manufacturer warranty.'
        ];

        $randomFeatures = array_rand($features, rand(2, 4));
        $additionalFeatures = '';
        foreach ($randomFeatures as $key) {
            $additionalFeatures .= ' ' . $features[$key];
        }

        return $baseDescription . ' From ' . $brandName . ', this product combines quality and innovation.' . $additionalFeatures;
    }

    private function createProductImages(Product $product): void
    {
        $imageCount = rand(2, 6);
        
        for ($i = 0; $i < $imageCount; $i++) {
            ProductImage::create([
                'product_id' => $product->id,
                'path' => 'https://picsum.photos/800/800?random=' . ($product->id * 10 + $i),
                'alt' => $product->name . ' - Image ' . ($i + 1),
                'position' => $i,
                'product_variation_id' => null
            ]);
        }
    }

    private function createProductVariations(Product $product, Category $category, array $attributeValues): void
    {
        $categoryName = strtolower($category->name);
        $parentCategoryName = strtolower($category->parent->name ?? '');
        
        // Determine which attributes to use based on category
        $applicableAttributes = $this->getApplicableAttributes($categoryName, $parentCategoryName, $attributeValues);
        
        // Generate variations (limit to prevent too many combinations)
        $variations = $this->generateVariationCombinations($applicableAttributes, 20); // Max 20 variations per product
        
        foreach ($variations as $index => $variationAttributes) {
            $variationPrice = $product->price + rand(-200, 500); // Price variation
            $variationPrice = max(50, $variationPrice); // Minimum price
            
            $variation = ProductVariation::create([
                'product_id' => $product->id,
                'sku' => strtoupper(substr($product->slug, 0, 3)) . '-' . strtoupper(Str::random(6)),
                'price' => $variationPrice,
                'min_qty' => rand(1, 5),
                'attribute_value_ids' => json_encode($variationAttributes),
            ]);

            // Create stock for variation
            $quantity = rand(0, 50);
            VariationStock::create([
                'product_variation_id' => $variation->id,
                'quantity' => $quantity,
                'in_stock' => $quantity > 0,
            ]);

            // Create variation-specific images (25% chance)
            if (rand(1, 4) == 1) {
                $this->createVariationImages($product, $variation);
            }
        }
    }

    private function getApplicableAttributes(string $categoryName, string $parentCategoryName, array $attributeValues): array
    {
        $applicable = [];
        
        // Fashion categories
        if (str_contains($parentCategoryName, 'fashion') || 
            str_contains($categoryName, 'clothing') || 
            str_contains($categoryName, 'shoes')) {
            $applicable['size'] = array_slice($attributeValues['size'], 0, 5); // Limit sizes
            $applicable['color'] = array_slice($attributeValues['color'], 0, 8); // Limit colors
            if (rand(1, 3) == 1) { // 33% chance to include material
                $applicable['material'] = array_slice($attributeValues['material'], 0, 3);
            }
        }
        
        // Electronics categories
        elseif (str_contains($parentCategoryName, 'electronics') || 
                str_contains($categoryName, 'laptop') || 
                str_contains($categoryName, 'smartphone')) {
            $applicable['color'] = array_slice($attributeValues['color'], 0, 5);
            if (str_contains($categoryName, 'laptop') || str_contains($categoryName, 'smartphone')) {
                $applicable['storage'] = array_slice($attributeValues['storage'], 0, 4);
                $applicable['ram'] = array_slice($attributeValues['ram'], 0, 3);
            }
            if (str_contains($categoryName, 'tv') || str_contains($categoryName, 'monitor')) {
                $applicable['screen'] = array_slice($attributeValues['screen'], 0, 4);
            }
        }
        
        // Default: just color variations
        else {
            $applicable['color'] = array_slice($attributeValues['color'], 0, rand(3, 6));
        }
        
        return $applicable;
    }

    private function generateVariationCombinations(array $applicableAttributes, int $maxCombinations): array
    {
        if (empty($applicableAttributes)) {
            return [[]]; // Return single empty variation
        }
        
        $combinations = [[]];
        
        foreach ($applicableAttributes as $attributeType => $values) {
            $newCombinations = [];
            foreach ($combinations as $combination) {
                foreach (array_slice($values, 0, rand(2, min(5, count($values)))) as $value) {
                    $newCombination = $combination;
                    $newCombination[] = $value;
                    $newCombinations[] = $newCombination;
                }
            }
            $combinations = $newCombinations;
            
            // Limit combinations to prevent explosion
            if (count($combinations) > $maxCombinations) {
                $combinations = array_slice($combinations, 0, $maxCombinations);
                break;
            }
        }
        
        return array_slice($combinations, 0, $maxCombinations);
    }

    private function createVariationImages(Product $product, ProductVariation $variation): void
    {
        $imageCount = rand(1, 3);
        
        for ($i = 0; $i < $imageCount; $i++) {
            ProductVariationImage::create([
                'product_id' => $product->id,
                'product_variation_id' => $variation->id,
                'path' => 'https://picsum.photos/800/800?random=' . ($variation->id * 100 + $i),
                'alt' => $product->name . ' - Variation ' . $variation->id . ' - Image ' . ($i + 1),
                'position' => $i,
            ]);
        }
    }

    private function createCoupons(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME50', 
                'discount' => 50, 
                'type' => 'fixed', 
                'valid_from' => now(), 
                'valid_until' => now()->addMonths(3),
                'minimum_cart_value' => 200,
                'maximum_discount_limit' => null,
                'usage_limit' => 100
            ],
            [
                'code' => 'SUMMER25', 
                'discount' => 25, 
                'type' => 'percentage', 
                'valid_from' => now(), 
                'valid_until' => now()->addMonths(2),
                'minimum_cart_value' => 300,
                'maximum_discount_limit' => 150,
                'usage_limit' => 50
            ],
            [
                'code' => 'NEWUSER100', 
                'discount' => 100, 
                'type' => 'fixed', 
                'valid_from' => now(), 
                'valid_until' => now()->addMonth(),
                'minimum_cart_value' => 500,
                'maximum_discount_limit' => null,
                'usage_limit' => 200
            ],
            [
                'code' => 'SAVE15', 
                'discount' => 15, 
                'type' => 'percentage', 
                'valid_from' => now(), 
                'valid_until' => now()->addMonths(6),
                'minimum_cart_value' => 100,
                'maximum_discount_limit' => 100,
                'usage_limit' => null
            ],
            [
                'code' => 'FLASH200', 
                'discount' => 200, 
                'type' => 'fixed', 
                'valid_from' => now(), 
                'valid_until' => now()->addWeeks(2),
                'minimum_cart_value' => 800,
                'maximum_discount_limit' => null,
                'usage_limit' => 25
            ],
            [
                'code' => 'WEEKEND10', 
                'discount' => 10, 
                'type' => 'percentage', 
                'valid_from' => now(), 
                'valid_until' => now()->addMonths(1),
                'minimum_cart_value' => 200,
                'maximum_discount_limit' => 50,
                'usage_limit' => null
            ],
            [
                'code' => 'MEGA30', 
                'discount' => 30, 
                'type' => 'percentage', 
                'valid_from' => now(), 
                'valid_until' => now()->addDays(30),
                'minimum_cart_value' => 1000,
                'maximum_discount_limit' => 300,
                'usage_limit' => 10
            ],
            [
                'code' => 'CLEARANCE500', 
                'discount' => 500, 
                'type' => 'fixed', 
                'valid_from' => now(), 
                'valid_until' => now()->addWeeks(3),
                'minimum_cart_value' => 600, // Requires minimum ₹600 cart
                'maximum_discount_limit' => 450, // Maximum ₹450 discount
                'usage_limit' => 5
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(['code' => $coupon['code']], $coupon);
        }
    }
}
