<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Helpers\ImageOptimizer;

Route::get('/debug/large-upload-test', function () {
    return view('debug.large-upload-test');
});

Route::post('/debug/large-upload-test', function (Request $request) {
    try {
        \Log::info('Large upload test started', [
            'has_files' => $request->hasFile('test_images'),
            'php_limits' => [
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time')
            ]
        ]);

        if (!$request->hasFile('test_images')) {
            return response()->json(['error' => 'No files uploaded']);
        }

        $results = [];
        foreach ($request->file('test_images') as $index => $file) {
            $fileInfo = [
                'index' => $index,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'size_mb' => round($file->getSize() / (1024 * 1024), 2),
                'mime_type' => $file->getMimeType(),
                'upload_error' => $file->getError(),
                'is_valid' => $file->isValid(),
                'temp_path' => $file->getPathname()
            ];

            try {
                // Test our enhanced upload handling
                $optimizationResult = ImageOptimizer::handleLargeFileUpload($file, 'test-large-uploads', [
                    'quality' => 85,
                    'maxWidth' => 1600,
                    'maxHeight' => 1600,
                    'generateWebP' => false, // Skip WebP for testing speed
                    'generateThumbnails' => false, // Skip thumbnails for testing
                    'max_attempts' => 2
                ]);

                $fileInfo['processing'] = [
                    'success' => true,
                    'method_used' => $optimizationResult['method'] ?? 'optimization',
                    'fallback_used' => $optimizationResult['fallback_used'] ?? false,
                    'simple_store' => $optimizationResult['simple_store'] ?? false,
                    'stored_path' => $optimizationResult['optimized'],
                    'file_exists' => file_exists(storage_path('app/public/' . $optimizationResult['optimized']))
                ];

                if (isset($optimizationResult['error'])) {
                    $fileInfo['processing']['optimization_error'] = $optimizationResult['error'];
                }

            } catch (\Exception $e) {
                $fileInfo['processing'] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ];
            }

            $results[] = $fileInfo;
        }

        return response()->json([
            'success' => true,
            'files' => $results,
            'summary' => [
                'total_files' => count($results),
                'successful' => count(array_filter($results, fn($r) => $r['processing']['success'] ?? false)),
                'failed' => count(array_filter($results, fn($r) => !($r['processing']['success'] ?? true)))
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Large upload test error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});