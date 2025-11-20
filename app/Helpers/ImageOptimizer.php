<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Illuminate\Http\UploadedFile;

class ImageOptimizer
{
    /**
     * Get Image Manager instance
     */
    private static function getImageManager()
    {
        return new ImageManager(new Driver());
    }
    /**
     * Optimize uploaded image using Spatie Image Optimizer
     */
    public static function optimizeUploadedImage(UploadedFile $file, string $directory = 'uploads', array $options = [])
    {
        // Enhanced upload validation and debugging
        $fileSize = $file->getSize();
        $uploadError = $file->getError();
        
        // Log upload attempt details
        \Log::info('ImageOptimizer: Processing upload', [
            'original_name' => $file->getClientOriginalName(),
            'size_bytes' => $fileSize,
            'size_mb' => round($fileSize / (1024 * 1024), 2),
            'mime_type' => $file->getMimeType(),
            'upload_error' => $uploadError,
            'is_valid' => $file->isValid(),
            'php_upload_max' => ini_get('upload_max_filesize'),
            'php_post_max' => ini_get('post_max_size')
        ]);
        
        // Check for upload errors first
        if ($uploadError !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            
            $errorMessage = $errorMessages[$uploadError] ?? 'Unknown upload error';
            \Log::error('ImageOptimizer: Upload error detected', [
                'error_code' => $uploadError,
                'error_message' => $errorMessage,
                'file_size' => $fileSize,
                'file_name' => $file->getClientOriginalName()
            ]);
            
            throw new \Exception("Upload error: {$errorMessage} (Code: {$uploadError})");
        }
        
        // Validate file is valid
        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload detected');
        }
        
        // Memory management for large files
        $originalMemoryLimit = ini_get('memory_limit');
        
        // Increase memory limit for files larger than 1MB (since we now handle up to 5MB)
        if ($fileSize > 1024 * 1024) {
            // Calculate required memory based on file size (image processing needs ~5x file size for 5MB images)
            $requiredMemory = max(512, ($fileSize * 6) / (1024 * 1024)); // At least 512MB for 5MB images
            ini_set('memory_limit', $requiredMemory . 'M');
            set_time_limit(600); // 10 minutes for large files (up to 5MB)
        }
        
        $options = array_merge([
            'quality' => 85,
            'maxWidth' => 1600, // Increased for 5MB images
            'maxHeight' => 1600, // Increased for 5MB images
            'generateWebP' => true,
            'generateThumbnails' => true,
            'thumbnailSizes' => [150, 300, 600, 900], // Added 900px thumbnail for larger images
            'keepOriginal' => false,
            'preserveAspectRatio' => true,
        ], $options);

        try {
            // Validate file type and size (now supporting up to 5MB)
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
                throw new \Exception('Unsupported file type: ' . $file->getMimeType());
            }
            
            // Additional validation for 5MB limit
            $maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
            if ($fileSize > $maxFileSize) {
                throw new \Exception('File size exceeds 5MB limit. Current size: ' . round($fileSize / (1024 * 1024), 2) . 'MB');
            }

            // Generate unique filename
            $extension = strtolower($file->getClientOriginalExtension());
            $filename = uniqid() . '_' . time();
            $originalPath = $directory . '/' . $filename . '.' . $extension;
            
            // Ensure directory exists
            $fullDirectoryPath = storage_path('app/public/' . $directory);
            if (!is_dir($fullDirectoryPath)) {
                mkdir($fullDirectoryPath, 0755, true);
            }
            
            // Store the original file temporarily
            $tempPath = $file->storeAs($directory, $filename . '.' . $extension, 'public');
            $fullPath = storage_path('app/public/' . $tempPath);
            
            // Verify file was stored successfully
            if (!file_exists($fullPath)) {
                throw new \Exception('Failed to store uploaded file at: ' . $fullPath);
            }

            // Get file info for logging
            $originalSize = filesize($fullPath);
            
            // Initialize optimizer chain
            $optimizerChain = OptimizerChainFactory::create();
            
