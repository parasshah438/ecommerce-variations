<?php

use App\Models\Category;
use Illuminate\Support\Facades\Route;

// Debug route to check categories
Route::get('/admin/debug/categories', function () {
    $categories = Category::select('id', 'name')->get();
    
    return response()->json([
        'categories' => $categories,
        'count' => $categories->count(),
        'sample_names' => $categories->pluck('name')->take(5)->toArray()
    ]);
});