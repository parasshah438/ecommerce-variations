<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\ProductController as FrontProduct;
use App\Http\Controllers\Frontend\CartController as FrontCart;
use App\Http\Controllers\Frontend\CheckoutController as FrontCheckout;
use App\Http\Controllers\Frontend\WishlistController as FrontWishlist;
use App\Http\Controllers\Frontend\SearchController as FrontSearch;
use App\Http\Controllers\Frontend\CouponController as FrontCoupon;
use App\Http\Controllers\OrderController as OrderController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// API routes for registration validation
Route::post('/api/check-email', [App\Http\Controllers\Auth\RegisterController::class, 'checkEmail'])->name('api.check.email');
Route::post('/api/check-mobile', [App\Http\Controllers\Auth\RegisterController::class, 'checkMobile'])->name('api.check.mobile');

// Geolocation API routes
Route::get('/api/geo-location', [App\Http\Controllers\GeoLocationController::class, 'getCountryCode'])->name('api.geo.location');
Route::post('/api/location-details', [App\Http\Controllers\GeoLocationController::class, 'getLocationDetails'])->name('api.location.details');
Route::get('/api/location-from-ip', [App\Http\Controllers\GeoLocationController::class, 'getLocationFromIP'])->name('api.location.from.ip');
Route::get('/api/search-locations', [App\Http\Controllers\GeoLocationController::class, 'searchLocations'])->name('api.search.locations');
Route::get('/api/pincode-details', [App\Http\Controllers\GeoLocationController::class, 'getPincodeDetails'])->name('api.pincode.details');

// Location Demo Page
Route::get('/location-demo', function () {
    return view('location-demo');
})->name('location.demo');

// Location Integration Example
Route::get('/location-integration', function () {
    return view('location-integration-example');
})->name('location.integration');

// Debug route for pincode API
Route::get('/debug/pincode/{pincode}', function($pincode) {
    $controller = new App\Http\Controllers\GeoLocationController();
    
    try {
        $request = new Illuminate\Http\Request();
        $request->merge(['pincode' => $pincode]);
        
        $response = $controller->getPincodeDetails($request);
        $data = $response->getData(true);
        
        $output = "<h3>Pincode Debug: {$pincode}</h3>";
        $output .= "<strong>Status:</strong> " . ($data['success'] ? 'Success' : 'Failed') . "<br>";
        
        if ($data['success']) {
            $output .= "<h4>Data:</h4>";
            $output .= "<pre>" . json_encode($data['data'], JSON_PRETTY_PRINT) . "</pre>";
        } else {
            $output .= "<strong>Error:</strong> " . $data['error'] . "<br>";
            $output .= "<strong>Message:</strong> " . $data['message'] . "<br>";
        }
        
        return $output;
        
    } catch (\Exception $e) {
        return "<h3>Exception occurred:</h3><p>" . $e->getMessage() . "</p>";
    }
})->where('pincode', '[0-9]{6}');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Debug route to check product variations
Route::get('/debug/product/{id}', function($id) {
    $product = App\Models\Product::with(['variations.stock', 'images'])->find($id);
    
    if (!$product) {
        return "Product $id not found";
    }
    
    $output = "<h3>Product Debug: {$product->name}</h3>";
    $output .= "<strong>Product ID:</strong> {$product->id}<br>";
    $output .= "<strong>Slug:</strong> {$product->slug}<br>";
    $output .= "<strong>Price:</strong> ₹{$product->price}<br>";
    $output .= "<strong>Total Variations:</strong> " . $product->variations->count() . "<br><br>";
    
    if ($product->variations->count() > 0) {
        $output .= "<h4>Variations:</h4>";
        foreach ($product->variations as $variation) {
            $output .= "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0;'>";
            $output .= "<strong>Variation ID:</strong> {$variation->id}<br>";
            $output .= "<strong>SKU:</strong> {$variation->sku}<br>";
            $output .= "<strong>Price:</strong> ₹{$variation->price}<br>";
            
            if ($variation->stock) {
                $output .= "<strong>Stock:</strong> {$variation->stock->quantity}<br>";
                $output .= "<strong>In Stock:</strong> " . ($variation->stock->in_stock ? 'Yes' : 'No') . "<br>";
            } else {
                $output .= "<strong>Stock:</strong> No stock record<br>";
            }
            
            $output .= "<strong>Attribute Value IDs:</strong> " . json_encode($variation->attribute_value_ids) . "<br>";
            
            // Get attribute values using the accessor
            $attributeValues = $variation->attribute_values;
            $output .= "<strong>Attributes:</strong> ";
            if ($attributeValues->count() > 0) {
                $attrs = [];
                foreach ($attributeValues as $attrValue) {
                    $attrs[] = "{$attrValue->attribute->name}: {$attrValue->value}";
                }
                $output .= implode(', ', $attrs);
            } else {
                $output .= "No attributes";
            }
            $output .= "<br>";
            $output .= "</div>";
        }
    } else {
        $output .= "<p>No variations found for this product.</p>";
    }
    
    $output .= "<br><a href='/test/12/variations/public/products/{$product->slug}'>View Product Page</a>";
    
    return $output;
});

