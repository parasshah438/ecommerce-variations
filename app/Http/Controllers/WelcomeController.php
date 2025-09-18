<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class WelcomeController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function getFeaturedProducts(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 6);
        
        $products = Product::with(['images', 'variations', 'brand', 'category'])
            ->where('active', true)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Transform products for frontend
        $transformedProducts = $products->getCollection()->map(function ($product) {
            // Calculate price range for products with variations
            if ($product->variations->count() > 0) {
                $prices = $product->variations->pluck('price')->filter();
                $minPrice = $prices->count() > 0 ? $prices->min() : $product->price;
                $maxPrice = $prices->count() > 0 ? $prices->max() : $product->price;
            } else {
                $minPrice = $product->price;
                $maxPrice = $product->price;
            }

            // Get first image
            $image = $product->images->first();
            $imageUrl = $image ? asset('storage/' . $image->path) : 'https://via.placeholder.com/400x300?text=No+Image';

            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $minPrice,
                'original_price' => $product->mrp,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'has_variations' => $product->variations->count() > 0,
                'rating' => 4.5, // You can implement real ratings later
                'reviews' => rand(50, 500), // Sample data
                'image' => $imageUrl,
                'category' => $product->category ? $product->category->name : 'Uncategorized',
                'brand' => $product->brand ? $product->brand->name : null,
                'in_stock' => $this->checkProductStock($product),
            ];
        });

        return response()->json([
            'products' => $transformedProducts,
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'has_more' => $products->hasMorePages(),
            'total' => $products->total(),
        ]);
    }

    private function checkProductStock($product)
    {
        if ($product->variations->count() > 0) {
            // Check if any variation has stock
            foreach ($product->variations as $variation) {
                if ($variation->stock && $variation->stock->quantity > 0) {
                    return true;
                }
            }
            return false;
        }
        
        // For simple products, assume in stock (you can add stock management for simple products)
        return true;
    }
}
