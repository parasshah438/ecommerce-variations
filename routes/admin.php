<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\AttributeValueController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TaxSettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Models\Category;

// Debug route to check categories
Route::get('/debug/categories', function () {
    $categories = Category::select('id', 'name')->get();
    
    return response()->json([
        'categories' => $categories,
        'count' => $categories->count(),
        'sample_names' => $categories->pluck('name')->take(10)->toArray()
    ]);
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application.
|
*/

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    
    // Products Management
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
        Route::post('/{product}/duplicate', [ProductController::class, 'duplicate'])->name('duplicate');
        Route::post('/{product}/variations', [ProductController::class, 'storeVariation'])->name('variations.store');
        Route::delete('/variations/{variation}', [ProductController::class, 'destroyVariation'])->name('variations.destroy');
        Route::post('/{product}/images', [ProductController::class, 'storeImage'])->name('images.store');
        Route::delete('/images/{image}', [ProductController::class, 'destroyImage'])->name('images.destroy');
    });
    
    // AJAX routes for dynamic loading
    Route::get('/api/attributes/{attribute}/values', [ProductController::class, 'getAttributeValues'])->name('api.attributes.values');
    Route::post('/api/variations/preview', [ProductController::class, 'previewVariations'])->name('api.variations.preview');
    
    // Attributes Management
    Route::resource('attributes', AttributeController::class);
    Route::resource('attribute-values', AttributeValueController::class);
    
    // Categories Management
    Route::resource('categories', CategoryController::class);
    Route::delete('/categories/{category}/remove-image', [CategoryController::class, 'removeImage'])->name('categories.remove_image');
    
    // Sliders Management
    Route::prefix('sliders')->name('sliders.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SliderController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\SliderController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\SliderController::class, 'store'])->name('store');
        
        // AJAX DataTables endpoint - MUST come before parameterized routes
        Route::match(['GET', 'POST'], '/data', [\App\Http\Controllers\Admin\SliderController::class, 'data'])->name('data');
        
        // AJAX CRUD endpoints - MUST come before parameterized routes  
        Route::post('/bulk-action', [\App\Http\Controllers\Admin\SliderController::class, 'bulkAction'])->name('bulk-action');
        
        // Parameterized routes - MUST come last
        Route::get('/{slider}', [\App\Http\Controllers\Admin\SliderController::class, 'show'])->name('show');
        Route::get('/{slider}/edit', [\App\Http\Controllers\Admin\SliderController::class, 'edit'])->name('edit');
        Route::put('/{slider}', [\App\Http\Controllers\Admin\SliderController::class, 'update'])->name('update');
        Route::delete('/{slider}', [\App\Http\Controllers\Admin\SliderController::class, 'destroy'])->name('destroy');
        Route::post('/{slider}/toggle-status', [\App\Http\Controllers\Admin\SliderController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{slider}/remove-image', [\App\Http\Controllers\Admin\SliderController::class, 'removeImage'])->name('remove_image');
    });
    
    // Order Management Routes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [AdminOrderController::class, 'index'])->name('index');
        Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
        
        // Status management
        Route::post('/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('update_status');
        Route::post('/{order}/confirm', [AdminOrderController::class, 'confirmOrder'])->name('confirm');
        Route::post('/{order}/cancel', [AdminOrderController::class, 'cancelOrder'])->name('cancel');
        Route::post('/{order}/return', [AdminOrderController::class, 'returnOrder'])->name('return');
        
        // Payment management
        Route::post('/{order}/mark-paid', [AdminOrderController::class, 'markAsPaid'])->name('mark_paid');
        
        // Bulk operations
        Route::post('/bulk-status-update', [AdminOrderController::class, 'bulkStatusUpdate'])->name('bulk_status_update');
        
        // Export and reports
        Route::get('/export', [AdminOrderController::class, 'export'])->name('export');
        Route::get('/{order}/invoice', [AdminOrderController::class, 'downloadInvoice'])->name('invoice');
        
        // Email management
        Route::post('/{order}/send-email', [AdminOrderController::class, 'sendOrderEmail'])->name('send_email');
        
        // AJAX endpoints
        Route::get('/api/statistics', [AdminOrderController::class, 'getOrderStatistics'])->name('statistics');
    });
    
    // Payment Management Routes
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    
    // Email Log Management Routes
    Route::get('/email-logs', [\App\Http\Controllers\Admin\EmailLogController::class, 'index'])->name('email-logs.index');
    Route::get('/email-logs/{emailLog}', [\App\Http\Controllers\Admin\EmailLogController::class, 'show'])->name('email-logs.show');
    Route::get('/email-logs/{emailLog}/retry', [\App\Http\Controllers\Admin\EmailLogController::class, 'retry'])->name('email-logs.retry');
    Route::get('/email-logs/retry-all', [\App\Http\Controllers\Admin\EmailLogController::class, 'retryAll'])->name('email-logs.retry-all');
    Route::get('/email-logs/process-retry-queue', [\App\Http\Controllers\Admin\EmailLogController::class, 'processRetryQueue'])->name('email-logs.process-retry-queue');
    Route::get('/email-logs/{emailLog}/delete', [\App\Http\Controllers\Admin\EmailLogController::class, 'delete'])->name('email-logs.delete');
    Route::post('/email-logs/bulk-delete', [\App\Http\Controllers\Admin\EmailLogController::class, 'bulkDelete'])->name('email-logs.bulk-delete');
    Route::get('/email-logs/export', [\App\Http\Controllers\Admin\EmailLogController::class, 'export'])->name('email-logs.export');
    
    // Stock Management Dashboard Routes
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\StockDashboardController::class, 'index'])->name('dashboard');
        Route::get('/low-stock-report', [\App\Http\Controllers\Admin\StockDashboardController::class, 'lowStockReport'])->name('low_stock_report');
        Route::get('/out-of-stock-report', [\App\Http\Controllers\Admin\StockDashboardController::class, 'outOfStockReport'])->name('out_of_stock_report');
        Route::get('/stock-movement-report', [\App\Http\Controllers\Admin\StockDashboardController::class, 'stockMovementReport'])->name('movement_report');
        Route::post('/bulk-stock-update', [\App\Http\Controllers\Admin\StockDashboardController::class, 'bulkStockUpdate'])->name('bulk_update');
        Route::get('/api/stock-alerts', [\App\Http\Controllers\Admin\StockDashboardController::class, 'getStockAlerts'])->name('api.alerts');
        Route::post('/api/update-stock/{variation}', [\App\Http\Controllers\Admin\StockDashboardController::class, 'updateStock'])->name('api.update');
        Route::get('/export', [\App\Http\Controllers\Admin\StockDashboardController::class, 'exportStock'])->name('export');
    });
    
    // Sales Management Routes
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SaleController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\SaleController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\SaleController::class, 'store'])->name('store');
        Route::get('/{sale}', [\App\Http\Controllers\Admin\SaleController::class, 'show'])->name('show');
        Route::get('/{sale}/edit', [\App\Http\Controllers\Admin\SaleController::class, 'edit'])->name('edit');
        Route::put('/{sale}', [\App\Http\Controllers\Admin\SaleController::class, 'update'])->name('update');
        Route::delete('/{sale}', [\App\Http\Controllers\Admin\SaleController::class, 'destroy'])->name('destroy');
        Route::post('/{sale}/toggle-status', [\App\Http\Controllers\Admin\SaleController::class, 'toggleStatus'])->name('toggle_status');
        
        // AJAX endpoints for product management
        Route::get('/api/search-products', [\App\Http\Controllers\Admin\SaleController::class, 'searchProducts'])->name('search-products');
        Route::get('/api/get-product', [\App\Http\Controllers\Admin\SaleController::class, 'getProduct'])->name('get-product');
        Route::post('/api/products-by-category', [\App\Http\Controllers\Admin\SaleController::class, 'productsByCategory'])->name('products-by-category');
    });

    // Tax Settings Routes
    Route::prefix('tax-settings')->name('tax-settings.')->group(function () {
        Route::get('/', [TaxSettingsController::class, 'index'])->name('index');
        Route::put('/', [TaxSettingsController::class, 'update'])->name('update');
        Route::post('/test-calculation', [TaxSettingsController::class, 'testCalculation'])->name('test');
    });

    // User Management Routes
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'delete_user'])->name('destroy');
        Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/bulk-action', [UserController::class, 'bulkAction'])->name('bulk-action');
        
        // AJAX DataTables endpoint
        Route::post('/data', [UserController::class, 'manage_user'])->name('data');
        
        // AJAX CRUD endpoints
        Route::post('/ajax/view', [UserController::class, 'view_user'])->name('ajax.view');
        Route::post('/ajax/delete', [UserController::class, 'delete_user'])->name('ajax.delete');
        Route::post('/ajax/delete-multiple', [UserController::class, 'delete_all_user'])->name('ajax.delete-multiple');
        Route::post('/ajax/toggle-status', [UserController::class, 'toggleStatus'])->name('ajax.toggle-status');
        
        // Export routes
        Route::get('/{user}/pdf', [UserController::class, 'exportPdf'])->name('pdf');
        Route::get('/{user}/excel', [UserController::class, 'exportExcel'])->name('excel');
        Route::get('/export/all', [UserController::class, 'exportAllUsers'])->name('export.all');
        
        // User details for printing
        Route::get('/{user}/details', [UserController::class, 'user_all_details'])->name('details');
        Route::get('/{user}/details/export', [UserController::class, 'user_all_details_export'])->name('details.export');
        
        // Ecommerce-specific routes
        Route::get('/{user}/ecommerce-details', [UserController::class, 'getEcommerceDetails'])->name('ecommerce.details');
        Route::get('/{user}/orders', [UserController::class, 'getUserOrders'])->name('orders');
        Route::get('/{user}/wishlist', [UserController::class, 'getUserWishlist'])->name('wishlist');
        Route::get('/{user}/cart', [UserController::class, 'getUserCart'])->name('cart');
        
        Route::get('/{user}/payments', [UserController::class, 'getUserPayments'])->name('payments');
        Route::post('/{user}/send-email', [UserController::class, 'sendUserEmail'])->name('send-email');
    });

    // User Activity Management Routes  
    Route::prefix('user-activities')->name('user-activities.')->group(function () {
        Route::get('/', [UserController::class, 'activity_logs'])->name('index');
        Route::post('/data', [UserController::class, 'manage_activity_logs'])->name('data');
        Route::post('/view', [UserController::class, 'view_user_activity'])->name('view');
        Route::post('/delete', [UserController::class, 'delete_user_activity'])->name('delete');
        Route::post('/delete-multiple', [UserController::class, 'delete_all_user_activity'])->name('delete-multiple');
    });

    // Cache Management Routes (Admin Only)
    Route::prefix('cache')->name('cache.')->middleware('admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CacheManagementController::class, 'index'])->name('index');
        Route::post('/clear', [\App\Http\Controllers\Admin\CacheManagementController::class, 'clearCache'])->name('clear');
        Route::post('/clear-specific', [\App\Http\Controllers\Admin\CacheManagementController::class, 'clearSpecificCache'])->name('clear-specific');
        Route::get('/logs', [\App\Http\Controllers\Admin\CacheManagementController::class, 'getLogs'])->name('logs');
    });
});
