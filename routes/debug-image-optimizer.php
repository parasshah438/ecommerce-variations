<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Helpers\ImageOptimizer;

Route::get('/debug/image-optimizer-status', function () {
    try {
        $status = ImageOptimizer::checkOptimizerStatus();
        return response()->json([
            'status' => 'success',
            'optimizers' => $status
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

Route::post('/debug/test-image-upload', function (Request $request) {
    try {
        if (!$request->hasFile('image')) {
            return response()->json(['error' => 'No image file provided']);
        }

        $file = $request->file('image');
        
        // Test basic file info
        $fileInfo = [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension()
        ];
        
        // Test simple storage first
        try {
            $simplePath = $file->store('debug-test', 'public');
            $fileInfo['simple_storage'] = [
                'success' => true,
                'path' => $simplePath
            ];
        } catch (\Exception $e) {
            $fileInfo['simple_storage'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        // Test optimization
        try {
            $optimizationResult = ImageOptimizer::optimizeUploadedImage(
                $file, 
                'debug-test-optimized', 
                [
                    'quality' => 85,
                    'maxWidth' => 800,
                    'maxHeight' => 800,
                    'generateWebP' => false,
                    'generateThumbnails' => false
                ]
            );
            $fileInfo['optimization'] = [
                'success' => true,
                'result' => $optimizationResult
            ];
        } catch (\Exception $e) {
            $fileInfo['optimization'] = [
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
        
        return response()->json([
            'status' => 'success',
            'file_info' => $fileInfo
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});