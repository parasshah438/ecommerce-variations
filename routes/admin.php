<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application.
|
*/

Route::prefix('admin')->name('admin.')->group(function () {
    
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
});
