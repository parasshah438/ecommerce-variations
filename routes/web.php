<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\ProductController as FrontProduct;
use App\Http\Controllers\Frontend\CartController as FrontCart;
use App\Http\Controllers\Frontend\CheckoutController as FrontCheckout;
use App\Http\Controllers\Frontend\WishlistController as FrontWishlist;
use App\Http\Controllers\Frontend\SearchController as FrontSearch;
use App\Http\Controllers\Frontend\CouponController as FrontCoupon;
use App\Http\Controllers\OrderController as OrderController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\WelcomeController;

// Location Integration Example
Route::get('/location-integration', function () {
    return view('location-integration');
})->name('location.integration');

// Postal Code Validation Routes
Route::get('/postal-code-checker', [App\Http\Controllers\PostalCodeController::class, 'index'])->name('postal.code.checker');
Route::post('/api/validate-postal-code', [App\Http\Controllers\PostalCodeController::class, 'validatePostalCode'])->name('api.validate.postal.code');
Route::get('/api/supported-countries', [App\Http\Controllers\PostalCodeController::class, 'getSupportedCountries'])->name('api.supported.countries');

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// API route for featured products
Route::get('/api/featured-products', [WelcomeController::class, 'getFeaturedProducts'])->name('api.featured.products');

Auth::routes();

// Social Login Routes
Route::prefix('auth')->name('social.')->group(function () {
    Route::get('/{provider}', [App\Http\Controllers\Auth\SocialLoginController::class, 'redirectToProvider'])
        ->name('redirect')
        ->where('provider', 'google|facebook|github|linkedin|twitter');
    
    Route::get('/{provider}/callback', [App\Http\Controllers\Auth\SocialLoginController::class, 'handleProviderCallback'])
        ->name('callback')
        ->where('provider', 'google|facebook|github|linkedin|twitter');
    
    Route::get('/providers', [App\Http\Controllers\Auth\SocialLoginController::class, 'getProviders'])
        ->name('providers');
    
    Route::delete('/{provider}/disconnect', [App\Http\Controllers\Auth\SocialLoginController::class, 'disconnectProvider'])
        ->name('disconnect')
        ->middleware('auth')
        ->where('provider', 'google|facebook|github|linkedin|twitter');
});

// OTP Authentication Routes
Route::prefix('otp')->name('otp.')->group(function () {
    Route::get('/login', [OtpController::class, 'showOtpForm'])->name('login');
    Route::post('/send', [OtpController::class, 'sendOtp'])->name('send');
    Route::get('/send', [OtpController::class, 'redirectToLogin'])->name('send.redirect'); // Handle GET requests
    Route::get('/verify', [OtpController::class, 'showVerifyForm'])->name('verify.form');
    Route::post('/verify', [OtpController::class, 'verifyOtp'])->name('verify');
    Route::post('/resend', [OtpController::class, 'resendOtp'])->name('resend');
    Route::get('/status', [OtpController::class, 'getOtpStatus'])->name('status');
    Route::post('/cancel', [OtpController::class, 'cancelOtp'])->name('cancel');
});

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

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Social Login Demo Page
Route::get('/social-login-demo', function () {
    $socialController = new App\Http\Controllers\Auth\SocialLoginController();
    $socialProviders = $socialController->getEnabledProviders();
    return view('social-login-demo', compact('socialProviders'));
})->name('social.login.demo');

// Test single device login functionality
Route::get('/test-single-login', function () {
    return view('test-single-login');
})->name('test.single.login');

// Dashboard route (protected)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'single.session'])->name('dashboard');

// Test OTP system
Route::get('/test-otp', function () {
    try {
        $otpService = new \App\Services\OtpService(new \App\Services\ReliableEmailService());
        $result = $otpService->sendOtp('mingjk@yopmail.com', 'email', true);
        return response()->json([
            'status' => 'success',
            'result' => $result
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

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

Route::get('/products', [FrontProduct::class, 'index'])->name('products.index');
Route::get('/products/load-more', [FrontProduct::class, 'loadMore'])->name('products.load_more');
Route::get('/products/{slug}', [FrontProduct::class, 'show'])->name('products.show');

// Authenticated routes - requiring single session
Route::middleware(['auth', 'single.session'])->group(function () {
    // Cart routes
    Route::post('/cart/add', [FrontCart::class, 'add'])->name('cart.add');
    Route::post('/cart/update', [FrontCart::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [FrontCart::class, 'remove'])->name('cart.remove');
    Route::post('/cart/save-for-later', [FrontCart::class, 'saveForLater'])->name('cart.save_for_later');
    Route::post('/cart/move-to-cart', [FrontCart::class, 'moveToCart'])->name('cart.move_to_cart');
    Route::post('/cart/remove-saved', [FrontCart::class, 'removeSaved'])->name('cart.remove_saved');
    Route::post('/cart/move-to-wishlist', [FrontCart::class, 'moveToWishlist'])->name('cart.move_to_wishlist');
    Route::get('/cart/sync-counts', [FrontCart::class, 'syncCounts'])->name('cart.sync_counts');
    Route::get('/cart', [FrontCart::class, 'index'])->name('cart.index');

    // Checkout routes
    Route::get('/checkout', [FrontCheckout::class, 'index'])->name('checkout.index');
    Route::post('/checkout/place-order', [FrontCheckout::class, 'placeOrder'])->name('checkout.place_order');

    // Order routes
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Wishlist routes
    Route::get('/wishlist', [FrontWishlist::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [FrontWishlist::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/wishlist/remove', [FrontWishlist::class, 'remove'])->name('wishlist.remove');
    Route::post('/wishlist/remove-multiple', [FrontWishlist::class, 'removeMultiple'])->name('wishlist.remove_multiple');
    Route::post('/wishlist/clear-all', [FrontWishlist::class, 'clearAll'])->name('wishlist.clear_all');
    Route::post('/wishlist/move-to-cart', [FrontWishlist::class, 'moveToCart'])->name('wishlist.move_to_cart');
    Route::post('/wishlist/move-all-to-cart', [FrontWishlist::class, 'moveAllToCart'])->name('wishlist.move_all_to_cart');
    Route::get('/wishlist/load-more', [FrontWishlist::class, 'loadMore'])->name('wishlist.load_more');

    // Coupon routes
    Route::post('/coupon/apply', [FrontCoupon::class, 'apply'])->name('coupon.apply');
});

// Public search route (doesn't require authentication)
Route::get('/search', [FrontSearch::class, 'autocomplete'])->name('products.search');

// Include admin routes
require __DIR__.'/admin.php';

// Include email preview routes (only in development)
if (app()->environment('local', 'testing')) {
    require __DIR__.'/email-preview.php';
}

