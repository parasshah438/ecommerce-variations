<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::withCount('products')->latest()->paginate(15);
        return view('admin.sales.index', compact('sales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        $products = Product::with(['images', 'variations.images', 'category'])->get();
        
        return view('admin.sales.create', compact('categories', 'brands', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type' => 'required|in:percentage,fixed,bogo',
            'discount_value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'applicable_categories' => 'nullable|array',
            'applicable_brands' => 'nullable|array',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
            'usage_limit' => 'nullable|integer|min:1',
        ]);

        $validated['slug'] = Str::slug($validated['name'] . '-' . time());

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $request->file('banner_image')->store('sales', 'public');
        }

        $sale = Sale::create($validated);

        if ($request->products) {
            $sale->products()->attach($request->products);
        }

        return redirect()->route('admin.sales.index')
                        ->with('success', 'Sale created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        $sale->load('products');
        return view('admin.sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        $categories = Category::all();
        $brands = Brand::all();
        // Don't load all products anymore - we'll use AJAX search
        $sale->load('products.images', 'products.variationImages', 'products.category');
        
        return view('admin.sales.edit', compact('sale', 'categories', 'brands'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type' => 'required|in:percentage,fixed,bogo',
            'discount_value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'applicable_categories' => 'nullable|array',
            'applicable_brands' => 'nullable|array',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
            'custom_discounts' => 'nullable|array',
            'custom_discounts.*' => 'nullable|numeric|min:0|max:100',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            // Delete old image
            if ($sale->banner_image) {
                Storage::disk('public')->delete($sale->banner_image);
            }
            $validated['banner_image'] = $request->file('banner_image')->store('sales', 'public');
        }

        $sale->update($validated);

        // Update product relationships with custom discounts
        if (!empty($validated['products'])) {
            $syncData = [];
            $customDiscounts = $request->input('custom_discounts', []);
            
            foreach ($validated['products'] as $productId) {
                $syncData[$productId] = [
                    'custom_discount' => isset($customDiscounts[$productId]) && $customDiscounts[$productId] !== '' 
                        ? $customDiscounts[$productId] 
                        : null
                ];
            }
            
            $sale->products()->sync($syncData);
        } else {
            $sale->products()->sync([]);
        }

        return redirect()->route('admin.sales.index')->with('success', 'Sale updated successfully!');
    }

    /**
     * Search products for AJAX requests
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['products' => []]);
        }
        
        $products = Product::with(['images', 'category', 'variationImages'])
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhereHas('category', function($categoryQuery) use ($query) {
                      $categoryQuery->where('name', 'LIKE', "%{$query}%");
                  })
                  ->orWhereHas('variations', function($variationQuery) use ($query) {
                      $variationQuery->where('sku', 'LIKE', "%{$query}%");
                  });
            })
            ->withCount('variations')
            ->limit(20)
            ->get()
            ->map(function($product) {
                $thumbnailImage = $product->getThumbnailImage();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'category' => $product->category->name ?? null,
                    'thumbnail' => $thumbnailImage ? $thumbnailImage->image_path : null,
                    'variations_count' => $product->variations_count
                ];
            });
        
        return response()->json(['products' => $products]);
    }

    /**
     * Get single product for AJAX requests
     */
    public function getProduct(Request $request)
    {
        $productId = $request->get('id');
        
        $product = Product::with(['images', 'category', 'variationImages'])
            ->withCount('variations')
            ->findOrFail($productId);
        
        $thumbnailImage = $product->getThumbnailImage();
        
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'category' => $product->category->name ?? null,
            'thumbnail' => $thumbnailImage ? $thumbnailImage->image_path : null,
            'variations_count' => $product->variations_count
        ]);
    }

    /**
     * Get products by category for bulk addition
     */
    public function productsByCategory(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'custom_discount' => 'nullable|numeric|min:0|max:100',
            'exclude_ids' => 'nullable|array',
            'exclude_ids.*' => 'integer'
        ]);
        
        $categoryIds = $request->categories;
        $excludeIds = $request->exclude_ids ?? [];
        
        $products = Product::with(['images', 'category', 'variationImages'])
            ->whereIn('category_id', $categoryIds)
            ->whereNotIn('id', $excludeIds)
            ->withCount('variations')
            ->limit(50) // Limit to prevent overwhelming the UI
            ->get()
            ->map(function($product) {
                $thumbnailImage = $product->getThumbnailImage();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'category' => $product->category->name ?? null,
                    'thumbnail' => $thumbnailImage ? $thumbnailImage->image_path : null,
                    'variations_count' => $product->variations_count
                ];
            });
        
        return response()->json([
            'success' => true,
            'products' => $products,
            'count' => $products->count()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        // Delete banner image
        if ($sale->banner_image) {
            Storage::disk('public')->delete($sale->banner_image);
        }

        $sale->delete();

        return redirect()->route('admin.sales.index')
                        ->with('success', 'Sale deleted successfully!');
    }

    /**
     * Toggle sale status
     */
    public function toggleStatus(Sale $sale)
    {
        $sale->update(['is_active' => !$sale->is_active]);
        
        return response()->json([
            'success' => true,
            'message' => 'Sale status updated successfully!',
            'is_active' => $sale->is_active
        ]);
    }
}