            // Check if any optimizers are available and optimize only if binaries exist
            try {
                $hasOptimizers = false;
                $optimizers = $optimizerChain->getOptimizers();
                foreach ($optimizers as $optimizer) {
                    $binaryName = method_exists($optimizer, 'binaryName') ? $optimizer->binaryName() : null;
                    if ($binaryName) {
                        $checkCommand = PHP_OS_FAMILY === 'Windows' ? "where {$binaryName} 2>nul" : "which {$binaryName} 2>/dev/null";
                        $output = shell_exec($checkCommand);
                        if (!empty(trim($output))) {
                            $hasOptimizers = true;
                            break;
                        }
                    }
                }
                
                if ($hasOptimizers) {
                    $optimizerChain->optimize($fullPath);
                } else {
                    \Log::info('No image optimizer binaries found, using Intervention Image only for optimization');
                }
            } catch (\Exception $e) {
                \Log::warning('Spatie optimizer failed, continuing with Intervention Image only: ' . $e->getMessage());
            }
            
            // Process with Intervention Image for resizing and format conversion
            $manager = self::getImageManager();
            $image = $manager->read($fullPath);
            
            // Get original dimensions
            $originalWidth = $image->width();
            $originalHeight = $image->height();
            
            $needsResize = $originalWidth > $options['maxWidth'] || $originalHeight > $options['maxHeight'];
            
            // Resize if needed
            if ($needsResize) {
                if ($options['preserveAspectRatio']) {
                    $image->scaleDown($options['maxWidth'], $options['maxHeight']);
                } else {
                    $image->resize($options['maxWidth'], $options['maxHeight']);
                }
                
                // Save resized image
                if (in_array($extension, ['jpg', 'jpeg'])) {
                    $image->toJpeg($options['quality'])->save($fullPath);
                } elseif ($extension === 'png') {
                    $image->toPng()->save($fullPath);
                } else {
                    $image->toJpeg($options['quality'])->save($fullPath);
                }
                
                // Re-optimize after resizing (only if optimizers are available)
                if ($hasOptimizers) {
                    try {
                        $optimizerChain->optimize($fullPath);
                    } catch (\Exception $e) {
                        \Log::warning('Re-optimization after resizing failed: ' . $e->getMessage());
                    }
                }
            }
            
            $optimizedSize = filesize($fullPath);
            $compressionRatio = $originalSize > 0 ? round((($originalSize - $optimizedSize) / $originalSize) * 100, 2) : 0;
            
            $results = [
                'original' => $tempPath,
                'optimized' => $tempPath,
                'original_size' => $originalSize,
                'optimized_size' => $optimizedSize,
                'compression_ratio' => $compressionRatio,
                'dimensions' => [
                    'width' => $image->width(),
                    'height' => $image->height()
                ]
            ];
            
            // Generate WebP version if requested
            if ($options['generateWebP']) {
                try {
                    $webpPath = $directory . '/' . $filename . '.webp';
                    $webpFullPath = storage_path('app/public/' . $webpPath);
                    $image->toWebp($options['quality'])->save($webpFullPath);
                    
                    if ($hasOptimizers) {
                        try {
                            $optimizerChain->optimize($webpFullPath);
                        } catch (\Exception $e) {
                            \Log::warning('WebP optimization failed: ' . $e->getMessage());
                        }
                    }
                    
                    $results['webp'] = $webpPath;
                } catch (\Exception $e) {
                    \Log::warning('WebP generation failed: ' . $e->getMessage());
                }
            }
            
