<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShiprocketController;

/*
|--------------------------------------------------------------------------
| Shiprocket API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for Shiprocket integration
|
*/

Route::middleware(['auth', 'admin', 'throttle:60,1'])
    ->prefix('admin/shiprocket')
    ->name('admin.shiprocket.')
    ->group(function () {
    
    // Health Check
    Route::get('/health', [ShiprocketController::class, 'healthCheck'])->name('health');
    
    // Dashboard
    Route::get('/dashboard', [ShiprocketController::class, 'getDashboard'])->name('dashboard');
    
    // Order Management
    Route::prefix('orders')->group(function () {
        Route::post('/', [ShiprocketController::class, 'createOrder'])->name('orders.create');
        Route::get('/{orderId}', [ShiprocketController::class, 'getOrder'])->name('orders.show');
        Route::delete('/cancel', [ShiprocketController::class, 'cancelOrders'])->name('orders.cancel');
    });
    
    // Courier Services
    Route::prefix('couriers')->group(function () {
        Route::post('/serviceability', [ShiprocketController::class, 'checkServiceability'])->name('couriers.serviceability');
        Route::post('/recommendations', [ShiprocketController::class, 'getCourierRecommendations'])->name('couriers.recommendations');
        Route::post('/generate-awb', [ShiprocketController::class, 'generateAwb'])->name('couriers.generate_awb');
    });
    
    // Shipment Management
    Route::prefix('shipments')->group(function () {
        Route::get('/{shipmentId}', [ShiprocketController::class, 'getShipment'])->name('shipments.show');
        Route::get('/track/{awbCode}', [ShiprocketController::class, 'trackShipment'])->name('shipments.track');
        Route::post('/track/bulk', [ShiprocketController::class, 'bulkTrack'])->name('shipments.track_bulk');
        Route::post('/generate-label', [ShiprocketController::class, 'generateLabel'])->name('shipments.generate_label');
        Route::post('/generate-manifest', [ShiprocketController::class, 'generateManifest'])->name('shipments.generate_manifest');
        Route::post('/performance-metrics', [ShiprocketController::class, 'getPerformanceMetrics'])->name('shipments.performance_metrics');
    });
    
    // Return Management
    Route::prefix('returns')->group(function () {
        Route::get('/', [ShiprocketController::class, 'getReturns'])->name('returns.index');
        Route::post('/', [ShiprocketController::class, 'createReturn'])->name('returns.create');
        Route::post('/check-eligibility', [ShiprocketController::class, 'checkReturnEligibility'])->name('returns.check_eligibility');
    });

});