<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductVariationImage;
use App\Models\VariationStock;
use App\Models\Brand;
use App\Models\Category;

class AmazonStyleProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create brand and category
        $brand = Brand::firstOrCreate(['name' => 'Allen Solly'], ['slug' => 'allen-solly']);
        $category = Category::firstOrCreate(['name' => 'Shirts'], ['slug' => 'shirts']);

        // Create attributes
        $colorAttr = Attribute::firstOrCreate(['name' => 'Color'], ['slug' => 'color']);
        $sizeAttr = Attribute::firstOrCreate(['name' => 'Size'], ['slug' => 'size']);

        // Create color values
        $whiteColor = AttributeValue::firstOrCreate([
            'attribute_id' => $colorAttr->id,
            'value' => 'White'
        ], ['code' => '#FFFFFF']);

        $blackColor = AttributeValue::firstOrCreate([
            'attribute_id' => $colorAttr->id,
            'value' => 'Black'
        ], ['code' => '#000000']);

        $blueColor = AttributeValue::firstOrCreate([
            'attribute_id' => $colorAttr->id,
            'value' => 'Blue'
        ], ['code' => '#0066CC']);

        // Create size values
        $sizeM = AttributeValue::firstOrCreate([
            'attribute_id' => $sizeAttr->id,
            'value' => 'M'
        ]);

        $sizeL = AttributeValue::firstOrCreate([
            'attribute_id' => $sizeAttr->id,
            'value' => 'L'
        ]);

        $sizeXL = AttributeValue::firstOrCreate([
            'attribute_id' => $sizeAttr->id,
            'value' => 'XL'
        ]);

        // Create the product
        $product = Product::create([
            'name' => 'Allen Solly Men\'s Shirt',
            'slug' => 'allen-solly-mens-shirt-premium-collection',
            'description' => 'Premium quality shirt with modern design. Perfect for formal and casual occasions. Made with 70% Cotton and 30% Linen blend for ultimate comfort and durability.',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 1299.00,
            'mrp' => 1999.00,
            'active' => true,
        ]);

        // Create variations for each color-size combination
        $colors = [$whiteColor, $blackColor, $blueColor];
        $sizes = [$sizeM, $sizeL, $sizeXL];

        foreach ($colors as $color) {
            foreach ($sizes as $size) {
                $variation = ProductVariation::create([
                    'product_id' => $product->id,
                    'sku' => 'AS-SHIRT-' . strtoupper($color->value) . '-' . $size->value,
                    'price' => $this->getVariationPrice($color->value, $size->value),
                    'min_qty' => 1,
                    'attribute_value_ids' => [$color->id, $size->id],
                ]);

                // Create stock for each variation
                VariationStock::create([
                    'product_variation_id' => $variation->id,
                    'quantity' => rand(5, 50),
                    'in_stock' => true,
                ]);

                // Create sample variation images (you would replace these with actual image paths)
                $this->createSampleImages($product->id, $variation->id, $color->value);
            }
        }

        $this->command->info('Amazon-style product with color variations created successfully!');
    }

    private function getVariationPrice($color, $size)
    {
        $basePrice = 1299;
        
        // Premium colors cost more
        if ($color === 'Black') {
            $basePrice += 100;
        } elseif ($color === 'Blue') {
            $basePrice += 50;
        }
        
        // Larger sizes cost more
        if ($size === 'XL') {
            $basePrice += 100;
        } elseif ($size === 'L') {
            $basePrice += 50;
        }
        
        return $basePrice;
    }

    private function createSampleImages($productId, $variationId, $color)
    {
        // This would be replaced with actual image upload logic
        // For now, we'll create placeholder image paths
        
        $imageBasePath = 'products/shirts/' . strtolower($color);
        
        $images = [
            $imageBasePath . '/front.jpg',
            $imageBasePath . '/back.jpg',
            $imageBasePath . '/side.jpg',
            $imageBasePath . '/detail.jpg'
        ];

        foreach ($images as $index => $imagePath) {
            ProductVariationImage::create([
                'product_id' => $productId,
                'product_variation_id' => $variationId,
                'path' => $imagePath,
                'alt' => ucfirst($color) . ' shirt ' . ($index + 1),
                'position' => $index + 1,
            ]);
        }
    }
}