            // Generate thumbnails if requested
            if ($options['generateThumbnails'] && !empty($options['thumbnailSizes'])) {
                $results['thumbnails'] = [];
                foreach ($options['thumbnailSizes'] as $size) {
                    try {
                        $thumbPath = $directory . '/' . $filename . '_' . $size . '.' . $extension;
                        $thumbFullPath = storage_path('app/public/' . $thumbPath);
                        
                        $thumbImage = $manager->read($fullPath);
                        $thumbImage->scaleDown($size, $size);
                        
                        if (in_array($extension, ['jpg', 'jpeg'])) {
                            $thumbImage->toJpeg($options['quality'])->save($thumbFullPath);
                        } elseif ($extension === 'png') {
                            $thumbImage->toPng()->save($thumbFullPath);
                        } else {
                            $thumbImage->toJpeg($options['quality'])->save($thumbFullPath);
                        }
                        
                        if ($hasOptimizers) {
                            try {
                                $optimizerChain->optimize($thumbFullPath);
                            } catch (\Exception $e) {
                                \Log::warning("Thumbnail optimization failed for size {$size}: " . $e->getMessage());
                            }
                        }
                        
                        $results['thumbnails'][$size] = $thumbPath;
                    } catch (\Exception $e) {
                        \Log::warning("Thumbnail generation failed for size {$size}: " . $e->getMessage());
                    }
                }
            }
            
            // Log successful optimization
            \Log::info('Image optimization completed successfully', [
                'original_size' => $originalSize,
                'optimized_size' => $optimizedSize,
                'compression_ratio' => $compressionRatio,
                'webp_generated' => isset($results['webp']),
                'thumbnails_count' => count($results['thumbnails'] ?? []),
                'file_size_mb' => round($originalSize / (1024 * 1024), 2),
                'dimensions' => $results['dimensions']
            ]);
            
            // Restore original memory limit
            ini_set('memory_limit', $originalMemoryLimit);
            
