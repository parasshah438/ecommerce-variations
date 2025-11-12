<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;

class UpdateProductDatesForTesting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:update-product-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update product dates for testing new arrivals functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating product dates for testing...');
        
        // Get total products
        $totalProducts = Product::count();
        $this->info("Total products in database: {$totalProducts}");
        
        if ($totalProducts === 0) {
            $this->error('No products found in database. Please create some products first.');
            return 1;
        }
        
        // Update some products to have recent dates (last 30 days)
        $productsToUpdate = min(10, $totalProducts);
        $products = Product::limit($productsToUpdate)->get();
        
        $updated = 0;
        foreach ($products as $product) {
            $randomDaysAgo = rand(1, 29);
            $product->created_at = now()->subDays($randomDaysAgo);
            $product->save();
            $updated++;
        }
        
        $this->info("Updated {$updated} products with recent created_at dates");
        
        // Ensure we have categories and brands
        $this->ensureCategoriesAndBrands();
        
        // Update products to have category and brand associations
        $this->updateProductAssociations();
        
        // Show final statistics
        $newArrivals = Product::where('created_at', '>=', now()->subDays(30))->count();
        $categoriesWithProducts = Category::withCount(['products' => function($query) {
            $query->where('created_at', '>=', now()->subDays(30));
        }])->having('products_count', '>', 0)->count();
        
        $brandsWithProducts = Brand::withCount(['products' => function($query) {
            $query->where('created_at', '>=', now()->subDays(30));
        }])->having('products_count', '>', 0)->count();
        
        $this->info("Final statistics:");
        $this->info("- New arrivals (last 30 days): {$newArrivals}");
        $this->info("- Categories with new arrivals: {$categoriesWithProducts}");
        $this->info("- Brands with new arrivals: {$brandsWithProducts}");
        
        $this->info('✅ Product dates updated successfully for testing!');
        
        return 0;
    }
    
    private function ensureCategoriesAndBrands()
    {
        // Create sample categories if none exist
        if (Category::count() === 0) {
            Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
            Category::create(['name' => 'Clothing', 'slug' => 'clothing']);
            Category::create(['name' => 'Home & Garden', 'slug' => 'home-garden']);
            $this->info('Created sample categories');
        }
        
        // Create sample brands if none exist
        if (Brand::count() === 0) {
            Brand::create(['name' => 'Sample Brand A', 'slug' => 'sample-brand-a']);
            Brand::create(['name' => 'Sample Brand B', 'slug' => 'sample-brand-b']);
            Brand::create(['name' => 'Sample Brand C', 'slug' => 'sample-brand-c']);
            $this->info('Created sample brands');
        }
    }
    
    private function updateProductAssociations()
    {
        $categoryIds = Category::pluck('id')->toArray();
        $brandIds = Brand::pluck('id')->toArray();
        
        if (empty($categoryIds) || empty($brandIds)) {
            $this->warn('No categories or brands available for associations');
            return;
        }
        
        // Update products without category
        $productsWithoutCategory = Product::whereNull('category_id')->limit(10)->get();
        foreach ($productsWithoutCategory as $product) {
            $product->category_id = $categoryIds[array_rand($categoryIds)];
            $product->save();
        }
        
        // Update products without brand
        $productsWithoutBrand = Product::whereNull('brand_id')->limit(10)->get();
        foreach ($productsWithoutBrand as $product) {
            $product->brand_id = $brandIds[array_rand($brandIds)];
            $product->save();
        }
        
        $this->info('Updated product category and brand associations');
    }
}
