<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariation;

class VariationTestDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Creating size and color attributes...');

        // Create Size attribute
        $sizeAttribute = Attribute::firstOrCreate([
            'name' => 'Size',
            'slug' => 'size'
        ], [
            'type' => 'select',
            'is_required' => true,
            'is_filterable' => true
        ]);

        // Create Color attribute  
        $colorAttribute = Attribute::firstOrCreate([
            'name' => 'Color',
            'slug' => 'color'
        ], [
            'type' => 'select',
            'is_required' => true,
            'is_filterable' => true
        ]);

        $this->command->info("Size attribute ID: " . $sizeAttribute->id);
        $this->command->info("Color attribute ID: " . $colorAttribute->id);

        // Create size values
        $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
        $sizeValues = [];

        foreach ($sizes as $size) {
            $sizeValue = AttributeValue::firstOrCreate([
                'attribute_id' => $sizeAttribute->id,
                'value' => $size
            ], [
                'code' => strtolower($size),
                'is_default' => $size === 'M'
            ]);
            $sizeValues[] = $sizeValue;
            $this->command->info("Created size: {$size} (ID: {$sizeValue->id})");
        }

        // Create color values
        $colors = [
            ['name' => 'Red', 'hex' => '#FF0000'],
            ['name' => 'Blue', 'hex' => '#0000FF'],
            ['name' => 'Green', 'hex' => '#00FF00'],
            ['name' => 'Black', 'hex' => '#000000'],
            ['name' => 'White', 'hex' => '#FFFFFF'],
        ];
        $colorValues = [];

        foreach ($colors as $color) {
            $colorValue = AttributeValue::firstOrCreate([
                'attribute_id' => $colorAttribute->id,
                'value' => $color['name']
            ], [
                'code' => strtolower($color['name']),
                'hex_color' => $color['hex'],
                'is_default' => $color['name'] === 'Black'
            ]);
            $colorValues[] = $colorValue;
            $this->command->info("Created color: {$color['name']} (ID: {$colorValue->id})");
        }

        // Get some existing products and create variations for them
        $products = Product::take(5)->get();
        $this->command->info("Found " . $products->count() . " products to add variations to");

        foreach ($products as $product) {
            $this->command->info("Adding variations to product: {$product->name}");
            
            // Create 2-3 variations per product
            for ($i = 0; $i < 3; $i++) {
                $randomSize = $sizeValues[array_rand($sizeValues)];
                $randomColor = $colorValues[array_rand($colorValues)];
                
                // Check if variation already exists
                $existingVariation = ProductVariation::where('product_id', $product->id)
                    ->where('attribute_value_ids', json_encode([$randomSize->id, $randomColor->id]))
                    ->first();
                
                if (!$existingVariation) {
                    $variation = ProductVariation::create([
                        'product_id' => $product->id,
                        'sku' => $product->id . '-' . $randomSize->code . '-' . $randomColor->code . '-' . $i,
                        'price' => $product->price + ($i * 100), // Slight price variation
                        'min_qty' => 1,
                        'attribute_value_ids' => [$randomSize->id, $randomColor->id]
                    ]);
                    
                    $this->command->info("  Created variation: Size {$randomSize->value}, Color {$randomColor->value} (ID: {$variation->id})");
                } else {
                    $this->command->info("  Variation already exists: Size {$randomSize->value}, Color {$randomColor->value}");
                }
            }
        }

        $this->command->info("Sample variation data created successfully!");
        $this->command->info("Attributes created: Size, Color");
        $this->command->info("Size values: " . implode(', ', $sizes));
        $this->command->info("Color values: " . implode(', ', array_column($colors, 'name')));
    }
}