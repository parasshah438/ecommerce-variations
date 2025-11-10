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

// Dashboard route (protected) - Fixed Version
Route::get('/dashboard', [\App\Http\Controllers\Frontend\DashboardController::class, 'index'])
    ->middleware(['auth', 'single.session'])
    ->name('dashboard');

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
Route::get('/products/filter', [FrontProduct::class, 'index'])->name('products.filter');
Route::get('/products/load-more', [FrontProduct::class, 'loadMore'])->name('products.load_more');
Route::get('/new-arrivals', [FrontProduct::class, 'newArrivals'])->name('products.new_arrivals');
Route::get('/new-arrivals/filter', [FrontProduct::class, 'newArrivals'])->name('products.new_arrivals.filter');
Route::get('/category/{slug}', [FrontProduct::class, 'categoryProducts'])->name('category.products');
Route::get('/products/{slug}', [FrontProduct::class, 'show'])->name('products.show');

// Review routes
use App\Http\Controllers\Frontend\ReviewController;
Route::get('/products/{product}/reviews', [ReviewController::class, 'index'])->name('reviews.index');
Route::get('/products/{product}/reviews/statistics', [ReviewController::class, 'statistics'])->name('reviews.statistics');
Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store')->middleware('auth');
Route::put('/products/{product}/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update')->middleware('auth');
Route::delete('/products/{product}/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy')->middleware('auth');

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
    Route::get('/checkout/success/{order}', [FrontCheckout::class, 'success'])->name('checkout.success');
    
    // Razorpay Payment routes
    Route::post('/checkout/razorpay/create-order', [FrontCheckout::class, 'createRazorpayOrder'])->name('checkout.razorpay.create_order');
    Route::post('/checkout/razorpay/verify-payment', [FrontCheckout::class, 'verifyRazorpayPayment'])->name('checkout.razorpay.verify_payment');
    Route::post('/checkout/razorpay/payment-failed', [FrontCheckout::class, 'handleRazorpayFailure'])->name('checkout.razorpay.payment_failed');

    // Order Management System
    Route::get('/orders', [FrontCheckout::class, 'orderHistory'])->name('orders.index');
    Route::get('/order/{order}', [FrontCheckout::class, 'orderDetails'])->name('order.details');
    Route::get('/order/{order}/track', [FrontCheckout::class, 'trackOrder'])->name('order.track');
    Route::post('/order/{order}/cancel', [FrontCheckout::class, 'cancelOrder'])->name('order.cancel');
    Route::post('/order/{order}/reorder', [FrontCheckout::class, 'reorder'])->name('order.reorder');
    
    // Advanced Order Management Features
    Route::post('/order/{order}/return', [FrontCheckout::class, 'returnOrder'])->name('order.return');
    Route::post('/order/{order}/exchange', [FrontCheckout::class, 'exchangeOrder'])->name('order.exchange');
    Route::get('/order/{order}/invoice', [FrontCheckout::class, 'downloadInvoice'])->name('order.invoice');
    Route::get('/order/{order}/receipt', [FrontCheckout::class, 'downloadReceipt'])->name('order.receipt');

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
    Route::post('/coupon/remove', [FrontCoupon::class, 'remove'])->name('coupon.remove');

    // Address management routes
    Route::get('/address/{address}', [FrontCheckout::class, 'getAddress'])->name('address.get');
    Route::post('/address/store', [FrontCheckout::class, 'storeAddress'])->name('address.store');
    Route::put('/address/{address}', [FrontCheckout::class, 'updateAddress'])->name('address.update');
    Route::delete('/address/{address}', [FrontCheckout::class, 'deleteAddress'])->name('address.delete');
    Route::post('/address/{address}/set-default', [FrontCheckout::class, 'setDefaultAddress'])->name('address.set_default');
});

// Public search route (doesn't require authentication)
Route::get('/search', [FrontSearch::class, 'autocomplete'])->name('products.search');

// Sales routes (public access)
Route::prefix('sales')->name('sales.')->group(function () {
    Route::get('/', [App\Http\Controllers\Frontend\SaleController::class, 'index'])->name('index');
    Route::get('/{sale:slug}', [App\Http\Controllers\Frontend\SaleController::class, 'show'])->name('show');
    Route::get('/{sale:slug}/products', [App\Http\Controllers\Frontend\SaleController::class, 'products'])->name('products');
});

// Include admin routes
require __DIR__.'/admin.php';

// Test checkout route (remove in production)
Route::get('/checkout-demo', function () {
    return view('checkout.demo');
})->name('checkout.demo');

// Test success page (remove in production)
Route::get('/checkout-success-demo', function () {
    // Create a mock order object for demo
    $order = (object) [
        'id' => '12345',
        'total' => 4497.00,
        'payment_method' => 'cod',
        'created_at' => now(),
        'items' => collect([
            (object) [
                'quantity' => 2,
                'price' => 999.00,
                'productVariation' => (object) [
                    'sku' => 'CT-BLU-M-001',
                    'product' => (object) ['name' => 'Premium Cotton T-Shirt'],
                    'attribute_values' => collect([
                        (object) ['attribute' => (object) ['name' => 'Color'], 'value' => 'Blue'],
                        (object) ['attribute' => (object) ['name' => 'Size'], 'value' => 'M']
                    ])
                ]
            ],
            (object) [
                'quantity' => 1,
                'price' => 2499.00,
                'productVariation' => (object) [
                    'sku' => 'DJ-BLK-L-002',
                    'product' => (object) ['name' => 'Denim Jacket'],
                    'attribute_values' => collect([
                        (object) ['attribute' => (object) ['name' => 'Color'], 'value' => 'Black'],
                        (object) ['attribute' => (object) ['name' => 'Size'], 'value' => 'L']
                    ])
                ]
            ]
        ]),
        'address' => (object) [
            'name' => 'John Doe',
            'phone' => '+91 9876543210',
            'address_line' => '123 Main Street, Apartment 4B',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'zip' => '400001'
        ]
    ];
    
    return view('checkout.success', compact('order'));
})->name('checkout.success.demo');

