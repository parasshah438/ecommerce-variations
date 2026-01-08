
<?php 
// Weight-based shipping routes
Route::prefix('api/shipping')->group(function () {
    Route::post('/calculate', [\App\Http\Controllers\Api\ShippingController::class, 'calculateShipping']);
    Route::post('/options', [\App\Http\Controllers\Api\ShippingController::class, 'getShippingOptions']);
    Route::post('/suggestions', [\App\Http\Controllers\Api\ShippingController::class, 'getOptimizationSuggestions']);
    Route::get('/weights/default', [\App\Http\Controllers\Api\ShippingController::class, 'getDefaultWeights']);
});

?>