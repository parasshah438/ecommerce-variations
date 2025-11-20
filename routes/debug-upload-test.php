<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/debug/upload-test', function () {
    return view('debug.upload-test');
});

Route::post('/debug/upload-test', function (Request $request) {
    try {
        // Enhanced debug info
        $debugInfo = [
            'has_files' => $request->hasFile('images'),
            'all_files' => $request->allFiles(),
            'content_length' => $request->header('content-length'),
            'content_type' => $request->header('content-type'),
            'method' => $request->method(),
            'all_input' => $request->all(),
            'file_keys' => array_keys($request->allFiles()),
            'php_max_filesize' => ini_get('upload_max_filesize'),
            'php_post_max_size' => ini_get('post_max_size'),
            'php_max_file_uploads' => ini_get('max_file_uploads'),
            'server_vars' => [
                'CONTENT_LENGTH' => $_SERVER['CONTENT_LENGTH'] ?? 'not set',
                'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'not set',
                'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? 'not set'
            ]
        ];
        
        \Log::info('Enhanced upload test debug info', $debugInfo);

        // Check for different field names
        $hasImages = $request->hasFile('images');
        $hasTestImage = $request->hasFile('test_image'); 
        
        if (!$hasImages && !$hasTestImage) {
            return response()->json([
                'error' => 'No files uploaded',
                'debug_info' => $debugInfo,
                'possible_causes' => [
                    'File too large for server limits (effective limit appears to be ~2MB)',
                    'Incorrect form field name (should be images[] or test_image)',
                    'Form not using multipart/form-data',
                    'Server rejecting upload before PHP processes it',
                    'CSRF token missing or invalid',
                    'JavaScript form submission issue'
                ]
            ]);
        }
        
        // Handle single test image
        if ($hasTestImage) {
            $file = $request->file('test_image');
            $results = [[
                'field_name' => 'test_image',
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'error_code' => $file->getError(),
                'is_valid' => $file->isValid()
            ]];
            
            return response()->json([
                'success' => true,
                'message' => 'Single file test',
                'files' => $results
            ]);
        }

        $results = [];
        foreach ($request->file('images') as $index => $file) {
            // Check if file is valid first
            if (!$file || !$file->isValid()) {
                $results[] = [
                    'index' => $index,
                    'error' => 'Invalid file or upload error',
                    'error_code' => $file ? $file->getError() : 'No file object',
                    'is_valid' => false
                ];
                continue;
            }
            
            $fileInfo = [
                'index' => $index,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
                'error_code' => $file->getError(),
                'is_valid' => $file->isValid(),
                'temp_path' => $file->getPathname()
            ];
            
            // Safely get mime type
            try {
                $fileInfo['mime_type'] = $file->getMimeType();
            } catch (\Exception $e) {
                $fileInfo['mime_type'] = 'unknown';
                $fileInfo['mime_error'] = $e->getMessage();
            }

            // Try to store the file
            try {
                $path = $file->store('debug-uploads', 'public');
                $fileInfo['storage_success'] = true;
                $fileInfo['stored_path'] = $path;
                
                // Verify file exists
                $fullPath = storage_path('app/public/' . $path);
                $fileInfo['file_exists'] = file_exists($fullPath);
                $fileInfo['stored_size'] = file_exists($fullPath) ? filesize($fullPath) : 0;
            } catch (\Exception $e) {
                $fileInfo['storage_success'] = false;
                $fileInfo['storage_error'] = $e->getMessage();
            }

            $results[] = $fileInfo;
        }

        return response()->json([
            'success' => true,
            'files' => $results
        ]);

    } catch (\Exception $e) {
        \Log::error('Upload test error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

Route::get('/debug/test-product-creation', function () {
    return view('debug.product-test');
});

Route::post('/debug/test-product-creation', function (Request $request) {
    try {
        // Log all request data
        \Log::info('Product creation test request', [
            'has_files' => $request->hasFile('images'),
            'files_count' => $request->hasFile('images') ? count($request->file('images')) : 0,
            'all_data' => $request->except(['_token']),
            'files_info' => $request->hasFile('images') ? 
                collect($request->file('images'))->map(function($file) {
                    return [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime' => $file->getMimeType(),
                        'error' => $file->getError(),
                        'valid' => $file->isValid()
                    ];
                })->toArray() : []
        ]);

        // Test validation only
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|integer|min:1',
            'brand_id' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'images.*.image' => 'Each image must be a valid image file.',
            'images.*.mimes' => 'Each image must be a file of type: jpeg, png, jpg, gif, webp.',
            'images.*.max' => 'Each image may not be greater than 5MB.',
        ]);
        
        \Log::info('Validation passed successfully', $validated);
        
        return redirect('/debug/test-product-creation')->with('success', 'Validation passed! Check logs for details.');
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('Validation failed', [
            'errors' => $e->errors(),
            'failed_rules' => $e->validator->failed()
        ]);
        
        return redirect('/debug/test-product-creation')
            ->withErrors($e->validator)
            ->withInput();
            
    } catch (\Exception $e) {
        \Log::error('Unexpected error in product creation test', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect('/debug/test-product-creation')
            ->withErrors(['error' => 'Unexpected error: ' . $e->getMessage()])
            ->withInput();
    }
});

Route::get('/debug/limits-test', function () {
    return view('debug.limits-test');
})->name('debug.limits-test');

Route::post('/debug/single-file-test', function (Request $request) {
    try {
        if (!$request->hasFile('single_image')) {
            return response()->json(['error' => 'No file uploaded']);
        }

        $file = $request->file('single_image');
        
        $result = [
            'success' => true,
            'file_info' => [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'error_code' => $file->getError(),
                'is_valid' => $file->isValid(),
                'temp_path' => $file->getPathname()
            ]
        ];
        
        // Try to store the file
        try {
            $path = $file->store('debug-single', 'public');
            $result['storage'] = [
                'success' => true,
                'path' => $path,
                'full_path' => storage_path('app/public/' . $path),
                'file_exists' => file_exists(storage_path('app/public/' . $path))
            ];
        } catch (\Exception $e) {
            $result['storage'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        return response()->json($result);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

Route::get('/debug/basic-upload-test', function () {
    return view('debug.basic-upload-test');
});

Route::post('/debug/basic-upload-test', function (Request $request) {
    try {
        $debugInfo = [
            'method' => $request->method(),
            'content_type' => $request->header('content-type'),
            'content_length' => $request->header('content-length'),
            'all_files' => $request->allFiles(),
            'has_test_image' => $request->hasFile('test_image'),
            'php_limits' => [
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size')
            ]
        ];
        
        if (!$request->hasFile('test_image')) {
            return back()->with('error', 'No file was uploaded')->with('debug', $debugInfo);
        }
        
        $file = $request->file('test_image');
        
        $result = [
            'success' => true,
            'file_info' => [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize() . ' bytes (' . round($file->getSize()/1024, 2) . ' KB)',
                'mime' => $file->getMimeType(),
                'error_code' => $file->getError(),
                'is_valid' => $file->isValid()
            ],
            'debug' => $debugInfo
        ];
        
        // Try to store the file
        if ($file->isValid()) {
            try {
                $path = $file->store('debug-basic', 'public');
                $result['storage'] = [
                    'success' => true,
                    'path' => $path
                ];
                return back()->with('success', 'File uploaded successfully!')->with('result', $result);
            } catch (\Exception $e) {
                $result['storage'] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
                return back()->with('error', 'Storage failed: ' . $e->getMessage())->with('result', $result);
            }
        } else {
            return back()->with('error', 'Invalid file upload')->with('result', $result);
        }
        
    } catch (\Exception $e) {
        return back()->with('error', 'Exception: ' . $e->getMessage());
    }
});