<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Image Optimization Settings - GoDaddy Shared Hosting Optimized
    |--------------------------------------------------------------------------
    */
    
    'quality' => [
        'high' => 90,      // Premium products (reduced for faster processing)
        'standard' => 80,  // Regular products (balanced quality/speed)
        'thumbnail' => 70, // Thumbnails (smaller files)
    ],

    'sizes' => [
        'max_width' => 1600,  // Reduced for shared hosting limits
        'max_height' => 1600, // Reduced for shared hosting limits
        'thumbnails' => [150, 300, 600], // Removed 900px to speed up processing
    ],

    'formats' => [
        'webp' => env('WEBP_ENABLED', true),
        'avif' => false, // Disabled for shared hosting compatibility
        'original' => true,
    ],

    'optimization' => [
        'max_file_size' => 3145728, // 3MB limit for shared hosting
        'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'memory_limit_mb' => 256,   // Reduced for shared hosting
        'timeout_seconds' => 50,    // Reduced for shared hosting limits
    ],

    'queue' => [
        'enabled' => env('IMAGE_QUEUE_ENABLED', true),
        'queue_name' => env('IMAGE_QUEUE_NAME', 'images'),
        'size_threshold' => 524288, // 512KB - smaller threshold for shared hosting
        'batch_size' => env('QUEUE_BATCH_SIZE', 5), // Process max 5 images per batch
    ],

    'cache' => [
        'enabled' => env('IMAGE_CACHE_ENABLED', true),
        'ttl' => 86400 * 7, // 7 days (shorter for shared hosting)
        'driver' => env('IMAGE_CACHE_DRIVER', 'file'), // File cache for shared hosting
    ],

    'cdn' => [
        'enabled' => env('CDN_ENABLED', false),
        'url' => env('CDN_URL', ''),
        'path_prefix' => env('CDN_PATH_PREFIX', 'images'),
    ],

    'binaries' => [
        'jpegoptim' => env('JPEGOPTIM_PATH', '/usr/bin/jpegoptim'),
        'optipng' => env('OPTIPNG_PATH', '/usr/bin/optipng'),
        'pngquant' => env('PNGQUANT_PATH', '/usr/bin/pngquant'),
        'gifsicle' => env('GIFSICLE_PATH', '/usr/bin/gifsicle'),
    ],

    // GoDaddy Shared Hosting Specific Settings
    'godaddy' => [
        'max_execution_time' => 50,     // Safe execution limit
        'memory_safety_margin' => 0.8, // Use 80% of available memory
        'concurrent_limit' => 1,        // Process one image at a time
        'fallback_to_sync' => true,     // Auto fallback if queue fails
    ],
];