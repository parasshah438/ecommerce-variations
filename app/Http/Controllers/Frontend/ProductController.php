<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['images', 'variations', 'category', 'brand']);
        
        // Search filter
        if ($request->has('q') && $request->q) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }
        
        // Category filter
        if ($request->has('categories') && is_array($request->categories)) {
            $query->whereIn('category_id', $request->categories);
        }
        
        // Brand filter
        if ($request->has('brands') && is_array($request->brands)) {
            $query->whereIn('brand_id', $request->brands);
        }
        
        // Price range filter
        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->has('max_price') && $request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // Sort options
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $sortOrder);
                break;
        }
        
        $products = $query->paginate(12);
        
        // Add price range calculation for each product
        $products->getCollection()->transform(function ($product) {
            if ($product->variations->count() > 0) {
                $prices = $product->variations->pluck('price')->filter();
                if ($prices->count() > 0) {
                    $product->min_price = $prices->min();
                    $product->max_price = $prices->max();
                    $product->has_variations = true;
                } else {
                    $product->min_price = $product->price;
                    $product->max_price = $product->price;
                    $product->has_variations = false;
                }
            } else {
                // Simple product (no variations)
                $product->min_price = $product->price;
                $product->max_price = $product->price;
                $product->has_variations = false;
            }
            return $product;
        });
        
        // Get available categories and brands for filters
        $categories = \App\Models\Category::has('products')->get();
        $brands = \App\Models\Brand::has('products')->get();
        
        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'html' => view('products._list', compact('products'))->render(),
                'has_more' => $products->hasMorePages(),
                'current_page' => $products->currentPage(),
                'total' => $products->total()
            ]);
        }
        
        return view('products.index', compact('products', 'categories', 'brands'));
    }

    public function show($slug)
    {
        $product = Product::with(['images', 'variations.stock'])->where('slug', $slug)->firstOrFail();

        // Prepare JSON-friendly variations
        $variations = $product->variations->map(function ($v) {
            return [
                'id' => $v->id,
                'sku' => $v->sku,
                'price' => (float)$v->price,
                'values' => $v->attribute_value_ids,
                'in_stock' => optional($v->stock)->quantity > 0,
                'quantity' => optional($v->stock)->quantity ?? 0,
            ];
        })->values();

        // Variation images (product_variation_images) grouped by variation id, with full asset URLs
        $rawVariationImages = \App\Models\ProductVariationImage::where('product_id', $product->id)->get();
        $variationImages = $rawVariationImages->groupBy('product_variation_id')->map(function ($group) {
            return $group->map(function ($img) {
                return [
                    'id' => $img->id,
                    'path' => \Illuminate\Support\Facades\Storage::url($img->path),
                    'position' => $img->position,
                    'alt' => $img->alt
                ];
            })->values();
        })->toArray();

        // Product-level images with asset URLs
        $productImages = $product->images->map(function ($i) {
            return [
                'id' => $i->id, 
                'path' => \Illuminate\Support\Facades\Storage::url($i->path), 
                'position' => $i->position,
                'alt' => $i->alt
            ];
        })->values();

        // Prepare attribute groups (attribute -> options) used by this product's variations
        $allValueIds = collect($variations)->flatMap(function ($v) { return $v['values']; })->unique()->values()->all();
        $attributeGroups = [];
        if (!empty($allValueIds)) {
            $values = \App\Models\AttributeValue::whereIn('id', $allValueIds)->with('attribute')->get();
            foreach ($values as $val) {
                $attrName = $val->attribute->name ?? 'Other';
                if (!isset($attributeGroups[$attrName])) {
                    $attributeGroups[$attrName] = [];
                }
                $attributeGroups[$attrName][] = [
                    'id' => $val->id,
                    'value' => $val->value,
                    'attribute_id' => $val->attribute_id,
                ];
            }
        }

        // Debug logging
        \Log::info('Product variations debug', [
            'product_id' => $product->id,
            'variations_count' => count($variations),
            'attribute_groups' => array_keys($attributeGroups),
            'all_value_ids' => $allValueIds
        ]);

        return view('products.show', compact('product', 'variations', 'variationImages', 'productImages', 'attributeGroups'));
    }

    public function loadMore(Request $request)
    {
        $products = Product::with(['images', 'variations'])->paginate(12);
        
        // Add price range calculation for each product
        $products->getCollection()->transform(function ($product) {
            if ($product->variations->count() > 0) {
                $prices = $product->variations->pluck('price')->filter();
                if ($prices->count() > 0) {
                    $product->min_price = $prices->min();
                    $product->max_price = $prices->max();
                    $product->has_variations = true;
                } else {
                    $product->min_price = $product->price;
                    $product->max_price = $product->price;
                    $product->has_variations = false;
                }
            } else {
                // Simple product (no variations)
                $product->min_price = $product->price;
                $product->max_price = $product->price;
                $product->has_variations = false;
            }
            return $product;
        });
        
        return view('products._list', compact('products'));
    }
}