// Debug route to test product controller data
Route::get('/debug/product-data/{slug}', function($slug) {
    $product = App\Models\Product::with(['images', 'variations.stock'])->where('slug', $slug)->firstOrFail();

    // Prepare JSON-friendly variations (same logic as controller)
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

    // Prepare attribute groups
    $allValueIds = collect($variations)->flatMap(function ($v) { return $v['values']; })->unique()->values()->all();
    $attributeGroups = [];
    if (!empty($allValueIds)) {
        $values = App\Models\AttributeValue::whereIn('id', $allValueIds)->with('attribute')->get();
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

    $output = "<h3>Product Controller Data: {$product->name}</h3>";
    $output .= "<strong>Variations:</strong> " . count($variations) . "<br>";
    $output .= "<strong>All Value IDs:</strong> " . implode(', ', $allValueIds) . "<br>";
    $output .= "<strong>Attribute Groups:</strong> " . count($attributeGroups) . "<br>";
    
    foreach ($attributeGroups as $attrName => $options) {
        $output .= "<h4>{$attrName} ({" . count($options) . " options)</h4>";
        foreach ($options as $opt) {
            $output .= "- {$opt['value']} (ID: {$opt['id']})<br>";
        }
    }
    
    $output .= "<h4>Variations JSON:</h4>";
    $output .= "<pre>" . json_encode($variations, JSON_PRETTY_PRINT) . "</pre>";
    
    return $output;
});

// Debug route to fix product variations by adding attributes
Route::get('/debug/fix-product-variations/{id}', function($id) {
    $product = App\Models\Product::with('variations')->find($id);
    
    if (!$product) {
        return "Product $id not found";
    }

    // Get some color attribute values
    $colorValues = App\Models\AttributeValue::whereHas('attribute', function($q) {
        $q->where('slug', 'color');
    })->take(5)->pluck('id')->toArray();
    
    // Get some size attribute values  
    $sizeValues = App\Models\AttributeValue::whereHas('attribute', function($q) {
        $q->where('slug', 'size');
    })->take(4)->pluck('id')->toArray();

    $output = "<h3>Fixing Product Variations: {$product->name}</h3>";
    
    if (empty($colorValues) || empty($sizeValues)) {
        return $output . "<p>No attribute values found. Please run the seeder first.</p>";
    }
    
    $fixed = 0;
    foreach ($product->variations as $index => $variation) {
        if (empty($variation->attribute_value_ids)) {
            // Assign some attributes to this variation
            $attributes = [];
            
            // Assign a color
            if (!empty($colorValues)) {
                $attributes[] = $colorValues[$index % count($colorValues)];
            }
            
            // Assign a size
            if (!empty($sizeValues)) {
                $attributes[] = $sizeValues[$index % count($sizeValues)];
            }
            
            $variation->update([
                'attribute_value_ids' => $attributes
            ]);
            
            $output .= "Fixed variation ID {$variation->id} - assigned attributes: " . implode(', ', $attributes) . "<br>";
            $fixed++;
        }
    }
    
    $output .= "<br><strong>Fixed {$fixed} variations</strong><br>";
    $output .= "<br><a href='/test/12/variations/public/debug/product-data/{$product->slug}'>Check Product Data</a> | ";
    $output .= "<a href='/test/12/variations/public/products/{$product->slug}'>View Product Page</a>";
    
    return $output;
});

Route::get('/products', [FrontProduct::class, 'index'])->name('products.index');
Route::get('/products/load-more', [FrontProduct::class, 'loadMore'])->name('products.load_more');
Route::get('/products/{slug}', [FrontProduct::class, 'show'])->name('products.show');

Route::post('/cart/add', [FrontCart::class, 'add'])->name('cart.add');
Route::post('/cart/update', [FrontCart::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [FrontCart::class, 'remove'])->name('cart.remove');
Route::post('/cart/save-for-later', [FrontCart::class, 'saveForLater'])->name('cart.save_for_later');
Route::post('/cart/move-to-cart', [FrontCart::class, 'moveToCart'])->name('cart.move_to_cart');
Route::post('/cart/remove-saved', [FrontCart::class, 'removeSaved'])->name('cart.remove_saved');
Route::post('/cart/move-to-wishlist', [FrontCart::class, 'moveToWishlist'])->name('cart.move_to_wishlist');
Route::get('/cart/sync-counts', [FrontCart::class, 'syncCounts'])->name('cart.sync_counts');
Route::get('/cart', [FrontCart::class, 'index'])->name('cart.index');

Route::get('/checkout', [FrontCheckout::class, 'index'])->name('checkout.index');
Route::post('/checkout/place-order', [FrontCheckout::class, 'placeOrder'])->name('checkout.place_order');

Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

Route::get('/wishlist', [FrontWishlist::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle', [FrontWishlist::class, 'toggle'])->name('wishlist.toggle');
Route::post('/wishlist/remove', [FrontWishlist::class, 'remove'])->name('wishlist.remove');
Route::post('/wishlist/remove-multiple', [FrontWishlist::class, 'removeMultiple'])->name('wishlist.remove_multiple');
Route::post('/wishlist/clear-all', [FrontWishlist::class, 'clearAll'])->name('wishlist.clear_all');
Route::post('/wishlist/move-to-cart', [FrontWishlist::class, 'moveToCart'])->name('wishlist.move_to_cart');
Route::post('/wishlist/move-all-to-cart', [FrontWishlist::class, 'moveAllToCart'])->name('wishlist.move_all_to_cart');
Route::get('/wishlist/load-more', [FrontWishlist::class, 'loadMore'])->name('wishlist.load_more');

Route::get('/search', [FrontSearch::class, 'autocomplete'])->name('products.search');
Route::post('/coupon/apply', [FrontCoupon::class, 'apply'])->name('coupon.apply');

// Admin Order Management Routes
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/confirm', [AdminOrderController::class, 'confirmOrder'])->name('orders.confirm');
    Route::post('/orders/{order}/cancel', [AdminOrderController::class, 'cancelOrder'])->name('orders.cancel');
    Route::post('/orders/{order}/return', [AdminOrderController::class, 'returnOrder'])->name('orders.return');
    Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update_status');
    
    // Email Log Management Routes
    Route::get('/email-logs', [\App\Http\Controllers\Admin\EmailLogController::class, 'index'])->name('email-logs.index');
    Route::get('/email-logs/{emailLog}', [\App\Http\Controllers\Admin\EmailLogController::class, 'show'])->name('email-logs.show');
    Route::get('/email-logs/{emailLog}/retry', [\App\Http\Controllers\Admin\EmailLogController::class, 'retry'])->name('email-logs.retry');
    Route::get('/email-logs/retry-all', [\App\Http\Controllers\Admin\EmailLogController::class, 'retryAll'])->name('email-logs.retry-all');
    Route::get('/email-logs/process-retry-queue', [\App\Http\Controllers\Admin\EmailLogController::class, 'processRetryQueue'])->name('email-logs.process-retry-queue');
    Route::get('/email-logs/{emailLog}/delete', [\App\Http\Controllers\Admin\EmailLogController::class, 'delete'])->name('email-logs.delete');
    Route::post('/email-logs/bulk-delete', [\App\Http\Controllers\Admin\EmailLogController::class, 'bulkDelete'])->name('email-logs.bulk-delete');
    Route::get('/email-logs/export', [\App\Http\Controllers\Admin\EmailLogController::class, 'export'])->name('email-logs.export');
});

// Include admin routes
require __DIR__.'/admin.php';

// Include email preview routes (only in development)
if (app()->environment('local', 'testing')) {
    require __DIR__.'/email-preview.php';
}

