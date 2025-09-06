<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\VariationStock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating Men\'s White Shirt with full variations...');

        // Get or create necessary data
        $brand = Brand::firstOrCreate(['name' => 'Nike'], ['slug' => 'nike']);
        $category = Category::firstOrCreate(
            ['name' => 'Men\'s Clothing'],
            ['slug' => 'mens-clothing', 'parent_id' => null]
        );

        // Get size and color attributes
        $sizeAttribute = Attribute::where('slug', 'size')->first();
        $colorAttribute = Attribute::where('slug', 'color')->first();

        if (!$sizeAttribute || !$colorAttribute) {
            $this->command->error('Size and Color attributes not found. Please run EcommerceSeeder first.');
            return;
        }

        // Get specific attribute values
        $sizes = [
            'L' => AttributeValue::where('attribute_id', $sizeAttribute->id)->where('value', 'L')->first(),
            'M' => AttributeValue::where('attribute_id', $sizeAttribute->id)->where('value', 'M')->first(),
            'XL' => AttributeValue::where('attribute_id', $sizeAttribute->id)->where('value', 'XL')->first(),
            'XXL' => AttributeValue::where('attribute_id', $sizeAttribute->id)->where('value', 'XXL')->first(),
            '4XL' => AttributeValue::firstOrCreate(
                ['attribute_id' => $sizeAttribute->id, 'value' => '4XL'],
                ['code' => '4xl']
            ),
        ];

        $colors = [
            'White' => AttributeValue::firstOrCreate(
                ['attribute_id' => $colorAttribute->id, 'value' => 'White'],
                ['code' => 'white']
            ),
            'Black' => AttributeValue::where('attribute_id', $colorAttribute->id)->where('value', 'Black')->first(),
            'Blue' => AttributeValue::where('attribute_id', $colorAttribute->id)->where('value', 'Blue')->first(),
            'Green' => AttributeValue::where('attribute_id', $colorAttribute->id)->where('value', 'Green')->first(),
            'Red' => AttributeValue::where('attribute_id', $colorAttribute->id)->where('value', 'Red')->first(),
        ];

        // Create the product
        $product = Product::create([
            'name' => 'Men\'s White Shirt Nike Pro',
            'slug' => 'mens-white-shirt-nike-pro-' . time(),
            'description' => 'Premium men\'s white shirt with classic fit. Perfect for office wear, casual outings, and formal events. Made from high-quality cotton blend for comfort and durability. Features button-down collar, long sleeves, and modern cut.',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 2499, // Base price ₹24.99
            'mrp' => 3499,   // MRP ₹34.99
            'active' => true,
        ]);

        // Create product images
        for ($i = 0; $i < 4; $i++) {
            ProductImage::create([
                'product_id' => $product->id,
                'path' => 'https://picsum.photos/800/800?random=' . ($product->id * 10 + $i),
                'alt' => $product->name . ' - Image ' . ($i + 1),
                'position' => $i,
                'product_variation_id' => null
            ]);
        }

        // Create variations for all size/color combinations
        $variationCount = 0;
        $createdVariations = [];
        
        foreach ($sizes as $sizeName => $sizeValue) {
            if (!$sizeValue) continue;

            foreach ($colors as $colorName => $colorValue) {
                if (!$colorValue) continue;

                // Price variation (slight differences)
                $variationPrice = $product->price + rand(-100, 200);
                $variationPrice = max(1999, $variationPrice); // Minimum price

                $variation = ProductVariation::create([
                    'product_id' => $product->id,
                    'sku' => 'SHIRT-' . strtoupper(substr($colorName, 0, 2)) . '-' . $sizeName . '-' . strtoupper(Str::random(4)),
                    'price' => $variationPrice,
                    'min_qty' => 1,
                    'attribute_value_ids' => [$sizeValue->id, $colorValue->id],
                ]);

                // Create stock - Only White 4XL has 0 stock, all others have stock
                if ($sizeName === '4XL' && $colorName === 'White') {
                    $quantity = 0; // White 4XL out of stock
                } else {
                    $quantity = rand(15, 50); // All other combinations have stock
                }
                
                VariationStock::create([
                    'product_variation_id' => $variation->id,
                    'quantity' => $quantity,
                    'in_stock' => $quantity > 0,
                ]);

                // Store variation for image creation
                $createdVariations[$colorName][] = $variation;

                $variationCount++;
                $stockStatus = $quantity > 0 ? "Stock: {$quantity}" : "OUT OF STOCK";
                $this->command->info("Created variation: {$colorName} {$sizeName} - {$stockStatus}");
            }
        }

        // Create color-specific variation images
        $this->createVariationImages($product, $createdVariations);

        $variationCount++;
        $this->command->info("✅ Created product: {$product->name}");
        $this->command->info("✅ Created {$variationCount} variations");
        $this->command->info("✅ Size availability: L, M, XL, XXL, 4XL (full stock for all colors)");
        $this->command->info("✅ Color availability: White, Black, Blue, Green, Red");
        $this->command->info("⚠️  Exception: White 4XL is OUT OF STOCK");
        $this->command->info("🖼️  Color-specific images created for each variation");
        $this->command->info("✅ Product URL: /products/{$product->slug}");
        
        $this->command->info("\n🎯 Test the smart variation selection:");
        $this->command->info("1. Select any size L/M/XL/XXL → All 5 colors enabled");
        $this->command->info("2. Select size 4XL → White disabled, other colors enabled");
        $this->command->info("3. Select White color → L,M,XL,XXL enabled, 4XL disabled");
        $this->command->info("4. Select any other color → All sizes enabled including 4XL");
        $this->command->info("5. Change colors → Images change automatically!");
    }

    /**
     * Create color-specific images for variations
     */
    private function createVariationImages(Product $product, array $variationsByColor): void
    {
        $colorImageUrls = [
            'White' => [
                'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&h=800&fit=crop', // White shirt
                'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=800&h=800&fit=crop', // White shirt detail
            ],
            'Black' => [
                'https://images.unsplash.com/photo-1603252109612-ffd93bd59cd6?w=800&h=800&fit=crop', // Black shirt
                'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=800&h=800&fit=crop', // Black shirt detail
            ],
            'Blue' => [
                'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=800&h=800&fit=crop', // Blue shirt
                'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?w=800&h=800&fit=crop', // Blue shirt detail
            ],
            'Green' => [
                'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=800&fit=crop', // Green shirt
                'https://images.unsplash.com/photo-1618453292465-97b1b5d6e9c0?w=800&h=800&fit=crop', // Green shirt detail
            ],
            'Red' => [
                'https://images.unsplash.com/photo-1586790170083-2f9ceadc732d?w=800&h=800&fit=crop', // Red shirt
                'https://images.unsplash.com/photo-1618453292930-65048793d49c?w=800&h=800&fit=crop', // Red shirt detail
            ],
        ];

        foreach ($variationsByColor as $colorName => $variations) {
            if (!isset($colorImageUrls[$colorName])) continue;

            // Create 2 images per color variation
            foreach ($colorImageUrls[$colorName] as $index => $imageUrl) {
                \App\Models\ProductVariationImage::create([
                    'product_id' => $product->id,
                    'product_variation_id' => $variations[0]->id, // Use first variation of this color
                    'path' => $imageUrl,
                    'alt' => $product->name . ' - ' . $colorName . ' - Image ' . ($index + 1),
                    'position' => $index,
                ]);
            }

            $this->command->info("🖼️  Created images for {$colorName} variations");
        }
    }
}
