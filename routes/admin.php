<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;

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
        
        // Variation management
        Route::post('/{product}/variations', [ProductController::class, 'storeVariation'])->name('variations.store');
        Route::delete('/variations/{variation}', [ProductController::class, 'destroyVariation'])->name('variations.destroy');
        
        // Image management
        Route::post('/{product}/images', [ProductController::class, 'storeImage'])->name('images.store');
        Route::delete('/images/{image}', [ProductController::class, 'destroyImage'])->name('images.destroy');
    });
    
    // AJAX routes for dynamic loading
    Route::get('/api/attributes/{attribute}/values', [ProductController::class, 'getAttributeValues'])->name('api.attributes.values');
    Route::post('/api/variations/preview', [ProductController::class, 'previewVariations'])->name('api.variations.preview');
    
    // Order Management Routes
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
