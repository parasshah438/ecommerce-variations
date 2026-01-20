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

Route::prefix('shiprocket')->group(function () {
    
    // Health Check
    Route::get('/health', [ShiprocketController::class, 'healthCheck']);
    
    // Dashboard
    Route::get('/dashboard', [ShiprocketController::class, 'getDashboard']);
    
    // Order Management
    Route::prefix('orders')->group(function () {
        Route::post('/', [ShiprocketController::class, 'createOrder']);
        Route::get('/{orderId}', [ShiprocketController::class, 'getOrder']);
        Route::delete('/cancel', [ShiprocketController::class, 'cancelOrders']);
    });
    
    // Courier Services
    Route::prefix('couriers')->group(function () {
        Route::post('/serviceability', [ShiprocketController::class, 'checkServiceability']);
        Route::post('/recommendations', [ShiprocketController::class, 'getCourierRecommendations']);
        Route::post('/generate-awb', [ShiprocketController::class, 'generateAwb']);
    });
    
    // Shipment Management
    Route::prefix('shipments')->group(function () {
        Route::get('/{shipmentId}', [ShiprocketController::class, 'getShipment']);
        Route::get('/track/{awbCode}', [ShiprocketController::class, 'trackShipment']);
        Route::post('/track/bulk', [ShiprocketController::class, 'bulkTrack']);
        Route::post('/generate-label', [ShiprocketController::class, 'generateLabel']);
        Route::post('/generate-manifest', [ShiprocketController::class, 'generateManifest']);
        Route::post('/performance-metrics', [ShiprocketController::class, 'getPerformanceMetrics']);
    });
    
    // Return Management
    Route::prefix('returns')->group(function () {
        Route::get('/', [ShiprocketController::class, 'getReturns']);
        Route::post('/', [ShiprocketController::class, 'createReturn']);
        Route::post('/check-eligibility', [ShiprocketController::class, 'checkReturnEligibility']);
    });
    
});