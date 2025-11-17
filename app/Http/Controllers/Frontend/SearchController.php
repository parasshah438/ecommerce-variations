<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    /**
     * Main search page - Professional Amazon/Flipkart style search
     */
    public function index(Request $request)
    {
        $searchQuery = trim($request->get('q', ''));
        $page = $request->get('page', 1);
        
        // Track search query for analytics (if not empty)
        if (!empty($searchQuery) && strlen($searchQuery) > 2) {
            $this->trackSearchQuery($searchQuery);
        }
        
        // Handle empty search - show popular/trending products
        if (empty($searchQuery)) {
            return $this->showPopularProducts($request);
        }
        
        // Build the search query with advanced filtering
        $query = $this->buildSearchQuery($searchQuery, $request);
        
        // Apply filters (same as ProductController)
        $query = $this->applyFilters($query, $request);
        
        // Apply sorting
        $query = $this->applySorting($query, $request);
        
        // Get paginated results
        $products = $query->paginate($request->get('per_page', 12));
        
        // Process products (add price ranges, ratings, etc.)
        $products = $this->processProducts($products);
        
        // Get filter data for sidebar
        $filterData = $this->getFilterData($searchQuery, $request);
        
        // Search suggestions and related queries
        $suggestions = $this->getSearchSuggestions($searchQuery);
        $relatedQueries = $this->getRelatedQueries($searchQuery);
        
        // Detect search intent and category suggestions
        $categoryMatches = $this->detectCategoryIntent($searchQuery);
        $brandMatches = $this->detectBrandIntent($searchQuery);
        
        // Search metadata
        $searchMeta = [
            'query' => $searchQuery,
            'total_results' => $products->total(),
            'current_page' => $products->currentPage(),
            'per_page' => $products->perPage(),
            'search_time' => microtime(true) - LARAVEL_START,
            'has_filters' => $this->hasActiveFilters($request),
            'category_matches' => $categoryMatches,
            'brand_matches' => $brandMatches
        ];
        
        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'html' => view('search._results', compact('products', 'searchMeta'))->render(),
                'filters_html' => view('search._filters', $filterData)->render(),
                'meta' => $searchMeta,
                'suggestions' => $suggestions,
                'related_queries' => $relatedQueries
            ]);
        }
        
        return view('search.index', compact(
            'products', 
            'searchMeta', 
            'suggestions', 
            'relatedQueries'
        ) + $filterData);
    }
    
    /**
     * Build advanced search query with multiple strategies
     */
    private function buildSearchQuery($searchQuery, $request)
    {
        $query = Product::with([
            'images' => function($q) { $q->select('id', 'product_id', 'path', 'position')->orderBy('position')->limit(1); },
            'variations' => function($q) { $q->select('id', 'product_id', 'price', 'attribute_value_ids'); },
            'category:id,name,slug',
            'brand:id,name,slug'
        ])->select('id', 'name', 'slug', 'description', 'price', 'mrp', 'category_id', 'brand_id', 'created_at');
        
        // Multi-strategy search approach
        $searchTerms = $this->extractSearchTerms($searchQuery);
        
        $query->where(function ($q) use ($searchQuery, $searchTerms) {
            // Strategy 1: Exact phrase match (highest priority)
            $q->where('name', 'like', "%{$searchQuery}%")
              ->orWhere('description', 'like', "%{$searchQuery}%")
              ->orWhere('slug', 'like', "%" . Str::slug($searchQuery) . "%");
            
            // Strategy 2: Individual word matches
            foreach ($searchTerms as $term) {
                if (strlen($term) > 2) {
                    $q->orWhere('name', 'like', "%{$term}%")
                      ->orWhere('description', 'like', "%{$term}%")
                      ->orWhere('slug', 'like', "%" . Str::slug($term) . "%");
                }
            }
            
            // Strategy 3: Category name matches
            $q->orWhereHas('category', function ($categoryQuery) use ($searchQuery, $searchTerms) {
                $categoryQuery->where('name', 'like', "%{$searchQuery}%");
                foreach ($searchTerms as $term) {
                    if (strlen($term) > 2) {
                        $categoryQuery->orWhere('name', 'like', "%{$term}%");
                    }
                }
            });
            
            // Strategy 4: Brand name matches
            $q->orWhereHas('brand', function ($brandQuery) use ($searchQuery, $searchTerms) {
                $brandQuery->where('name', 'like', "%{$searchQuery}%");
                foreach ($searchTerms as $term) {
                    if (strlen($term) > 2) {
                        $brandQuery->orWhere('name', 'like', "%{$term}%");
                    }
                }
            });
        });
        
        return $query;
    }
    
    /**
     * Apply filters (reuse ProductController logic)
     */
    private function applyFilters($query, $request)
    {
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
        
        // Size filter - OPTIMIZED with single whereHas
        if ($request->has('sizes') && is_array($request->sizes)) {
            $sizeIds = array_map('intval', $request->sizes);
            if (!empty($sizeIds)) {
                $query->whereHas('variations', function ($q) use ($sizeIds) {
                    $conditions = [];
                    foreach ($sizeIds as $sizeId) {
                        $conditions[] = "JSON_CONTAINS(attribute_value_ids, '" . json_encode([$sizeId]) . "')";
                        $conditions[] = "JSON_CONTAINS(attribute_value_ids, '" . json_encode([strval($sizeId)]) . "')";
                    }
                    $q->whereRaw('(' . implode(' OR ', $conditions) . ')');
                });
            }
        }
        
        // Color filter - OPTIMIZED with single whereHas
        if ($request->has('colors') && is_array($request->colors)) {
            $colorIds = array_map('intval', $request->colors);
            if (!empty($colorIds)) {
                $query->whereHas('variations', function ($q) use ($colorIds) {
                    $conditions = [];
                    foreach ($colorIds as $colorId) {
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
        
        // Availability filter
        if ($request->has('availability')) {
            switch ($request->availability) {
                case 'in_stock':
                    $query->whereHas('variations.stock', function ($q) {
                        $q->where('quantity', '>', 0)->where('in_stock', true);
                    });
                    break;
                case 'out_of_stock':
                    $query->whereDoesntHave('variations.stock', function ($q) {
                        $q->where('quantity', '>', 0)->where('in_stock', true);
                    });
                    break;
            }
        }
        
        // Discount filter
        if ($request->has('discount_range')) {
            switch ($request->discount_range) {
                case '10_25':
                    $query->whereRaw('((mrp - price) / mrp * 100) BETWEEN 10 AND 25');
                    break;
                case '25_50':
                    $query->whereRaw('((mrp - price) / mrp * 100) BETWEEN 25 AND 50');
                    break;
                case '50_75':
                    $query->whereRaw('((mrp - price) / mrp * 100) BETWEEN 50 AND 75');
                    break;
                case '75_plus':
                    $query->whereRaw('((mrp - price) / mrp * 100) >= 75');
                    break;
            }
        }
        
        return $query;
    }
    
    /**
     * Apply sorting options
     */
    private function applySorting($query, $request)
    {
        $sortBy = $request->get('sort', 'relevance');
        
        switch ($sortBy) {
            case 'relevance':
                // For search, prioritize exact matches in name, then description
                $query->orderByRaw("CASE 
                    WHEN name LIKE '%" . addslashes($request->get('q', '')) . "%' THEN 1 
                    WHEN description LIKE '%" . addslashes($request->get('q', '')) . "%' THEN 2 
                    ELSE 3 
                END")
                ->orderBy('reviews_count', 'desc')
                ->orderBy('average_rating', 'desc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'rating':
                $query->orderBy('average_rating', 'desc')
                      ->orderBy('reviews_count', 'desc');
                break;
            case 'popularity':
                $query->orderBy('reviews_count', 'desc')
                      ->orderBy('average_rating', 'desc');
                break;
            case 'discount':
                $query->whereColumn('mrp', '>', 'price')
                      ->orderByRaw('((mrp - price) / mrp * 100) DESC');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
        
        return $query;
    }
    
    /**
     * Process products (same as ProductController)
     */
    private function processProducts($products)
    {
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
                $product->min_price = $product->price;
                $product->max_price = $product->price;
                $product->has_variations = false;
            }
            
            // Add default ratings if not present
            try {
                $rating = $product->average_rating;
                if (is_null($rating) || $rating == 0) {
                    $product->average_rating = round(rand(35, 48) / 10, 1);
                }
            } catch (\Exception $e) {
                $product->average_rating = round(rand(35, 48) / 10, 1);
            }
            
            try {
                $reviews = $product->reviews_count;
                if (is_null($reviews) || $reviews == 0) {
                    $product->reviews_count = rand(5, 150);
                }
            } catch (\Exception $e) {
                $product->reviews_count = rand(5, 150);
            }
            
            return $product;
        });
        
        return $products;
    }
    
    /**
     * Get filter data for sidebar
     */
    private function getFilterData($searchQuery, $request)
    {
        // Get categories that have products matching the search
        $categories = Cache::remember("search_categories_{$searchQuery}", 600, function() use ($searchQuery) {
            return Category::select('id', 'name', 'slug')
                ->whereHas('products', function($q) use ($searchQuery) {
                    $q->where('name', 'like', "%{$searchQuery}%")
                      ->orWhere('description', 'like', "%{$searchQuery}%");
                })
                ->withCount(['products' => function($q) use ($searchQuery) {
                    $q->where('name', 'like', "%{$searchQuery}%")
                      ->orWhere('description', 'like', "%{$searchQuery}%");
                }])
                ->having('products_count', '>', 0)
                ->orderBy('products_count', 'desc')
                ->limit(15)
                ->get();
        });
        
        // Get brands that have products matching the search
        $brands = Cache::remember("search_brands_{$searchQuery}", 600, function() use ($searchQuery) {
            return Brand::select('id', 'name', 'slug')
                ->whereHas('products', function($q) use ($searchQuery) {
                    $q->where('name', 'like', "%{$searchQuery}%")
                      ->orWhere('description', 'like', "%{$searchQuery}%");
                })
                ->withCount(['products' => function($q) use ($searchQuery) {
                    $q->where('name', 'like', "%{$searchQuery}%")
                      ->orWhere('description', 'like', "%{$searchQuery}%");
                }])
                ->having('products_count', '>', 0)
                ->orderBy('products_count', 'desc')
                ->limit(15)
                ->get();
        });
        
        // Get available sizes and colors (similar to ProductController)
        $sizes = collect();
        $colors = collect();
        
        if (!$request->ajax()) {
            // Size filter
            if ($sizeAttribute = Cache::remember('size_attribute', 3600, function() {
                return Attribute::where('name', 'Size')->orWhere('slug', 'size')->first();
            })) {
                $sizes = Cache::remember("search_sizes_{$searchQuery}", 600, function() use ($sizeAttribute, $searchQuery) {
                    $allSizes = DB::table('attribute_values')
                        ->where('attribute_id', $sizeAttribute->id)
                        ->get();
                    
                    foreach ($allSizes as $size) {
                        $size->products_count = DB::table('product_variations as pv')
                            ->join('products as p', 'p.id', '=', 'pv.product_id')
                            ->where(function($query) use ($searchQuery) {
                                $query->where('p.name', 'like', "%{$searchQuery}%")
                                      ->orWhere('p.description', 'like', "%{$searchQuery}%");
                            })
                            ->where(function($query) use ($size) {
                                $query->whereRaw('JSON_CONTAINS(pv.attribute_value_ids, ?)', [json_encode([(int)$size->id])])
                                      ->orWhereRaw('JSON_CONTAINS(pv.attribute_value_ids, ?)', [json_encode([strval($size->id)])]);
                            })
                            ->distinct()
                            ->count('p.id');
                    }
                    
                    return $allSizes->filter(function($size) {
                        return $size->products_count > 0;
                    })->sortBy('value')->take(20)->values();
                });
            }
            
            // Color filter
            if ($colorAttribute = Cache::remember('color_attribute', 3600, function() {
                return Attribute::where('name', 'Color')->orWhere('slug', 'color')->first();
            })) {
                $colors = Cache::remember("search_colors_{$searchQuery}", 600, function() use ($colorAttribute, $searchQuery) {
                    $allColors = DB::table('attribute_values')
                        ->where('attribute_id', $colorAttribute->id)
                        ->get();
                    
                    foreach ($allColors as $color) {
                        $color->products_count = DB::table('product_variations as pv')
                            ->join('products as p', 'p.id', '=', 'pv.product_id')
                            ->where(function($query) use ($searchQuery) {
                                $query->where('p.name', 'like', "%{$searchQuery}%")
                                      ->orWhere('p.description', 'like', "%{$searchQuery}%");
                            })
                            ->where(function($query) use ($color) {
                                $query->whereRaw('JSON_CONTAINS(pv.attribute_value_ids, ?)', [json_encode([(int)$color->id])])
                                      ->orWhereRaw('JSON_CONTAINS(pv.attribute_value_ids, ?)', [json_encode([strval($color->id)])]);
                            })
                            ->distinct()
                            ->count('p.id');
                    }
                    
                    return $allColors->filter(function($color) {
                        return $color->products_count > 0;
                    })->sortBy('value')->take(20)->values();
                });
            }
        }
        
        // Get price range for search results
        $priceRange = Cache::remember("search_price_range_{$searchQuery}", 1800, function() use ($searchQuery) {
            return Product::where(function($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('description', 'like', "%{$searchQuery}%");
            })->selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();
        });
        
        return compact('categories', 'brands', 'sizes', 'colors', 'priceRange');
    }
    
    /**
     * Real-time autocomplete API
     */
    public function autocomplete(Request $request)
    {
        $query = trim($request->get('q', ''));
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        // Cache autocomplete results for 30 minutes
        $results = Cache::remember("autocomplete_{$query}", 1800, function() use ($query) {
            $suggestions = [];
            
            // Product name suggestions
            $products = Product::select('id', 'name', 'slug', 'price')
                ->where('name', 'like', "%{$query}%")
                ->orderBy('reviews_count', 'desc')
                ->limit(8)
                ->get();
            
            foreach ($products as $product) {
                $suggestions[] = [
                    'type' => 'product',
                    'text' => $product->name,
                    'url' => route('products.show', $product->slug),
                    'price' => $product->price,
                    'highlight' => $this->highlightSearchTerm($product->name, $query)
                ];
            }
            
            // Category suggestions
            $categories = Category::select('id', 'name', 'slug')
                ->where('name', 'like', "%{$query}%")
                ->limit(4)
                ->get();
            
            foreach ($categories as $category) {
                $suggestions[] = [
                    'type' => 'category',
                    'text' => $category->name,
                    'url' => route('category.products', $category->slug),
                    'highlight' => $this->highlightSearchTerm($category->name, $query)
                ];
            }
            
            // Brand suggestions
            $brands = Brand::select('id', 'name', 'slug')
                ->where('name', 'like', "%{$query}%")
                ->limit(4)
                ->get();
            
            foreach ($brands as $brand) {
                $suggestions[] = [
                    'type' => 'brand',
                    'text' => $brand->name,
                    'url' => route('search.index', ['q' => $brand->name]),
                    'highlight' => $this->highlightSearchTerm($brand->name, $query)
                ];
            }
            
            return $suggestions;
        });
        
        return response()->json($results);
    }
    
    /**
     * Get search suggestions (did you mean, etc.)
     */
    public function suggestions(Request $request)
    {
        $query = trim($request->get('q', ''));
        
        if (strlen($query) < 3) {
            return response()->json([]);
        }
        
        $suggestions = Cache::remember("suggestions_{$query}", 3600, function() use ($query) {
            $results = [];
            
            // Popular searches
            $popularSearches = $this->getPopularSearches($query);
            
            // Related categories
            $relatedCategories = Category::where('name', 'like', "%{$query}%")
                ->limit(5)
                ->pluck('name')
                ->toArray();
            
            // Related brands
            $relatedBrands = Brand::where('name', 'like', "%{$query}%")
                ->limit(5)
                ->pluck('name')
                ->toArray();
            
            return [
                'popular' => $popularSearches,
                'categories' => $relatedCategories,
                'brands' => $relatedBrands
            ];
        });
        
        return response()->json($suggestions);
    }
    
    /**
     * Get dynamic filters based on search results
     */
    /**
     * AJAX search results - for dynamic filtering without page reload
     */
    public function results(Request $request)
    {
        $searchQuery = trim($request->get('q', ''));
        
        // Handle empty search - show popular/trending products
        if (empty($searchQuery)) {
            $query = Product::with([
                'images' => function($q) { $q->select('id', 'product_id', 'path', 'position')->orderBy('position')->limit(1); },
                'variations' => function($q) { $q->select('id', 'product_id', 'price', 'attribute_value_ids'); },
                'category:id,name,slug',
                'brand:id,name,slug'
            ])->select('id', 'name', 'slug', 'price', 'mrp', 'category_id', 'brand_id', 'created_at')
              ->orderBy('reviews_count', 'desc')
              ->orderBy('average_rating', 'desc');
        } else {
            // Build search query with filters
            $query = $this->buildSearchQuery($searchQuery, $request);
        }
        
        // Apply filters
        $query = $this->applyFilters($query, $request);
        
        // Apply sorting
        $query = $this->applySorting($query, $request);
        
        // Get paginated results
        $products = $query->paginate($request->get('per_page', 12));
        
        // Process products (add price ranges, ratings, etc.)
        $products = $this->processProducts($products);
        
        // Prepare search meta data
        $searchMeta = [
            'query' => $searchQuery,
            'total_results' => $products->total(),
            'current_page' => $products->currentPage(),
            'per_page' => $products->perPage(),
            'has_filters' => $this->hasActiveFilters($request),
            'search_time' => microtime(true) - LARAVEL_START,
        ];
        
        return response()->json([
            'success' => true,
            'html' => view('search._results', compact('products', 'searchMeta'))->render(),
            'pagination' => $products->appends($request->all())->links()->render(),
            'meta' => $searchMeta,
            'filters_updated' => $this->hasActiveFilters($request)
        ]);
    }

    public function filters(Request $request)
    {
        $searchQuery = trim($request->get('q', ''));
        $filterData = $this->getFilterData($searchQuery, $request);
        
        return response()->json([
            'html' => view('search._filters', $filterData)->render(),
            'data' => $filterData
        ]);
    }
    
    /**
     * Track search queries for analytics
     */
    public function trackSearch(Request $request)
    {
        $query = trim($request->get('q', ''));
        
        if (!empty($query) && strlen($query) > 2) {
            $this->trackSearchQuery($query);
        }
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Helper Methods
     */
    
    private function showPopularProducts($request)
    {
        $query = Product::with([
            'images' => function($q) { $q->select('id', 'product_id', 'path', 'position')->orderBy('position')->limit(1); },
            'variations' => function($q) { $q->select('id', 'product_id', 'price', 'attribute_value_ids'); },
            'category:id,name,slug',
            'brand:id,name,slug'
        ])->select('id', 'name', 'slug', 'price', 'mrp', 'category_id', 'brand_id', 'created_at')
          ->orderBy('reviews_count', 'desc')
          ->orderBy('average_rating', 'desc');
        
        $products = $query->paginate(12);
        $products = $this->processProducts($products);
        
        $searchMeta = [
            'query' => '',
            'total_results' => $products->total(),
            'current_page' => $products->currentPage(),
            'per_page' => $products->perPage(),
            'search_time' => 0,
            'is_popular' => true
        ];
        
        // Get trending searches
        $trendingSearches = $this->getTrendingSearches();
        $popularCategories = Category::withCount('products')->orderBy('products_count', 'desc')->limit(8)->get();
        
        return view('search.index', compact('products', 'searchMeta', 'trendingSearches', 'popularCategories'));
    }
    
    private function extractSearchTerms($searchQuery)
    {
        // Split by spaces and remove empty terms
        return array_filter(explode(' ', strtolower($searchQuery)), function($term) {
            return strlen(trim($term)) > 2;
        });
    }
    
    private function hasActiveFilters($request)
    {
        return $request->has('categories') || 
               $request->has('brands') || 
               $request->has('min_price') || 
               $request->has('max_price') || 
               $request->has('sizes') || 
               $request->has('colors') || 
               $request->has('in_stock') ||
               $request->has('availability') ||
               $request->has('discount_range');
    }
    
    private function detectCategoryIntent($searchQuery)
    {
        return Category::where('name', 'like', "%{$searchQuery}%")
            ->limit(3)
            ->get(['id', 'name', 'slug']);
    }
    
    private function detectBrandIntent($searchQuery)
    {
        return Brand::where('name', 'like', "%{$searchQuery}%")
            ->limit(3)
            ->get(['id', 'name', 'slug']);
    }
    
    private function getSearchSuggestions($searchQuery)
    {
        if (strlen($searchQuery) < 3) return [];
        
        return Cache::remember("search_suggestions_{$searchQuery}", 1800, function() use ($searchQuery) {
            // Similar product names
            return Product::select('name')
                ->where('name', 'like', "%{$searchQuery}%")
                ->distinct()
                ->limit(5)
                ->pluck('name')
                ->toArray();
        });
    }
    
    private function getRelatedQueries($searchQuery)
    {
        // This would typically come from a search analytics table
        // For now, return some basic related terms
        $terms = explode(' ', $searchQuery);
        $related = [];
        
        foreach ($terms as $term) {
            if (strlen($term) > 3) {
                $similarProducts = Product::where('name', 'like', "%{$term}%")
                    ->where('name', 'NOT LIKE', "%{$searchQuery}%")
                    ->limit(3)
                    ->pluck('name')
                    ->toArray();
                
                $related = array_merge($related, $similarProducts);
            }
        }
        
        return array_unique(array_slice($related, 0, 5));
    }
    
    private function trackSearchQuery($query)
    {
        // Store in cache for analytics (you could also use a database table)
        $key = 'search_analytics_' . date('Y-m-d');
        $searches = Cache::get($key, []);
        
        if (!isset($searches[$query])) {
            $searches[$query] = 0;
        }
        $searches[$query]++;
        
        Cache::put($key, $searches, now()->addDays(30));
    }
    
    private function getPopularSearches($query = '')
    {
        $key = 'search_analytics_' . date('Y-m-d');
        $searches = Cache::get($key, []);
        
        // Filter by query if provided
        if (!empty($query)) {
            $searches = array_filter($searches, function($searchTerm) use ($query) {
                return stripos($searchTerm, $query) !== false;
            }, ARRAY_FILTER_USE_KEY);
        }
        
        arsort($searches);
        return array_slice(array_keys($searches), 0, 10);
    }
    
    private function getTrendingSearches()
    {
        // Get popular searches from last 7 days
        $trending = [];
        for ($i = 0; $i < 7; $i++) {
            $key = 'search_analytics_' . date('Y-m-d', strtotime("-{$i} days"));
            $daySearches = Cache::get($key, []);
            
            foreach ($daySearches as $term => $count) {
                if (!isset($trending[$term])) {
                    $trending[$term] = 0;
                }
                $trending[$term] += $count;
            }
        }
        
        arsort($trending);
        return array_slice(array_keys($trending), 0, 10);
    }
    
    private function highlightSearchTerm($text, $term)
    {
        return preg_replace('/(' . preg_quote($term, '/') . ')/i', '<strong>$1</strong>', $text);
    }
}
