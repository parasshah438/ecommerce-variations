<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductWeightSeeder extends Seeder
{
    /**
     * Seed default weights for existing products based on categories
     */
    public function run(): void
    {
        // Default weights by category name (in grams)
        $categoryWeights = [
            't-shirt' => 200,
            'tshirt' => 200,
            'shirt' => 300,
            'jeans' => 600,
            'pants' => 500,
            'dress' => 400,
            'jacket' => 800,
            'blazer' => 700,
            'coat' => 1000,
            'shoes' => 500,
            'sneakers' => 400,
            'boots' => 700,
            'sandals' => 300,
            'cap' => 100,
            'hat' => 100,
            'socks' => 50,
            'underwear' => 80,
            'bra' => 100,
            'scarf' => 120,
            'belt' => 200,
            'watch' => 150,
            'jewelry' => 50,
            'bag' => 400,
            'backpack' => 600,
            'wallet' => 150,
        ];

        // Get all products with their categories
        $products = DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.id', 'products.name', 'categories.name as category_name')
            ->whereNull('products.weight') // Only update products without weight
            ->get();

        foreach ($products as $product) {
            $weight = $this->determineProductWeight($product, $categoryWeights);
            
            DB::table('products')
                ->where('id', $product->id)
                ->update(['weight' => $weight]);
        }

        $this->command->info('Updated weights for ' . $products->count() . ' products');
    }

    /**
     * Determine product weight based on name and category
     */
    private function determineProductWeight($product, $categoryWeights): int
    {
        $productName = strtolower($product->name);
        $categoryName = strtolower($product->category_name ?? '');

        // Check category first
        foreach ($categoryWeights as $keyword => $weight) {
            if (strpos($categoryName, $keyword) !== false) {
                return $weight;
            }
        }

        // Check product name
        foreach ($categoryWeights as $keyword => $weight) {
            if (strpos($productName, $keyword) !== false) {
                return $weight;
            }
        }

        // Size-based weight adjustment
        $sizeMultiplier = 1;
        if (preg_match('/(xs|extra small)/i', $productName)) {
            $sizeMultiplier = 0.8;
        } elseif (preg_match('/(small|s\b)/i', $productName)) {
            $sizeMultiplier = 0.9;
        } elseif (preg_match('/(large|l\b)/i', $productName)) {
            $sizeMultiplier = 1.2;
        } elseif (preg_match('/(xl|extra large|xxl)/i', $productName)) {
            $sizeMultiplier = 1.4;
        }

        // Default weight for clothing
        return (int) (200 * $sizeMultiplier);
    }
}