            return $results;
            
        } catch (\Exception $e) {
            // Restore original memory limit
            ini_set('memory_limit', $originalMemoryLimit);
            
            \Log::error('Image optimization failed: ' . $e->getMessage(), [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'upload_error_code' => $file->getError()
            ]);
            
            // Enhanced fallback: try different approaches based on error type
            try {
                // For upload errors, try alternative storage method
                if ($file->getError() !== UPLOAD_ERR_OK) {
                    \Log::info('Attempting alternative upload handling for error code: ' . $file->getError());
                    
                    // Try to get file content directly and save it
                    $content = file_get_contents($file->getPathname());
                    if ($content === false) {
                        throw new \Exception('Cannot read file content from temporary location');
                    }
                    
                    $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $directory . '/' . $filename;
                    $fullPath = storage_path('app/public/' . $path);
                    
                    // Ensure directory exists
                    $fullDirectoryPath = storage_path('app/public/' . $directory);
                    if (!is_dir($fullDirectoryPath)) {
                        mkdir($fullDirectoryPath, 0755, true);
                    }
                    
                    if (file_put_contents($fullPath, $content) === false) {
                        throw new \Exception('Failed to write file content to storage');
                    }
                    
                    \Log::info('Alternative upload method succeeded', ['path' => $path]);
                    
                    return [
                        'original' => $path,
                        'optimized' => $path,
                        'error' => $e->getMessage(),
                        'fallback_used' => true,
                        'alternative_method' => true
                    ];
                }
                
                // Standard fallback for other errors
                $path = $file->store($directory, 'public');
                return [
                    'original' => $path, 
                    'optimized' => $path,
                    'error' => $e->getMessage(),
                    'fallback_used' => true
                ];
            } catch (\Exception $fallbackError) {
                \Log::error('All fallback methods failed: ' . $fallbackError->getMessage());
                throw new \Exception('Complete image upload failure. Original error: ' . $e->getMessage() . '. Fallback error: ' . $fallbackError->getMessage());
            }
        }
    }

    /**
     * Optimize existing image file
     */
    public static function optimizeExistingImage(string $filePath, array $options = [])
    {
        $options = array_merge([
            'quality' => 85,
            'maxWidth' => 1600, // Increased for 5MB images
            'maxHeight' => 1600, // Increased for 5MB images
        ], $options);

        try {
            $fullPath = storage_path('app/public/' . $filePath);
            
            if (!file_exists($fullPath)) {
                \Log::error('Image file not found: ' . $fullPath);
                return false;
            }
            
            // Create backup
            $backupPath = $fullPath . '.backup';
            copy($fullPath, $backupPath);
            
            // Optimize with Spatie Image Optimizer
            $optimizerChain = OptimizerChainFactory::create();
            
            // Verify file exists
            if (!file_exists($fullPath)) {
                \Log::error('Image file not found for optimization: ' . $fullPath);
                return false;
            }
            
            $optimizerChain->optimize($fullPath);
            
            // Further optimization with Intervention Image
            $manager = self::getImageManager();
            $image = $manager->read($fullPath);
            
            // Resize if too large
            if ($image->width() > $options['maxWidth'] || $image->height() > $options['maxHeight']) {
                $image->scaleDown($options['maxWidth'], $options['maxHeight']);
                $image->toJpeg($options['quality'])->save($fullPath);
                
                // Re-optimize after resizing
                $optimizerChain->optimize($fullPath);
            }
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Image optimization failed for existing file: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Optimize and convert image to WebP
     */
    public static function optimizeImage($imagePath, $quality = 80, $maxWidth = 1600) // Increased for 5MB images
    {
        try {
            // Get the original image
            $manager = self::getImageManager();
            $image = $manager->read($imagePath);
            
            // Resize if too large
            if ($image->width() > $maxWidth) {
                $image->scaleDown($maxWidth);
            }
            
            // Get file info
            $pathInfo = pathinfo($imagePath);
            $filename = $pathInfo['filename'];
            $directory = $pathInfo['dirname'];
            
            // Save optimized original format
            $image->toJpeg($quality)->save($imagePath);
            
            // Optimize with Spatie Image Optimizer
            $optimizerChain = OptimizerChainFactory::create();
            
            // Verify file exists
            if (!file_exists($imagePath)) {
                \Log::error('Image file not found for optimization: ' . $imagePath);
                return ['original' => $imagePath];
            }
            
            $optimizerChain->optimize($imagePath);
            
            // Create WebP version
            $webpPath = $directory . '/' . $filename . '.webp';
            $image->toWebp($quality)->save($webpPath);
            $optimizerChain->optimize($webpPath);
            
            // Create optimized JPEG fallback
            $jpegPath = $directory . '/' . $filename . '_optimized.jpg';
            $image->toJpeg($quality)->save($jpegPath);
            $optimizerChain->optimize($jpegPath);
            
            return [
                'webp' => $webpPath,
                'jpeg' => $jpegPath,
                'original' => $imagePath
            ];
            
        } catch (\Exception $e) {
            \Log::error('Image optimization failed: ' . $e->getMessage());
            return ['original' => $imagePath];
        }
    }
    
    /**
     * Generate responsive image HTML
     */
    public static function generateResponsiveImage($imagePath, $alt = '', $class = '', $sizes = [])
    {
        $defaultSizes = [
            'sm' => 576,
            'md' => 768,
            'lg' => 992,
            'xl' => 1200
        ];
        
        $sizes = array_merge($defaultSizes, $sizes);
        $pathInfo = pathinfo($imagePath);
        $filename = $pathInfo['filename'];
        $directory = $pathInfo['dirname'];
        
        // Generate srcset
        $srcset = [];
        foreach ($sizes as $breakpoint => $width) {
            $resizedPath = $directory . '/' . $filename . '_' . $width . '.webp';
            if (file_exists(public_path($resizedPath))) {
                $srcset[] = $resizedPath . ' ' . $width . 'w';
            }
        }
        
        $html = '<picture>';
        
        // WebP source
        if (!empty($srcset)) {
            $html .= '<source type="image/webp" srcset="' . implode(', ', $srcset) . '">';
        }
        
        // Fallback image
        $html .= '<img src="' . $imagePath . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . '" loading="lazy" decoding="async">';
        $html .= '</picture>';
        
        return $html;
    }
    
    /**
     * Generate image with lazy loading
     */
    public static function lazyImage($src, $alt = '', $class = '', $placeholder = null)
    {
        $placeholder = $placeholder ?: 'data:image/svg+xml;base64,' . base64_encode(
            '<svg width="400" height="300" xmlns="http://www.w3.org/2000/svg"><rect width="100%" height="100%" fill="#f8f9fa"/><text x="50%" y="50%" text-anchor="middle" dy=".3em" fill="#6c757d">Loading...</text></svg>'
        );
        
        return '<img src="' . $placeholder . '" data-src="' . $src . '" alt="' . htmlspecialchars($alt) . '" class="lazy ' . $class . '" loading="lazy" decoding="async">';
    }

    /**
     * Batch optimize multiple images in a directory
     */
    public static function batchOptimizeDirectory(string $directory, array $options = [])
    {
        $options = array_merge([
            'quality' => 85,
            'maxWidth' => 1600, // Increased for 5MB images
            'maxHeight' => 1600, // Increased for 5MB images
            'allowedExtensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'], // Added webp support
            'skipOptimized' => true,
            'createBackup' => true,
        ], $options);

        $directoryPath = storage_path('app/public/' . $directory);
        
        if (!is_dir($directoryPath)) {
            return ['error' => 'Directory not found: ' . $directoryPath];
        }

        $results = [
            'processed' => 0,
            'skipped' => 0,
            'errors' => 0,
            'total_size_before' => 0,
            'total_size_after' => 0,
            'files' => []
        ];

        $files = glob($directoryPath . '/*');
        
        foreach ($files as $filePath) {
            if (!is_file($filePath)) {
                continue;
            }

            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            
            if (!in_array($extension, $options['allowedExtensions'])) {
                continue;
            }

            $relativePath = str_replace(storage_path('app/public/'), '', $filePath);
            $sizeBefore = filesize($filePath);
            
            // Skip if already optimized (check for backup file)
            if ($options['skipOptimized'] && file_exists($filePath . '.backup')) {
                $results['skipped']++;
                continue;
            }

            try {
                $results['total_size_before'] += $sizeBefore;
                
                if (self::optimizeExistingImage($relativePath, $options)) {
                    $sizeAfter = filesize($filePath);
                    $results['total_size_after'] += $sizeAfter;
                    $results['processed']++;
                    
                    $results['files'][] = [
                        'file' => $relativePath,
                        'size_before' => $sizeBefore,
                        'size_after' => $sizeAfter,
                        'compression' => round((($sizeBefore - $sizeAfter) / $sizeBefore) * 100, 2)
                    ];
                } else {
                    $results['errors']++;
                }
            } catch (\Exception $e) {
                $results['errors']++;
                \Log::error('Batch optimization failed for file: ' . $relativePath, [
                    'error' => $e->getMessage()
                ]);
            }
        }

        $results['total_compression'] = $results['total_size_before'] > 0 
            ? round((($results['total_size_before'] - $results['total_size_after']) / $results['total_size_before']) * 100, 2)
            : 0;

        return $results;
    }

    /**
     * Handle large file uploads with enhanced error recovery
     */
    public static function handleLargeFileUpload(UploadedFile $file, string $directory = 'uploads', array $options = [])
    {
        $options = array_merge([
            'skip_optimization' => false,
            'force_store' => true,
            'max_attempts' => 3
        ], $options);
        
        $attempts = 0;
        $lastError = null;
        
        while ($attempts < $options['max_attempts']) {
            try {
                $attempts++;
                
                \Log::info("Large file upload attempt {$attempts}", [
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'error_code' => $file->getError(),
                    'is_valid' => $file->isValid()
                ]);
                
                // If optimization is disabled or file has upload errors, just store it
                if ($options['skip_optimization'] || $file->getError() !== UPLOAD_ERR_OK) {
                    return self::simpleFileStore($file, $directory);
                }
                
                // Try full optimization
                return self::optimizeUploadedImage($file, $directory, $options);
                
            } catch (\Exception $e) {
                $lastError = $e;
                \Log::warning("Large file upload attempt {$attempts} failed: " . $e->getMessage());
                
                // On final attempt, try simple storage
                if ($attempts === $options['max_attempts'] && $options['force_store']) {
                    try {
                        return self::simpleFileStore($file, $directory);
                    } catch (\Exception $storeError) {
                        \Log::error('Final simple storage attempt failed: ' . $storeError->getMessage());
                    }
                }
                
                // Wait a bit before retrying (in case it's a temporary issue)
                if ($attempts < $options['max_attempts']) {
                    usleep(500000); // 0.5 seconds
                }
            }
        }
        
        throw new \Exception("All upload attempts failed. Last error: " . ($lastError ? $lastError->getMessage() : 'Unknown error'));
    }
    
    /**
     * Simple file storage without optimization
     */
    private static function simpleFileStore(UploadedFile $file, string $directory)
    {
        try {
            // Check if file is accessible
            if (!$file->isValid() && $file->getError() !== UPLOAD_ERR_OK) {
                // Try direct file copy approach
                $content = file_get_contents($file->getPathname());
                if ($content === false) {
                    throw new \Exception('Cannot access file content');
                }
                
                $filename = uniqid() . '_' . time() . '.' . strtolower($file->getClientOriginalExtension());
                $path = $directory . '/' . $filename;
                $fullPath = storage_path('app/public/' . $path);
                
                // Ensure directory exists
                $fullDirectoryPath = dirname($fullPath);
                if (!is_dir($fullDirectoryPath)) {
                    mkdir($fullDirectoryPath, 0755, true);
                }
                
                if (file_put_contents($fullPath, $content) === false) {
                    throw new \Exception('Failed to save file content');
                }
                
                \Log::info('File stored using direct copy method', [
                    'path' => $path,
                    'size' => strlen($content)
                ]);
                
                return [
                    'original' => $path,
                    'optimized' => $path,
                    'simple_store' => true,
                    'method' => 'direct_copy'
                ];
            }
            
            // Standard Laravel storage
            $path = $file->store($directory, 'public');
            
            \Log::info('File stored using standard Laravel method', [
                'path' => $path,
                'size' => $file->getSize()
            ]);
            
            return [
                'original' => $path,
                'optimized' => $path,
                'simple_store' => true,
                'method' => 'laravel_store'
            ];
            
        } catch (\Exception $e) {
            \Log::error('Simple file store failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if image optimization binaries are available
     */
    public static function checkOptimizerStatus()
    {
        try {
            $optimizerChain = OptimizerChainFactory::create();
            $optimizers = $optimizerChain->getOptimizers();
            
            $status = [];
            foreach ($optimizers as $optimizer) {
                $className = get_class($optimizer);
                $binaryName = method_exists($optimizer, 'binaryName') ? $optimizer->binaryName() : 'N/A';
                
                // Check if binary exists
                $binaryExists = false;
                if ($binaryName !== 'N/A') {
                    $checkCommand = PHP_OS_FAMILY === 'Windows' ? "where {$binaryName} 2>nul" : "which {$binaryName} 2>/dev/null";
                    $output = shell_exec($checkCommand);
                    $binaryExists = !empty(trim($output));
                }
                
                $status[] = [
                    'class' => basename($className),
                    'binary' => $binaryName,
                    'available' => $binaryExists,
                    'full_class' => $className
                ];
            }
            
            return $status;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get optimization statistics for a directory
     */
    public static function getDirectoryStats(string $directory)
    {
        $directoryPath = storage_path('app/public/' . $directory);
        
        if (!is_dir($directoryPath)) {
            return ['error' => 'Directory not found'];
        }

        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'image_files' => 0,
            'optimized_files' => 0,
            'file_types' => []
        ];

        $files = glob($directoryPath . '/*');
        
        foreach ($files as $filePath) {
            if (!is_file($filePath)) {
                continue;
            }

            $stats['total_files']++;
            $stats['total_size'] += filesize($filePath);
            
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $stats['file_types'][$extension] = ($stats['file_types'][$extension] ?? 0) + 1;
            
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $stats['image_files']++;
                
                if (file_exists($filePath . '.backup')) {
                    $stats['optimized_files']++;
                }
            }
        }

        return $stats;
    }
}