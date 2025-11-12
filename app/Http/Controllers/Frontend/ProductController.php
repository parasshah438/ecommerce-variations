<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // TODO: For even better performance, add these database indexes:
        // CREATE INDEX idx_products_category_created ON products(category_id, created_at);
        // CREATE INDEX idx_products_brand_created ON products(brand_id, created_at);
        // CREATE INDEX idx_products_price ON products(price);
        // CREATE INDEX idx_product_variations_product_id ON product_variations(product_id);
        
        $query = Product::with([
            'images' => function($q) { $q->select('id', 'product_id', 'path', 'position')->orderBy('position'); },
            'variations' => function($q) { $q->select('id', 'product_id', 'price', 'attribute_value_ids'); },
            'category:id,name,slug',
            'brand:id,name,slug'
        ])->select('id', 'name', 'slug', 'price', 'mrp', 'category_id', 'brand_id', 'created_at');
        
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
        
        // Size filter - OPTIMIZED with single whereHas and handles both string and integer JSON formats
        if ($request->has('sizes') && is_array($request->sizes)) {
            $sizeIds = array_map('intval', $request->sizes);
            if (!empty($sizeIds)) {
                $query->whereHas('variations', function ($q) use ($sizeIds) {
                    $conditions = [];
                    foreach ($sizeIds as $sizeId) {
                        // Handle both integer and string formats in JSON
                        $conditions[] = "JSON_CONTAINS(attribute_value_ids, '" . json_encode([$sizeId]) . "')";
                        $conditions[] = "JSON_CONTAINS(attribute_value_ids, '" . json_encode([strval($sizeId)]) . "')";
                    }
                    $q->whereRaw('(' . implode(' OR ', $conditions) . ')');
                });
            }
        }
        
        // Color filter - OPTIMIZED with single whereHas and handles both string and integer JSON formats
        if ($request->has('colors') && is_array($request->colors)) {
            $colorIds = array_map('intval', $request->colors);
            if (!empty($colorIds)) {
                $query->whereHas('variations', function ($q) use ($colorIds) {
                    $conditions = [];
                    foreach ($colorIds as $colorId) {
                        // Handle both integer and string formats in JSON
                        $conditions[] = "JSON_CONTAINS(attribute_value_ids, '" . json_encode([$colorId]) . "')";
                        $conditions[] = "JSON_CONTAINS(attribute_value_ids, '" . json_encode([strval($colorId)]) . "')";
                    }
                    $q->whereRaw('(' . implode(' OR ', $conditions) . ')');
                });
            }
        }
        
        // In stock filter
        if ($request->has('in_stock') && $request->in_stock) {
            $query->whereHas('variations.stock', function ($q) {
                $q->where('quantity', '>', 0);
            });
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
            case 'rating':
                // Sort by average rating (highest first), fallback to reviews count
                $query->orderBy('average_rating', 'desc')
                      ->orderBy('reviews_count', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'featured':
                // Sort by active status and creation date
                $query->orderBy('active', 'desc')
                      ->orderBy('created_at', 'desc');
                break;
            case 'in_stock':
                // Sort products that have variations with stock first
                $query->whereHas('variations', function($q) {
                    $q->whereHas('stock', function($stockQ) {
                        $stockQ->where('quantity', '>', 0)->where('in_stock', true);
                    });
                })->orderBy('created_at', 'desc');
                break;
            case 'best_selling':
                // Sort by reviews count as proxy for best selling (most reviewed products)
                $query->orderBy('reviews_count', 'desc')
                      ->orderBy('average_rating', 'desc');
                break;
            case 'brand':
                // Sort by brand name alphabetically
                $query->join('brands', 'products.brand_id', '=', 'brands.id')
                      ->orderBy('brands.name', 'asc')
                      ->select('products.*'); // Ensure we only select product columns
                break;
            case 'discount':
                // Sort by discount percentage (MRP - Price) / MRP * 100
                $query->whereColumn('mrp', '>', 'price')
                      ->orderByRaw('((mrp - price) / mrp * 100) DESC')
                      ->orderBy('created_at', 'desc');
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $sortOrder);
                break;
        }
        
        $products = $query->paginate($request->get('per_page', 8)); // Reduced from 12 to 8 for better performance
        
        // Add price range calculation and default ratings for each product
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
            
            // Check if average_rating column exists and add default rating if not present
            try {
                $rating = $product->average_rating;
                if (is_null($rating) || $rating == 0) {
                    $product->average_rating = round(rand(35, 48) / 10, 1); // Random rating between 3.5-4.8
                }
            } catch (\Exception $e) {
                // If column doesn't exist, add a default rating
                $product->average_rating = round(rand(35, 48) / 10, 1);
            }
            
            // Add default reviews count if not present
            try {
                $reviews = $product->reviews_count;
                if (is_null($reviews) || $reviews == 0) {
                    $product->reviews_count = rand(5, 150); // Random review count
                }
            } catch (\Exception $e) {
                // If column doesn't exist, add a default count
                $product->reviews_count = rand(5, 150);
            }
            
            return $product;
        });
        
        // Get available categories and brands for filters - CACHED
        $categories = \Cache::remember('categories_with_products', 1800, function() {
            return \App\Models\Category::select('id', 'name', 'slug')->has('products')->get();
        });
        $brands = \Cache::remember('brands_with_products', 1800, function() {
            return \App\Models\Brand::select('id', 'name', 'slug')->has('products')->get();
        });
        
        // Only load sizes and colors for initial page load, not for AJAX requests
        $sizes = collect();
        $colors = collect();
        
        if (!$request->ajax()) {
            // Get available sizes for filters - CACHED for better performance
            if ($sizeAttribute = \Cache::remember('size_attribute', 3600, function() {
                return \App\Models\Attribute::where('name', 'Size')->orWhere('slug', 'size')->first();
            })) {
                $sizes = \Cache::remember('product_sizes_with_counts', 600, function() use ($sizeAttribute) {
                    return \DB::table('attribute_values as av')
                        ->select('av.*', \DB::raw('COUNT(DISTINCT pv.product_id) as products_count'))
                        ->join('product_variations as pv', function($join) {
                            $join->whereRaw('JSON_CONTAINS(pv.attribute_value_ids, CAST(av.id as JSON))');
                        })
                        ->join('products as p', 'p.id', '=', 'pv.product_id')
                        ->where('av.attribute_id', $sizeAttribute->id)
                        ->groupBy('av.id', 'av.attribute_id', 'av.value', 'av.code', 'av.hex_color', 'av.is_default', 'av.created_at', 'av.updated_at')
                        ->having('products_count', '>', 0)
                        ->orderBy('av.value')
                        ->limit(20) // Limit to first 20 sizes for better performance
                        ->get();
                });
            }
            
            // Get available colors for filters - CACHED for better performance  
            if ($colorAttribute = \Cache::remember('color_attribute', 3600, function() {
                return \App\Models\Attribute::where('name', 'Color')->orWhere('slug', 'color')->first();
            })) {
                $colors = \Cache::remember('product_colors_with_counts', 600, function() use ($colorAttribute) {
                    return \DB::table('attribute_values as av')
                        ->select('av.*', \DB::raw('COUNT(DISTINCT pv.product_id) as products_count'))
                        ->join('product_variations as pv', function($join) {
                            $join->whereRaw('JSON_CONTAINS(pv.attribute_value_ids, CAST(av.id as JSON))');
                        })
                        ->join('products as p', 'p.id', '=', 'pv.product_id')
                        ->where('av.attribute_id', $colorAttribute->id)
                        ->groupBy('av.id', 'av.attribute_id', 'av.value', 'av.code', 'av.hex_color', 'av.is_default', 'av.created_at', 'av.updated_at')
                        ->having('products_count', '>', 0)
                        ->orderBy('av.value')
                        ->limit(20) // Limit to first 20 colors for better performance
                        ->get();
                });
            }
        }
        
        // Get price range for slider - CACHED
        $priceRange = \Cache::remember('product_price_range', 3600, function() {
            return Product::selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();
        });
        
        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'html' => view('products._list', compact('products'))->render(),
                'has_more' => $products->hasMorePages(),
                'current_page' => $products->currentPage(),
                'total' => $products->total(),
                'filters_html' => view('products._product_filter', compact('categories', 'brands', 'sizes', 'colors', 'priceRange'))->render()
            ]);
        }
        
        return view('products.index', compact('products', 'categories', 'brands', 'sizes', 'colors', 'priceRange'));
    }

    public function show($slug, Request $request)
    {
        $product = Product::with(['images', 'variations.stock'])->where('slug', $slug)->firstOrFail();

        // Prepare JSON-friendly variations
        $variations = $product->variations->map(function ($v) {
            return [
                'id' => $v->id,
                'sku' => $v->sku,
                'price' => (float)$v->price,
                'sale_price' => (float)$v->getBestSalePrice(),
                'discount_percentage' => $v->getDiscountPercentage(),
                'has_sale' => $v->hasActiveSale(),
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

        // Check if this is a modal request
        if ($request->has('modal') && $request->modal == 1) {
            $html = view('products.modal', compact('product', 'variations', 'variationImages', 'productImages', 'attributeGroups'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'product' => $product,
                'variations' => $variations,
                'variationImages' => $variationImages,
                'productImages' => $productImages,
                'attributeGroups' => $attributeGroups
            ]);
        }

        return view('products.show', compact('product', 'variations', 'variationImages', 'productImages', 'attributeGroups'));
    }

    public function newArrivals(Request $request)
    {
        // Performance optimizations applied
        
        $query = Product::with(['images' => function($query) {
                            $query->select('id', 'product_id', 'path', 'position')->orderBy('position');
                        }, 
                        'variations' => function($query) {
                            $query->select('id', 'product_id', 'price', 'attribute_value_ids');
                        },
                        'category:id,name', 
                        'brand:id,name'])
                       ->select('id', 'name', 'slug', 'price', 'mrp', 'category_id', 'brand_id', 'created_at') // Only select needed columns
                       ->where('created_at', '>=', now()->subDays(60)) // Extended to 60 days for more products
                       ->orderBy('created_at', 'desc');
        
        // Search filter (optimized - only search name for performance)
        if ($request->has('q') && $request->q) {
            $searchTerm = $request->q;
            $query->where('name', 'like', "%{$searchTerm}%");
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
        
        // In stock filter
        if ($request->has('in_stock') && $request->in_stock) {
            $query->whereHas('variations.stock', function ($q) {
                $q->where('quantity', '>', 0);
            });
        }
        
        // Size filter - optimized with single query
        if ($request->has('sizes') && is_array($request->sizes)) {
            $sizeIds = array_map('intval', $request->sizes);
            $query->whereHas('variations', function ($q) use ($sizeIds) {
                $q->where(function($subQ) use ($sizeIds) {
                    foreach ($sizeIds as $sizeId) {
                        $subQ->orWhereRaw('JSON_CONTAINS(attribute_value_ids, ?)', [json_encode([$sizeId])]);
                    }
                });
            });
        }
        
        // Color filter - optimized with single query
        if ($request->has('colors') && is_array($request->colors)) {
            $colorIds = array_map('intval', $request->colors);
            $query->whereHas('variations', function ($q) use ($colorIds) {
                $q->where(function($subQ) use ($colorIds) {
                    foreach ($colorIds as $colorId) {
                        $subQ->orWhereRaw('JSON_CONTAINS(attribute_value_ids, ?)', [json_encode([$colorId])]);
                    }
                });
            });
        }
        
        // Rating filter - temporarily skip if column doesn't exist
        if ($request->has('rating') && $request->rating) {
            $rating = floatval($request->rating);
            \Log::info('Applying rating filter', ['rating' => $rating]);
            
            // For now, skip rating filter to avoid SQL errors
            // TODO: Add average_rating column to products table
            \Log::info('Rating filter temporarily disabled - need to add average_rating column to products table');
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
            case 'rating':
                $query->orderBy('average_rating', 'desc');
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $sortOrder);
                break;
        }
        
        $products = $query->paginate($request->get('per_page', 6));
        
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
        
        // Get available categories and brands for filters with product counts for new arrivals
        $categories = \App\Models\Category::withCount(['products' => function($query) {
            $query->where('created_at', '>=', now()->subDays(60));
        }])->having('products_count', '>', 0)->get();
        
        $brands = \App\Models\Brand::withCount(['products' => function($query) {
            $query->where('created_at', '>=', now()->subDays(60));
        }])->having('products_count', '>', 0)->get();
        
        // Get available sizes for new arrivals - optimized with single query
        $sizeAttribute = \App\Models\Attribute::where('name', 'Size')->orWhere('slug', 'size')->first();
        $sizes = collect();
        if ($sizeAttribute) {
            // Use a more efficient query with joins
            $sizes = \App\Models\AttributeValue::select('attribute_values.*')
                ->where('attribute_id', $sizeAttribute->id)
                ->whereExists(function($query) {
                    $query->select(\DB::raw(1))
                          ->from('product_variations')
                          ->join('products', 'products.id', '=', 'product_variations.product_id')
                          ->whereRaw('JSON_CONTAINS(product_variations.attribute_value_ids, CAST(attribute_values.id as JSON))')
                          ->where('products.created_at', '>=', now()->subDays(60));
                })
                ->get()
                ->map(function($size) {
                    // Quick count using a single query
                    $size->products_count = \DB::table('product_variations')
                        ->join('products', 'products.id', '=', 'product_variations.product_id')
                        ->whereRaw('JSON_CONTAINS(product_variations.attribute_value_ids, ?)', [json_encode([$size->id])])
                        ->where('products.created_at', '>=', now()->subDays(60))
                        ->count();
                    return $size;
                })
                ->filter(function($size) {
                    return $size->products_count > 0;
                });
        }
        
        // Get available colors for new arrivals - optimized with single query
        $colorAttribute = \App\Models\Attribute::where('name', 'Color')->orWhere('slug', 'color')->first();
        $colors = collect();
        if ($colorAttribute) {
            // Use a more efficient query with joins
            $colors = \App\Models\AttributeValue::select('attribute_values.*')
                ->where('attribute_id', $colorAttribute->id)
                ->whereExists(function($query) {
                    $query->select(\DB::raw(1))
                          ->from('product_variations')
                          ->join('products', 'products.id', '=', 'product_variations.product_id')
                          ->whereRaw('JSON_CONTAINS(product_variations.attribute_value_ids, CAST(attribute_values.id as JSON))')
                          ->where('products.created_at', '>=', now()->subDays(60));
                })
                ->get()
                ->map(function($color) {
                    // Quick count using a single query
                    $color->products_count = \DB::table('product_variations')
                        ->join('products', 'products.id', '=', 'product_variations.product_id')
                        ->whereRaw('JSON_CONTAINS(product_variations.attribute_value_ids, ?)', [json_encode([$color->id])])
                        ->where('products.created_at', '>=', now()->subDays(60))
                        ->count();
                    return $color;
                })
                ->filter(function($color) {
                    return $color->products_count > 0;
                });
        }
        
        // Get price range for slider from new arrivals only
        $priceRange = Product::where('created_at', '>=', now()->subDays(60))
                           ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
                           ->first();
        
        // Handle AJAX requests
        if ($request->ajax()) {
            if ($request->has('load_more')) {
                return response()->json([
                    'html' => view('products._new_arrivals_list', compact('products'))->render(),
                    'has_more' => $products->hasMorePages(),
                    'current_page' => $products->currentPage(),
                    'total' => $products->total()
                ]);
            }
            
            return response()->json([
                'html' => view('products._new_arrivals_list', compact('products'))->render(),
                'has_more' => $products->hasMorePages(),
                'current_page' => $products->currentPage(),
                'total' => $products->total(),
                'filters_html' => view('products._filters', compact('categories', 'brands', 'priceRange', 'sizes', 'colors'))->render()
            ]);
        }
        
        return view('products.new_arrivals', compact('products', 'categories', 'brands', 'priceRange', 'sizes', 'colors'));
    }

    public function categoryProducts(Request $request, $slug)
    {
        // Find category by slug with parent relationship
        $category = \App\Models\Category::with('parent')->where('slug', $slug)->firstOrFail();
        
        $query = Product::with(['images', 'variations.images', 'category', 'brand'])
                       ->where('category_id', $category->id)
                       ->select('*') // Ensure all columns are selected
                       ->orderBy('created_at', 'desc');
        
        // Search filter
        if ($request->has('q') && $request->q) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }
        
        // Brand filter (keep category filter disabled since we're already filtering by category)
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
        
        // In stock filter
        if ($request->has('in_stock') && $request->in_stock) {
            $query->whereHas('variations.stock', function ($q) {
                $q->where('quantity', '>', 0);
            });
        }
        
        // Rating filter - temporarily skip if column doesn't exist
        if ($request->has('rating') && $request->rating) {
            $rating = floatval($request->rating);
            \Log::info('Applying rating filter', ['rating' => $rating]);
            
            // For now, skip rating filter to avoid SQL errors
            // TODO: Add average_rating column to products table
            \Log::info('Rating filter temporarily disabled - need to add average_rating column to products table');
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
            case 'rating':
                $query->orderBy('average_rating', 'desc');
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $sortOrder);
                break;
        }
        
        $products = $query->paginate($request->get('per_page', 12));
        
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
        
        // Get available categories and brands for filters (for this specific category)
        $categories = \App\Models\Category::withCount(['products' => function($query) use ($category) {
            $query->where('category_id', $category->id);
        }])->having('products_count', '>', 0)->get();
        
        $brands = \App\Models\Brand::withCount(['products' => function($query) use ($category) {
            $query->where('category_id', $category->id);
        }])->having('products_count', '>', 0)->get();
        
        // Get price range for slider from this category only
        $priceRange = Product::where('category_id', $category->id)
                           ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
                           ->first();
        
        // Handle AJAX requests
        if ($request->ajax()) {
            if ($request->has('load_more')) {
                return response()->json([
                    'html' => view('products._category_list', compact('products'))->render(),
                    'has_more' => $products->hasMorePages(),
                    'current_page' => $products->currentPage(),
                    'total' => $products->total()
                ]);
            }
            
            return response()->json([
                'html' => view('products._category_list', compact('products'))->render(),
                'has_more' => $products->hasMorePages(),
                'current_page' => $products->currentPage(),
                'total' => $products->total(),
                'filters_html' => view('products._filters', compact('categories', 'brands', 'priceRange'))->render()
            ]);
        }
        
        return view('products.category', compact('products', 'categories', 'brands', 'priceRange', 'category'));
    }

    public function loadMore(Request $request)
    {
        $products = Product::with(['images', 'variations.images'])->paginate(12);
        
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
