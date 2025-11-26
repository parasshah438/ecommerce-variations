<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class PagesController extends Controller
{
    /**
     * Display the About Us page
     *
     * @return \Illuminate\View\View
     */
    public function about()
    {
        return view('pages.about');
    }

    /**
     * Display the FAQ page
     *
     * @return \Illuminate\View\View
     */
    public function faq()
    {
        return view('pages.faq');
    }

    /**
     * Display the Help page
     *
     * @return \Illuminate\View\View
     */
    public function help()
    {
        return view('pages.help');
    }

    /**
     * Display the Support page (redirect to help)
     *
     * @return \Illuminate\View\View
     */
    public function support()
    {
        return view('pages.help');
    }

    /**
     * Display the Privacy Policy page
     *
     * @return \Illuminate\View\View
     */
    public function privacy()
    {
        return view('pages.privacy');
    }

    /**
     * Display the Terms & Conditions page
     *
     * @return \Illuminate\View\View
     */
    public function terms()
    {
        return view('pages.terms');
    }

    /**
     * Display the Shipping Policy page
     *
     * @return \Illuminate\View\View
     */
    public function shipping()
    {
        return view('pages.shipping');
    }

    /**
     * Display the Return & Refund Policy page
     *
     * @return \Illuminate\View\View
     */
    public function returnRefund()
    {
        return view('pages.return-refund');
    }

    /**
     * Display the Cookie Policy page
     *
     * @return \Illuminate\View\View
     */
    public function cookiePolicy()
    {
        return view('pages.cookie-policy');
    }

    /**
     * Display the Cookie Preferences page
     *
     * @return \Illuminate\View\View
     */
    public function cookiePreferences()
    {
        return view('pages.cookie-preferences');
    }

    /**
     * Display the Size Guide page
     *
     * @return \Illuminate\View\View
     */
    public function sizeGuide()
    {
        return view('pages.size-guide');
    }

    /**
     * Display the Virtual Try-On page
     *
     * @return \Illuminate\View\View
     */
    public function virtualTryOn()
    {
        // Get products suitable for virtual try-on (clothing items)
        $products = collect();
        
        try {
            $products = \App\Models\Product::with([
                'images' => function($query) {
                    $query->orderBy('position');
                },
                'variations' => function($query) {
                    $query->with([
                        'stock',
                        'attributeValues.attribute'
                    ]);
                },
                'brand',
                'category'
            ])
            ->where('active', true)
            ->whereHas('variations', function($query) {
                $query->whereHas('stock', function($stockQuery) {
                    $stockQuery->where('quantity', '>', 0)
                             ->where('in_stock', true);
                });
            })
            ->where(function($query) {
                // Filter for clothing items suitable for virtual try-on
                $query->where('name', 'LIKE', '%shirt%')
                      ->orWhere('name', 'LIKE', '%t-shirt%')
                      ->orWhere('name', 'LIKE', '%jacket%')
                      ->orWhere('name', 'LIKE', '%hoodie%')
                      ->orWhere('name', 'LIKE', '%dress%')
                      ->orWhere('name', 'LIKE', '%top%')
                      ->orWhere('name', 'LIKE', '%blouse%')
                      ->orWhere('name', 'LIKE', '%sweater%')
                      ->orWhere('name', 'LIKE', '%cardigan%')
                      ->orWhere('name', 'LIKE', '%blazer%')
                      ->orWhere(function($categoryQuery) {
                          // Also include products from clothing categories
                          $categoryQuery->whereHas('category', function($catQuery) {
                              $catQuery->where('name', 'LIKE', '%clothing%')
                                       ->orWhere('name', 'LIKE', '%apparel%')
                                       ->orWhere('name', 'LIKE', '%fashion%')
                                       ->orWhere('name', 'LIKE', '%wear%');
                          });
                      });
            })
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

            // Transform products to include necessary data for virtual try-on
            $products = $products->map(function ($product) {
                // Get the first available variation for default selection
                $defaultVariation = $product->variations->where('is_in_stock', true)->first();
                
                // Get available sizes and colors from variations
                $sizes = collect();
                $colors = collect();
                
                foreach ($product->variations as $variation) {
                    if ($variation->is_in_stock) {
                        foreach ($variation->attribute_values as $attrValue) {
                            if (strtolower($attrValue->attribute->name) === 'size') {
                                $sizes->push($attrValue->value);
                            } elseif (in_array(strtolower($attrValue->attribute->name), ['color', 'colour'])) {
                                $colors->push($attrValue->value);
                            }
                        }
                    }
                }

                // Filter by selected size if provided
                if ($size && !empty(trim($size))) {
                    \Log::info('AI Recommendations: Filtering by size', ['size' => $size]);
                    
                    // Find size attribute values that match the selected size
                    $sizeAttribute = \App\Models\Attribute::where('name', 'Size')
                        ->orWhere('slug', 'size')
                        ->orWhere('name', 'LIKE', '%size%')
                        ->first();
                        
                    if ($sizeAttribute) {
                        // Try exact match first, then case-insensitive
                        $sizeValueIds = \App\Models\AttributeValue::where('attribute_id', $sizeAttribute->id)
                            ->where(function($sizeQuery) use ($size) {
                                $sizeQuery->where('value', $size)
                                         ->orWhere('value', 'LIKE', "%{$size}%")
                                         ->orWhereRaw('UPPER(value) = ?', [strtoupper($size)]);
                            })
                            ->pluck('id')
                            ->toArray();
                            
                        \Log::info('AI Recommendations: Found size value IDs', ['size_value_ids' => $sizeValueIds]);
                            
                        if (!empty($sizeValueIds)) {
                            $query->whereHas('variations', function($vQuery) use ($sizeValueIds) {
                                $vQuery->where(function($innerQuery) use ($sizeValueIds) {
                                    foreach ($sizeValueIds as $sizeId) {
                                        $innerQuery->orWhereRaw('JSON_CONTAINS(attribute_value_ids, ?)', [json_encode([$sizeId])])
                                                   ->orWhereRaw('JSON_CONTAINS(attribute_value_ids, ?)', [json_encode((string)$sizeId)]);
                                    }
                                });
                            });
                        } else {
                            \Log::warning('AI Recommendations: No matching size values found for', ['size' => $size]);
                        }
                    } else {
                        \Log::warning('AI Recommendations: Size attribute not found');
                    }
                }
                
                $product->available_sizes = $sizes->unique()->values();
                $product->available_colors = $colors->unique()->values();
                $product->default_variation = $defaultVariation;
                $product->thumbnail = $product->getThumbnailImage();
                $product->sale_price = $product->getBestSalePrice();
                $product->discount_percentage = $product->getDiscountPercentage();
                
                return $product;
            });

        } catch (\Exception $e) {
            // If there's an error with the database, continue with empty collection
            \Log::error('Virtual Try-On: Error fetching products - ' . $e->getMessage());
        }
        
        // Get user authentication status for JavaScript
        $isAuthenticated = auth()->check();
        
        return view('pages.virtual-try-on', compact('products', 'isAuthenticated'));
    }

    /**
     * Display the Accessibility / Screen Reader Info page
     *
     * @return \Illuminate\View\View
     */
    public function accessibility()
    {
        return view('pages.accessibility');
    }

    /**
     * Display the Security / Data Protection page
     *
     * @return \Illuminate\View\View
     */
    public function securityDataProtection()
    {
        return view('pages.security-data-protection');
    }

    /**
     * Display the AI Personal Shopper / Quiz page
     *
     * @return \Illuminate\View\View
     */
    public function aiPersonalShopper()
    {
        // Get active categories for dynamic category selection
        $categories = \App\Models\Category::where('is_active', true)
            ->whereNull('parent_id') // Only parent categories for main selection
            //->withCount('products')
            //->having('products_count', '>', 0) // Only categories with products
            ->orderBy('name')
            ->get();

        // Icon mapping for categories
        $categoryIcons = [
            'clothing' => 'fas fa-tshirt',
            'apparel' => 'fas fa-tshirt',
            'shirts' => 'fas fa-tshirt',
            'footwear' => 'fas fa-shoe-prints',
            'shoes' => 'fas fa-shoe-prints',
            'sneakers' => 'fas fa-shoe-prints',
            'accessories' => 'fas fa-glasses',
            'jewelry' => 'fas fa-gem',
            'watches' => 'fas fa-clock',
            'bags' => 'fas fa-shopping-bag',
            'handbags' => 'fas fa-shopping-bag',
            'backpacks' => 'fas fa-backpack',
            'electronics' => 'fas fa-mobile-alt',
            'gadgets' => 'fas fa-laptop',
            'home' => 'fas fa-home',
            'furniture' => 'fas fa-couch',
            'beauty' => 'fas fa-spa',
            'cosmetics' => 'fas fa-palette',
            'sports' => 'fas fa-running',
            'fitness' => 'fas fa-dumbbell',
            'books' => 'fas fa-book',
            'toys' => 'fas fa-gamepad',
            'automotive' => 'fas fa-car',
            'tools' => 'fas fa-tools',
            'garden' => 'fas fa-seedling',
            'health' => 'fas fa-heart',
            'baby' => 'fas fa-baby',
            'pet' => 'fas fa-paw'
        ];

        // Map icons to categories based on name matching
        $categories->each(function ($category) use ($categoryIcons) {
            $categoryNameLower = strtolower($category->name);
            $icon = 'fas fa-tag'; // Default icon
            
            foreach ($categoryIcons as $keyword => $iconClass) {
                if (strpos($categoryNameLower, $keyword) !== false) {
                    $icon = $iconClass;
                    break;
                }
            }
            
            $category->icon = $icon;
        });

        // Get dynamic colors from database
        $colors = collect();
        try {
            // Find the Color attribute
            $colorAttribute = \App\Models\Attribute::where('name', 'Color')
                ->orWhere('slug', 'color')
                ->orWhere('name', 'LIKE', '%color%')
                ->first();

            if ($colorAttribute) {
                // Get colors that are actually used in product variations
                $colors = \App\Models\AttributeValue::where('attribute_id', $colorAttribute->id)
                    ->whereHas('attribute', function($query) {
                        $query->where('name', 'Color')
                              ->orWhere('slug', 'color') 
                              ->orWhere('name', 'LIKE', '%color%');
                    })
                    ->whereRaw('EXISTS (
                        SELECT 1 FROM product_variations pv 
                        WHERE JSON_CONTAINS(pv.attribute_value_ids, CAST(attribute_values.id AS JSON))
                        OR JSON_CONTAINS(pv.attribute_value_ids, JSON_ARRAY(CAST(attribute_values.id AS CHAR)))
                    )')
                    ->orderBy('value')
                    ->get();
            }

            // If no colors found or empty, provide default color set
            if ($colors->isEmpty()) {
                $colors = collect([
                    (object) ['id' => 'black', 'value' => 'Black', 'hex_color' => '#000000'],
                    (object) ['id' => 'white', 'value' => 'White', 'hex_color' => '#FFFFFF'], 
                    (object) ['id' => 'blue', 'value' => 'Blue', 'hex_color' => '#3498db'],
                    (object) ['id' => 'red', 'value' => 'Red', 'hex_color' => '#e74c3c'],
                    (object) ['id' => 'green', 'value' => 'Green', 'hex_color' => '#27ae60'],
                    (object) ['id' => 'pink', 'value' => 'Pink', 'hex_color' => '#e91e63'],
                ]);
            }

        } catch (\Exception $e) {
            \Log::warning('Failed to fetch colors for AI personal shopper: ' . $e->getMessage());
            // Fallback to default colors
            $colors = collect([
                (object) ['id' => 'black', 'value' => 'Black', 'hex_color' => '#000000'],
                (object) ['id' => 'white', 'value' => 'White', 'hex_color' => '#FFFFFF'], 
                (object) ['id' => 'blue', 'value' => 'Blue', 'hex_color' => '#3498db'],
                (object) ['id' => 'red', 'value' => 'Red', 'hex_color' => '#e74c3c'],
                (object) ['id' => 'green', 'value' => 'Green', 'hex_color' => '#27ae60'],
                (object) ['id' => 'pink', 'value' => 'Pink', 'hex_color' => '#e91e63'],
            ]);
        }

        // Get dynamic sizes from database
        $sizes = collect();
        try {
            // Find the Size attribute
            $sizeAttribute = \App\Models\Attribute::where('name', 'Size')
                ->orWhere('slug', 'size')
                ->orWhere('name', 'LIKE', '%size%')
                ->first();

            if ($sizeAttribute) {
                // Get sizes that are actually used in product variations
                $sizes = \App\Models\AttributeValue::where('attribute_id', $sizeAttribute->id)
                    ->whereHas('attribute', function($query) {
                        $query->where('name', 'Size')
                              ->orWhere('slug', 'size')
                              ->orWhere('name', 'LIKE', '%size%');
                    })
                    ->whereRaw('EXISTS (
                        SELECT 1 FROM product_variations pv 
                        WHERE JSON_CONTAINS(pv.attribute_value_ids, CAST(attribute_values.id AS JSON))
                        OR JSON_CONTAINS(pv.attribute_value_ids, JSON_ARRAY(CAST(attribute_values.id AS CHAR)))
                    )')
                    ->orderByRaw('CASE 
                        WHEN value = "XS" THEN 1
                        WHEN value = "S" THEN 2
                        WHEN value = "M" THEN 3
                        WHEN value = "L" THEN 4
                        WHEN value = "XL" THEN 5
                        WHEN value = "XXL" THEN 6
                        WHEN value = "XXXL" THEN 7
                        ELSE 8 
                    END')
                    ->orderBy('value')
                    ->get();
            }

            // If no sizes found or empty, provide default size set
            if ($sizes->isEmpty()) {
                $sizes = collect([
                    (object) ['id' => 'S', 'value' => 'S', 'attribute_id' => null],
                    (object) ['id' => 'M', 'value' => 'M', 'attribute_id' => null],
                    (object) ['id' => 'L', 'value' => 'L', 'attribute_id' => null],
                    (object) ['id' => 'XL', 'value' => 'XL', 'attribute_id' => null],
                    (object) ['id' => 'XXL', 'value' => 'XXL', 'attribute_id' => null],
                ]);
            }

            // Add icons for sizes based on their values
            $sizeIcons = [
                'XS' => 'fas fa-compress-alt',
                'S' => 'fas fa-compress-alt', 
                'M' => 'fas fa-expand-alt',
                'L' => 'fas fa-arrows-alt',
                'XL' => 'fas fa-arrows-alt-h',
                'XXL' => 'fas fa-arrows-alt-v',
                'XXXL' => 'fas fa-expand-arrows-alt',
            ];

            $sizes->each(function ($size) use ($sizeIcons) {
                $sizeValue = strtoupper(trim($size->value));
                $size->icon = $sizeIcons[$sizeValue] ?? 'fas fa-ruler';
            });

        } catch (\Exception $e) {
            \Log::warning('Failed to fetch sizes for AI personal shopper: ' . $e->getMessage());
            // Fallback to default sizes
            $sizes = collect([
                (object) ['id' => 'S', 'value' => 'S', 'icon' => 'fas fa-compress-alt'],
                (object) ['id' => 'M', 'value' => 'M', 'icon' => 'fas fa-expand-alt'],
                (object) ['id' => 'L', 'value' => 'L', 'icon' => 'fas fa-arrows-alt'],
                (object) ['id' => 'XL', 'value' => 'XL', 'icon' => 'fas fa-arrows-alt-h'],
                (object) ['id' => 'XXL', 'value' => 'XXL', 'icon' => 'fas fa-arrows-alt-v'],
            ]);
        }

        return view('pages.ai-personal-shopper', compact('categories', 'colors', 'sizes'));
    }
    /**
     * Get AI recommendations based on user preferences
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function getAiRecommendations(Request $request)
    {
        try {
            $category = $request->input('category');
            $priceRange = $request->input('price_range');
            $occasion = $request->input('occasion');
            $style = $request->input('style');
            $colors = $request->input('colors', []);
            $size = $request->input('size');

            // Log the incoming request for debugging
            \Log::info('AI Recommendations Request', [
                'method' => $request->method(),
                'is_ajax' => $request->ajax(),
                'expects_json' => $request->expectsJson(),
                'content_type' => $request->header('Content-Type'),
                'accept' => $request->header('Accept'),
                'user_selections' => [
                    'category' => $category,
                    'price_range' => $priceRange,
                    'occasion' => $occasion,
                    'style' => $style,
                    'colors' => $colors,
                    'size' => $size
                ]
            ]);

            // Debug: Log all available categories in the database
            $allCategories = \App\Models\Category::select('id', 'name', 'slug')->get();
            \Log::info('Available categories in database', [
                'categories' => $allCategories->toArray()
            ]);

            // Debug: Log all available colors and sizes
            $colorAttribute = \App\Models\Attribute::where('name', 'Color')->first();
            $sizeAttribute = \App\Models\Attribute::where('name', 'Size')->first();
            
            if ($colorAttribute) {
                $availableColors = \App\Models\AttributeValue::where('attribute_id', $colorAttribute->id)
                    ->pluck('value')->toArray();
                \Log::info('Available colors', ['colors' => $availableColors]);
            }
            
            if ($sizeAttribute) {
                $availableSizes = \App\Models\AttributeValue::where('attribute_id', $sizeAttribute->id)
                    ->pluck('value')->toArray();
                \Log::info('Available sizes', ['sizes' => $availableSizes]);
            }






            
            // Build query for products with variations
            $query = \App\Models\Product::with(['images', 'variations.stock', 'category'])
              //  ->where('status', 'active')
                ->whereHas('variations');
            // Filter by category if selected
            if ($category) {
                \Log::info('AI Recommendations: Filtering by category', ['category' => $category]);
                
                // Try multiple approaches to find the category
                $selectedCategory = \App\Models\Category::where('slug', $category)
                    ->orWhere('name', $category)
                    ->orWhere('name', 'LIKE', "%{$category}%")
                    ->orWhere('slug', 'LIKE', "%{$category}%")
                    ->first();
                
                if ($selectedCategory) {
                    \Log::info('AI Recommendations: Found category', [
                        'category_id' => $selectedCategory->id,
                        'category_name' => $selectedCategory->name,
                        'category_slug' => $selectedCategory->slug
                    ]);
                    
                    // Include products from this category and its subcategories
                    $categoryIds = collect([$selectedCategory->id]);
                    if ($selectedCategory->children->count() > 0) {
                        $categoryIds = $categoryIds->merge($selectedCategory->children->pluck('id'));
                    }
                    // Use whereIn for single category_id field
                    $query->whereIn('category_id', $categoryIds->toArray());
                    
                    \Log::info('AI Recommendations: Filtering by category IDs', ['category_ids' => $categoryIds->toArray()]);
                } else {
                    \Log::warning('AI Recommendations: Category not found, trying broader search', ['category' => $category]);
                    // If exact category not found, try broader search by keywords
                    $categoryKeywords = explode('-', str_replace('_', ' ', $category));
                    
                    // Handle special cases for common category terms
                    if (in_array($category, ['fashion', 'clothing', 'apparel'])) {
                        $query->whereHas('category', function($catQuery) {
                            $catQuery->where('name', 'LIKE', '%fashion%')
                                     ->orWhere('name', 'LIKE', '%clothing%')
                                     ->orWhere('name', 'LIKE', '%apparel%')
                                     ->orWhere('name', 'LIKE', '%wear%')
                                     ->orWhere('slug', 'LIKE', '%fashion%')
                                     ->orWhere('slug', 'LIKE', '%clothing%')
                                     ->orWhere('slug', 'LIKE', '%apparel%');
                        });
                    } else {
                        $query->where(function($q) use ($categoryKeywords) {
                            foreach ($categoryKeywords as $keyword) {
                                if (strlen($keyword) > 2) {
                                    $q->orWhereHas('category', function($catQuery) use ($keyword) {
                                        $catQuery->where('name', 'LIKE', "%{$keyword}%")
                                                 ->orWhere('slug', 'LIKE', "%{$keyword}%");
                                    });
                                }
                            }
                        });
                    }
                }
            }

            // Apply filters based on selections
            if ($priceRange) {
                [$minPrice, $maxPrice] = explode('-', $priceRange);
                $query->whereBetween('price', [(float)$minPrice, (float)$maxPrice]);
            }

            // Filter by selected colors if provided
            if ($colors && is_array($colors) && !empty($colors)) {
                \Log::info('AI Recommendations: Filtering by colors', ['colors' => $colors]);
                
                // Find color attribute values that match the selected colors
                $colorAttribute = \App\Models\Attribute::where('name', 'Color')
                    ->orWhere('slug', 'color')
                    ->orWhere('name', 'LIKE', '%color%')
                    ->first();
                    
                if ($colorAttribute) {
                    // Try case-insensitive matching for colors
                    $colorValueIds = \App\Models\AttributeValue::where('attribute_id', $colorAttribute->id)
                        ->where(function($colorQuery) use ($colors) {
                            foreach ($colors as $color) {
                                $colorQuery->orWhere('value', 'LIKE', "%{$color}%");
                            }
                        })
                        ->pluck('id')
                        ->toArray();
                        
                    \Log::info('AI Recommendations: Found color value IDs', ['color_value_ids' => $colorValueIds]);
                        
                    if (!empty($colorValueIds)) {
                        $query->whereHas('variations', function($vQuery) use ($colorValueIds) {
                            $vQuery->where(function($innerQuery) use ($colorValueIds) {
                                foreach ($colorValueIds as $colorId) {
                                    $innerQuery->orWhereRaw('JSON_CONTAINS(attribute_value_ids, ?)', [json_encode([$colorId])])
                                               ->orWhereRaw('JSON_CONTAINS(attribute_value_ids, ?)', [json_encode((string)$colorId)]);
                                }
                            });
                        });
                    } else {
                        \Log::warning('AI Recommendations: No matching color values found for', ['colors' => $colors]);
                    }
                } else {
                    \Log::warning('AI Recommendations: Color attribute not found');
                }
            }            // Simulate AI logic based on selections
            $baseScore = 100;
            $orderBy = 'created_at'; // default

                // if ($style) {
                //     \Log::info('AI Recommendations: Filtering by style', ['style' => $style]);
                //     switch ($style) {
                //         case 'modern':
                //             $query->where('name', 'LIKE', '%modern%');
                //             break;
                //         case 'classic':
                //             $query->where('name', 'LIKE', '%classic%');
                //             break;
                //         case 'bohemian':
                //             $query->where(function($q) {
                //                 $q->where('name', 'LIKE', '%bohemian%')
                //                   ->orWhere('name', 'LIKE', '%boho%')
                //                   ->orWhere('description', 'LIKE', '%bohemian%')
                //                   ->orWhere('description', 'LIKE', '%boho%')
                //                   ->orWhere('description', 'LIKE', '%free-spirited%')
                //                   ->orWhere('description', 'LIKE', '%artistic%');
                //             });
                //             break;
                //         case 'trendy':
                //             $orderBy = 'created_at'; // newest first
                //             break;
                //         case 'minimalist':
                //             $query->where(function($q) {
                //                 $q->where('name', 'LIKE', '%minimalist%')
                //                   ->orWhere('name', 'LIKE', '%minimal%')
                //                   ->orWhere('description', 'LIKE', '%minimalist%')
                //                   ->orWhere('description', 'LIKE', '%clean lines%')
                //                   ->orWhere('description', 'LIKE', '%simple%');
                //             });
                //             break;
                //     }
                // }

            // if ($occasion) {
            //     switch ($occasion) {
            //         case 'formal':
            //             $query->where(function($q) {
            //                 $q->where('name', 'LIKE', '%formal%')
            //                   ->orWhere('name', 'LIKE', '%shirt%')
            //                   ->orWhere('name', 'LIKE', '%blazer%');
            //             });
            //             break;
            //         case 'casual':
            //             $query->where(function($q) {
            //                 $q->where('name', 'LIKE', '%casual%')
            //                   ->orWhere('name', 'LIKE', '%t-shirt%')
            //                   ->orWhere('name', 'LIKE', '%jeans%');
            //             });
            //             break;
            //         case 'party':
            //             $query->where(function($q) {
            //                 $q->where('name', 'LIKE', '%party%')
            //                   ->orWhere('name', 'LIKE', '%dress%')
            //                   ->orWhere('name', 'LIKE', '%suit%');
            //             });
            //             break;
            //     }
            // }
            // Log the query for debugging
            \Log::info('AI Recommendations: Executing product query', [
                'filters_applied' => [
                    'category' => $category,
                    'price_range' => $priceRange,
                    'colors' => $colors,
                    'size' => $size,
                    'occasion' => $occasion,
                    'style' => $style
                ]
            ]);

            // Get products
            $products = $query->orderBy($orderBy, 'desc')
                             ->limit(12)
                             ->get();

            \Log::info('AI Recommendations: Query result', [
                'products_found' => $products->count(),
                'product_ids' => $products->pluck('id')->toArray()
            ]);

                // NO FALLBACK - If no products found with user's specific filters, return empty
                // This ensures we only show products that actually match the user's criteria
                \Log::info('AI Recommendations: Final query result', [
                    'products_found' => $products->count(),
                    'user_filters_respected' => true
                ]);            // Check if request expects JSON (AJAX request or API call)
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                $html = view('pages.partials.ai-recommendations', compact('products'))->render();
                
                $message = $products->count() > 0 
                    ? "Found {$products->count()} products matching your preferences"
                    : "No products found with the selected criteria: " . 
                      ($category ? "Category: {$category}, " : "") .
                      ($style ? "Style: {$style}, " : "") .
                      ($colors ? "Colors: {$colors}, " : "") .
                      ($size ? "Size: {$size}, " : "") .
                      ($priceRange ? "Price: ₹{$priceRange}" : "");

                return response()->json([
                    'success' => $products->count() > 0,
                    'html' => $html,
                    'count' => $products->count(),
                    'message' => $message,
                    'filters_applied' => [
                        'category' => $category,
                        'price_range' => $priceRange,
                        'colors' => $colors,
                        'size' => $size,
                        'occasion' => $occasion,
                        'style' => $style
                    ],
                    'debug' => [
                        'query_executed' => true,
                        'strict_filtering' => true,
                        'no_fallback' => true
                    ]
                ]);
            }
            return view('pages.ai-personal-shopper', compact('products'));

        } catch (\Exception $e) {
            \Log::error('AI Recommendations Error: ' . $e->getMessage());
            
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, we encountered an error while generating recommendations. Please try again.',
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }

            return view('pages.ai-personal-shopper', ['products' => collect()]);
        }
    }    /**
     * Display the Product Care Guide page
     *
     * @return \Illuminate\View\View
     */
    public function productCareGuide()
    {
        return view('pages.product-care');
    }

    /**
     * Display the Lookbook page
     *
     * @return \Illuminate\View\View
     */
    public function lookbook()
    {
        return view('pages.lookbook');
    }

    /**
     * Display the Gallery page
     *
     * @return \Illuminate\View\View
     */
    public function gallery()
    {
        return view('pages.lookbook'); // Redirect to lookbook
    }

    /**
     * Display the Maintenance page
     *
     * @return \Illuminate\View\View
     */
    public function maintenance()
    {
        return view('pages.maintenance');
    }

    /**
     * Display the 404 Error page
     *
     * @return \Illuminate\Http\Response
     */
    public function error404()
    {
        return response()->view('pages.404', [], 404);
    }

    /**
     * Display the sitemap page
     *
     * @return \Illuminate\View\View
     */
    public function sitemap()
    {
        // Organize routes by categories for better SEO and user experience
        $routes = [
            'main' => [
                ['name' => 'Home', 'url' => route('welcome'), 'description' => 'Welcome to our online store with the latest products and offers'],
                ['name' => 'Products', 'url' => route('products.index'), 'description' => 'Browse our complete collection of products'],
                ['name' => 'New Arrivals', 'url' => route('products.new_arrivals'), 'description' => 'Latest products added to our collection'],
            ],
            'account' => [
                ['name' => 'Login', 'url' => route('login'), 'description' => 'Sign in to your account'],
                ['name' => 'Register', 'url' => route('register'), 'description' => 'Create a new account'],
                ['name' => 'Dashboard', 'url' => route('dashboard'), 'description' => 'Your personal dashboard', 'auth' => true],
            ],
            'shopping' => [
                ['name' => 'Cart', 'url' => route('cart.index'), 'description' => 'View and manage your shopping cart', 'auth' => true],
                ['name' => 'Checkout', 'url' => route('checkout.index'), 'description' => 'Complete your purchase securely', 'auth' => true],
                ['name' => 'Wishlist', 'url' => route('wishlist.index'), 'description' => 'Save your favorite items', 'auth' => true],
                ['name' => 'Orders', 'url' => route('orders.index'), 'description' => 'View your order history', 'auth' => true],
            ],
            'information' => [
                ['name' => 'About Us', 'url' => route('pages.about'), 'description' => 'Learn about our company, mission, and values'],
                ['name' => 'Help & Support', 'url' => route('pages.help'), 'description' => 'Get help with orders, returns, and general questions'],
                ['name' => 'FAQ', 'url' => route('pages.faq'), 'description' => 'Frequently asked questions about our products and services'],
                ['name' => 'Size Guide', 'url' => route('pages.size.guide'), 'description' => 'Find the perfect fit with our comprehensive sizing guide'],
                ['name' => 'Virtual Try-On', 'url' => route('pages.virtual.try.on'), 'description' => 'Try products virtually using AR technology before you buy'],
            ],
            'policies' => [
                ['name' => 'Privacy Policy', 'url' => route('pages.privacy'), 'description' => 'How we collect, use, and protect your personal information'],
                ['name' => 'Terms & Conditions', 'url' => route('pages.terms'), 'description' => 'Terms of service and usage conditions'],
                ['name' => 'Shipping Policy', 'url' => route('pages.shipping'), 'description' => 'Shipping options, delivery times, and costs'],
                ['name' => 'Return & Refund Policy', 'url' => route('pages.return.refund'), 'description' => 'Our hassle-free return and refund process'],
                ['name' => 'Cookie Policy', 'url' => route('pages.cookie.policy'), 'description' => 'How we use cookies to enhance your experience'],
            ],
            'gallery' => [
                ['name' => 'Lookbook & Gallery', 'url' => route('pages.lookbook'), 'description' => 'Explore our fashion photography and style inspiration'],
            ],
        ];

        return view('pages.sitemap', compact('routes'));
    }

    /**
     * Generate XML sitemap for search engines
     *
     * @return \Illuminate\Http\Response
     */
    public function sitemapXml()
    {
        // Get all static page URLs with their priorities and change frequencies
        $urls = collect([
            // High priority pages (main site pages)
            [
                'url' => route('welcome'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'daily',
                'priority' => '1.0'
            ],
            [
                'url' => route('products.index'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'daily', 
                'priority' => '0.9'
            ],
            [
                'url' => route('products.new_arrivals'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'daily',
                'priority' => '0.8'
            ],
            
            // Authentication pages
            [
                'url' => route('login'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.5'
            ],
            [
                'url' => route('register'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.5'
            ],
            
            // Information pages - High SEO value
            [
                'url' => route('pages.about'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.7'
            ],
            [
                'url' => route('pages.help'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.6'
            ],
            [
                'url' => route('pages.faq'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.6'
            ],
            [
                'url' => route('pages.size.guide'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.6'
            ],
            [
                'url' => route('pages.virtual.try.on'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.6'
            ],
            [
                'url' => route('pages.product.care'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.5'
            ],
            [
                'url' => route('pages.lookbook'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.6'
            ],
            
            // Policy pages - Important for legal compliance
            [
                'url' => route('pages.privacy'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'yearly',
                'priority' => '0.4'
            ],
            [
                'url' => route('pages.terms'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'yearly',
                'priority' => '0.4'
            ],
            [
                'url' => route('pages.shipping'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.5'
            ],
            [
                'url' => route('pages.return.refund'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.5'
            ],
            [
                'url' => route('pages.cookie.policy'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'yearly',
                'priority' => '0.3'
            ],
            [
                'url' => route('pages.cookie.preferences'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'yearly',
                'priority' => '0.3'
            ],
            
            // Additional utility pages
            [
                'url' => route('pages.sitemap'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.4'
            ],
        ]);


        // Add dynamic product URLs if products exist
        try {
            if (class_exists('\App\Models\Product')) {
                $products = \App\Models\Product::where('is_active', true)
                    ->select('slug', 'updated_at')
                    ->get();
                
                foreach ($products as $product) {
                    $urls->push([
                        'url' => route('products.show', $product->slug),
                        'lastmod' => $product->updated_at->format('Y-m-d'),
                        'changefreq' => 'weekly',
                        'priority' => '0.7'
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Silently ignore if Product model doesn't exist or database error
        }

        // Add category URLs if categories exist
        try {
            if (class_exists('\App\Models\Category')) {
                $categories = \App\Models\Category::where('is_active', true)
                    ->select('slug', 'updated_at')
                    ->get();
                
                foreach ($categories as $category) {
                    $urls->push([
                        'url' => route('category.products', $category->slug),
                        'lastmod' => $category->updated_at->format('Y-m-d'),
                        'changefreq' => 'weekly',
                        'priority' => '0.8'
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Silently ignore if Category model doesn't exist or database error
        }

        // Add sales URLs if sales exist
        try {
            if (class_exists('\App\Models\Sale')) {
                $sales = \App\Models\Sale::where('is_active', true)
                    ->where('starts_at', '<=', now())
                    ->where('ends_at', '>=', now())
                    ->select('slug', 'updated_at')
                    ->get();
                
                foreach ($sales as $sale) {
                    $urls->push([
                        'url' => route('sales.show', $sale->slug),
                        'lastmod' => $sale->updated_at->format('Y-m-d'),
                        'changefreq' => 'daily',
                        'priority' => '0.9'
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Silently ignore if Sale model doesn't exist or database error
        }

        // Generate XML content
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['url']) . "</loc>\n";
            $xml .= "    <lastmod>" . $url['lastmod'] . "</lastmod>\n";
            $xml .= "    <changefreq>" . $url['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $url['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }
        
        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600'); // Cache for 1 hour
    
        
            // Return proper JSON error response for AJAX requests
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, we encountered an error while generating recommendations. Please try again.',
                    'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
                ], 500);
            }

            // For non-AJAX requests, return view with empty products
            return view('pages.ai-personal-shopper', ['products' => collect()]);
        
    }
}    