// Test order placement route  
Route::get('/test-order-redirect', function() {
    return redirect()->route('checkout.success', ['order' => 1])->with('success', 'Test redirect working!');
})->name('test.order.redirect');

// Debug logs route
Route::get('/debug-logs', function() {
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        return '<pre>' . htmlspecialchars($logs) . '</pre>';
    }
    return 'No log file found';
})->name('debug.logs');

// Test order relationships
Route::get('/test-order-relationships', function() {
    try {
        $order = \App\Models\Order::with(['items.productVariation.product', 'address'])->first();
        if ($order) {
            $output = "<h3>Order Test: #{$order->id}</h3>";
            $output .= "<p>Status: {$order->status}</p>";
            $output .= "<p>Total: ₹" . number_format($order->total, 2) . "</p>";
            $output .= "<h4>Items ({$order->items->count()}):</h4>";
            
            foreach ($order->items as $item) {
                $output .= "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0;'>";
                $output .= "<strong>Product:</strong> " . $item->productVariation->product->name . "<br>";
                $output .= "<strong>SKU:</strong> " . $item->productVariation->sku . "<br>";
                $output .= "<strong>Quantity:</strong> " . $item->quantity . "<br>";
                $output .= "<strong>Price:</strong> ₹" . number_format($item->price, 2) . "<br>";
                $output .= "</div>";
            }
            
            $output .= "<br><a href='/orders'>Go to Orders Page</a>";
            return $output;
        } else {
            return "<h3>No orders found</h3><p>Please create an order first.</p>";
        }
    } catch (\Exception $e) {
        return "<h3>Error:</h3><p>" . $e->getMessage() . "</p>";
    }
})->name('test.order.relationships');

// Pages routes
Route::get('/about', [App\Http\Controllers\PagesController::class, 'about'])->name('pages.about');
Route::get('/faq', [App\Http\Controllers\PagesController::class, 'faq'])->name('pages.faq');
Route::get('/help', [App\Http\Controllers\PagesController::class, 'help'])->name('pages.help');
Route::get('/support', [App\Http\Controllers\PagesController::class, 'support'])->name('pages.support');
Route::get('/privacy-policy', [App\Http\Controllers\PagesController::class, 'privacy'])->name('pages.privacy');
Route::get('/terms-conditions', [App\Http\Controllers\PagesController::class, 'terms'])->name('pages.terms');
Route::get('/shipping-policy', [App\Http\Controllers\PagesController::class, 'shipping'])->name('pages.shipping');
Route::get('/return-refund-policy', [App\Http\Controllers\PagesController::class, 'returnRefund'])->name('pages.return.refund');
Route::get('/cookie-policy', [App\Http\Controllers\PagesController::class, 'cookiePolicy'])->name('pages.cookie.policy');
Route::get('/cookie-preferences', [App\Http\Controllers\PagesController::class, 'cookiePreferences'])->name('pages.cookie.preferences');
Route::get('/size-guide', [App\Http\Controllers\PagesController::class, 'sizeGuide'])->name('pages.size.guide');
Route::get('/product-care-guide', [App\Http\Controllers\PagesController::class, 'productCareGuide'])->name('pages.product.care');
Route::get('/lookbook', [App\Http\Controllers\PagesController::class, 'lookbook'])->name('pages.lookbook');
Route::get('/gallery', [App\Http\Controllers\PagesController::class, 'gallery'])->name('pages.gallery');
Route::get('/maintenance', [App\Http\Controllers\PagesController::class, 'maintenance'])->name('pages.maintenance');
Route::get('/sitemap', [App\Http\Controllers\PagesController::class, 'sitemap'])->name('pages.sitemap');
Route::get('/virtual-try-on', [App\Http\Controllers\PagesController::class, 'virtualTryOn'])->name('pages.virtual.try.on');
Route::get('/accessibility', [App\Http\Controllers\PagesController::class, 'accessibility'])->name('pages.accessibility');
Route::get('/security-data-protection', [App\Http\Controllers\PagesController::class, 'securityDataProtection'])->name('pages.security.data.protection');
Route::get('/ai-personal-shopper', [App\Http\Controllers\PagesController::class, 'aiPersonalShopper'])->name('pages.ai.personal.shopper');
Route::post('/ai-personal-shopper/recommendations', [App\Http\Controllers\PagesController::class, 'getAiRecommendations'])->name('pages.ai.recommendations');

// Sitemap XML route
Route::get('/sitemap.xml', [App\Http\Controllers\PagesController::class, 'sitemapXml'])->name('sitemap.xml');

// Custom 404 route (should be at the end)
Route::fallback([App\Http\Controllers\PagesController::class, 'error404']);

// Include email preview routes (only in development)
if (app()->environment('local', 'testing')) {
    require __DIR__.'/email-preview.php';
}

