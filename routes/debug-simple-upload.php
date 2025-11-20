<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/debug/simple-upload-test', function() {
    return view('debug.simple-upload-test');
});

Route::post('/debug/simple-upload', function(Request $request) {
    try {
        \Log::info('Simple upload test started');
        
        // Basic validation
        $request->validate([
            'test_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:8192'
        ]);
        
        $file = $request->file('test_image');
        
        // Log file info
        \Log::info('File info', [
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
            'is_valid' => $file->isValid(),
            'error' => $file->getError(),
            'temp_path' => $file->getPathname()
        ]);
        
        // Test 1: Basic store
        $path1 = $file->store('test-uploads', 'public');
        \Log::info('Basic store result: ' . $path1);
        
        // Test 2: Store with custom name
        $filename = 'test_' . time() . '.' . $file->getClientOriginalExtension();
        $path2 = $file->storeAs('test-uploads', $filename, 'public');
        \Log::info('Custom name store result: ' . $path2);
        
        // Test 3: Check if files exist
        $exists1 = \Storage::disk('public')->exists($path1);
        $exists2 = \Storage::disk('public')->exists($path2);
        
        \Log::info('File existence check', [
            'path1_exists' => $exists1,
            'path2_exists' => $exists2
        ]);
        
        // Clean up
        if ($exists1) \Storage::disk('public')->delete($path1);
        if ($exists2) \Storage::disk('public')->delete($path2);
        
        return response()->json([
            'success' => true,
            'message' => 'Simple upload test successful',
            'results' => [
                'basic_store' => $path1,
                'custom_store' => $path2,
                'files_existed' => $exists1 && $exists2
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Simple upload test failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});