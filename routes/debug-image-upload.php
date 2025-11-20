<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Helpers\ImageOptimizer;

Route::get('/debug/image-upload-test', function() {
    return view('debug.image-upload-test');
});

Route::post('/debug/test-image-optimization', function(Request $request) {
    try {
        \Log::info('Debug image optimization test started');
        
        // Validate request
        $request->validate([
            'test_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:8192'
        ]);
        
        $file = $request->file('test_image');
        
        // Log file details
        \Log::info('Test file details', [
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
            'is_valid' => $file->isValid(),
            'error' => $file->getError(),
            'temp_path' => $file->getPathname()
        ]);
        
        // Test basic storage first
        try {
            $basicPath = $file->store('debug-test', 'public');
            \Log::info('Basic storage successful: ' . $basicPath);
            
            // Delete test file
            if ($basicPath && \Storage::disk('public')->exists($basicPath)) {
                \Storage::disk('public')->delete($basicPath);
                \Log::info('Test file cleaned up');
            }
            
        } catch (\Exception $e) {
            \Log::error('Basic storage failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Basic storage failed: ' . $e->getMessage(),
                'stage' => 'basic_storage'
            ]);
        }
        
        // Reset file pointer for optimization test
        $file = $request->file('test_image');
        
        // Test optimization
        try {
            $result = ImageOptimizer::optimizeUploadedImage(
                $file,
                'debug-test',
                [
                    'quality' => 88,
                    'maxWidth' => 1200,
                    'maxHeight' => 1200,
                    'generateWebP' => true,
                    'generateThumbnails' => false
                ]
            );
            
            \Log::info('Optimization result', $result);
            
            // Clean up test files
            if (isset($result['optimized'])) {
                \Storage::disk('public')->delete($result['optimized']);
            }
            if (isset($result['webp'])) {
                \Storage::disk('public')->delete($result['webp']);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Image optimization test successful',
                'result' => $result
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Optimization failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Optimization failed: ' . $e->getMessage(),
                'stage' => 'optimization'
            ]);
        }
        
    } catch (\Exception $e) {
        \Log::error('Debug test failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'stage' => 'validation'
        ]);
    }
});