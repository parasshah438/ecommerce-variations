<?php

namespace App\Helpers;

use App\Jobs\OptimizeImageJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Cache, Log, Queue, Storage};
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class ImageOptimizer
{
    private static ?ImageManager $manager = null;
    private static ?array $optimizers = null;

    /**
     * Handle uploaded image with queue support
     */
    public static function handleUpload(UploadedFile $file, string $directory = 'uploads', array $options = []): array
    {
        // Validate upload
        self::validateUpload($file);
        
        // Store file immediately
        $path = self::storeFile($file, $directory);
        $config = config('image_optimizer');
        
        // Check if should queue optimization
        if (($config['queue']['enabled'] ?? false) && $file->getSize() > ($config['queue']['size_threshold'] ?? 1048576)) {
            OptimizeImageJob::dispatch($path, $options)->onQueue($config['queue']['queue_name']);
            
            return [
                'path' => $path,
                'queued' => true,
                'message' => 'File stored, optimization queued'
            ];
        }
        
        // Process immediately for small files
        return self::processStoredImage($path, $options);
    }

    /**
     * Process stored image file
     */
    public static function processStoredImage(string $path, array $options = []): array
    {
        $cacheKey = 'img_opt_' . md5($path . json_encode($options, JSON_UNESCAPED_SLASHES));
        
        if (config('image_optimizer.cache.enabled') && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $fullPath = Storage::disk('public')->path($path);
        if (!file_exists($fullPath)) {
            throw new \Exception('File not found: ' . $path);
        }

        $config = config('image_optimizer');
        $options = array_merge([
            'quality' => $config['quality']['standard'],
            'max_width' => $config['sizes']['max_width'],
            'max_height' => $config['sizes']['max_height'],
            'generate_webp' => $config['formats']['webp'],
            'thumbnails' => $config['sizes']['thumbnails'],
        ], $options);

        try {
            $originalSize = filesize($fullPath);
            $result = [
                'path' => $path,
                'original_size' => $originalSize,
                'variants' => []
            ];

            // Optimize main image
            $optimized = self::optimizeImage($fullPath, $options);
            $result['optimized_size'] = filesize($fullPath);
            $result['compression_ratio'] = round((($originalSize - $result['optimized_size']) / $originalSize) * 100, 2);

            // Generate WebP
            if ($options['generate_webp']) {
                $webpPath = self::generateWebP($fullPath, $options['quality']);
                if ($webpPath) {
                    $result['variants']['webp'] = str_replace(storage_path('app/public/'), '', $webpPath);
                }
            }

            // Generate thumbnails (optimized - single image load)
            $result['variants']['thumbnails'] = [];
            if (!empty($options['thumbnails'])) {
                $thumbnails = self::generateThumbnails($fullPath, $options['thumbnails'], $options);
                foreach ($thumbnails as $size => $thumbPath) {
                    $result['variants']['thumbnails'][$size] = str_replace(storage_path('app/public/'), '', $thumbPath);
                }
            }

            // Cache result (clear old cache first)
            if (config('image_optimizer.cache.enabled')) {
                Cache::forget($cacheKey);
                Cache::put($cacheKey, $result, config('image_optimizer.cache.ttl'));
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Image processing failed', ['path' => $path, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Validate uploaded file
     */
    private static function validateUpload(UploadedFile $file): void
    {
        $config = config('image_optimizer.optimization', []);
        
        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new \Exception('Upload failed with error code: ' . $file->getError());
        }

        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        if (!in_array($file->getMimeType(), $config['allowed_mime_types'] ?? ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            throw new \Exception('Unsupported file type: ' . $file->getMimeType());
        }

        if ($file->getSize() > ($config['max_file_size'] ?? 5242880)) {
            throw new \Exception('File size exceeds limit: ' . round($file->getSize() / 1024 / 1024, 2) . 'MB');
        }
    }

    /**
     * Store uploaded file
     */
    private static function storeFile(UploadedFile $file, string $directory): string
    {
        $filename = uniqid() . '_' . time() . '.' . strtolower($file->getClientOriginalExtension());
        return $file->storeAs($directory, $filename, 'public');
    }

    /**
     * Get singleton Image Manager
     */
    private static function getImageManager(): ImageManager
    {
        return self::$manager ??= new ImageManager(new Driver());
    }

    /**
     * Optimize single image file
     */
    private static function optimizeImage(string $fullPath, array $options): bool
    {
        try {
            // Use external optimizers if available
            if (self::hasOptimizers()) {
                $optimizerChain = OptimizerChainFactory::create();
                $optimizerChain->optimize($fullPath);
            }

            // Resize with Intervention Image
            $manager = self::getImageManager();
            $image = $manager->read($fullPath);
            if (!$image) {
                throw new \Exception("Cannot read image");
            }
            
            if ($image->width() > $options['max_width'] || $image->height() > $options['max_height']) {
                $image->scaleDown($options['max_width'], $options['max_height']);
                
                $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                if (in_array($extension, ['jpg', 'jpeg'])) {
                    $image->toJpeg($options['quality'])->save($fullPath);
                } else {
                    $image->toPng()->save($fullPath);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::warning('Image optimization failed', ['path' => $fullPath, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Generate WebP variant
     */
    private static function generateWebP(string $fullPath, int $quality): ?string
    {
        try {
            $pathInfo = pathinfo($fullPath);
            $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
            $image = self::getImageManager()->read($fullPath);
            $image->toWebp($quality)->save($webpPath);
            return $webpPath;
        } catch (\Exception $e) {
            Log::warning('WebP generation failed', ['path' => $fullPath, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Generate multiple thumbnails efficiently using image cloning
     */
    private static function generateThumbnails(string $fullPath, array $sizes, array $options): array
    {
        $thumbnails = [];
        
        try {
            // Load image once
            $original = self::getImageManager()->read($fullPath);
            $pathInfo = pathinfo($fullPath);
            
            foreach ($sizes as $size) {
                try {
                    $thumbPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $size . '.' . $pathInfo['extension'];
                    
                    // Clone the original image for each thumbnail
                    $image = clone $original;
                    $image->scaleDown($size, $size);
                    
                    $ext = strtolower($pathInfo['extension']);
                    if (in_array($ext, ['jpg', 'jpeg'])) {
                        $image->toJpeg($options['quality'])->save($thumbPath);
                    } else {
                        $image->toPng()->save($thumbPath);
                    }
                    
                    $thumbnails[$size] = $thumbPath;
                } catch (\Exception $e) {
                    Log::warning('Individual thumbnail generation failed', [
                        'path' => $fullPath, 
                        'size' => $size, 
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::warning('Thumbnail batch generation failed', [
                'path' => $fullPath, 
                'error' => $e->getMessage()
            ]);
        }
        
        return $thumbnails;
    }

    /**
     * Legacy method - generate single thumbnail (kept for compatibility)
     */
    private static function generateThumbnail(string $fullPath, int $size, array $options): ?string
    {
        $thumbnails = self::generateThumbnails($fullPath, [$size], $options);
        return $thumbnails[$size] ?? null;
    }

    /**
     * Check if optimization binaries are available
     */
    private static function hasOptimizers(): bool
    {
        if (self::$optimizers !== null) {
            return !empty(self::$optimizers);
        }

        self::$optimizers = [];
        
        try {
            $config = config('image_optimizer.binaries', []);
            foreach ($config as $name => $binary) {
                if (self::binaryExists($binary)) {
                    self::$optimizers[$name] = $binary;
                }
            }
        } catch (\Exception $e) {
            Log::debug('Optimizer check failed', ['error' => $e->getMessage()]);
        }

        return !empty(self::$optimizers);
    }

    /**
     * Check if binary exists (secure version)
     */
    private static function binaryExists(string $binary): bool
    {
        $binary = escapeshellarg($binary);
        $command = PHP_OS_FAMILY === 'Windows' ? "where $binary 2>nul" : "which $binary 2>/dev/null";
        
        $output = null;
        $returnVar = null;
        exec($command, $output, $returnVar);
        
        return $returnVar === 0 && !empty($output);
    }

    /**
     * Generate responsive image HTML
     */
    public static function responsiveImage(string $path, string $alt = '', array $attributes = []): string
    {
        $config = config('image_optimizer', []);
        $cdnUrl = ($config['cdn']['enabled'] ?? false) ? rtrim($config['cdn']['url'] ?? '', '/') . '/' : '';
        
        $pathInfo = pathinfo($path);
        $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
        
        $srcset = [];
        foreach ($config['sizes']['thumbnails'] as $size) {
            $thumbPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $size . '.' . $pathInfo['extension'];
            if (Storage::disk('public')->exists($thumbPath)) {
                $srcset[] = $cdnUrl . Storage::url($thumbPath) . ' ' . $size . 'w';
            }
        }

        $class = $attributes['class'] ?? '';
        $loading = $attributes['loading'] ?? 'lazy';
        
        $html = '<picture>';
        
        if (Storage::disk('public')->exists($webpPath)) {
            $html .= '<source type="image/webp" srcset="' . $cdnUrl . Storage::url($webpPath) . '">';
        }
        
        if (!empty($srcset)) {
            $html .= '<source srcset="' . implode(', ', $srcset) . '">';
        }
        
        $html .= '<img src="' . $cdnUrl . Storage::url($path) . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . '" loading="' . $loading . '">';
        $html .= '</picture>';
        
        return $html;
    }

    /**
     * Get optimization status
     */
    public static function getStatus(): array
    {
        $config = config('image_optimizer.binaries', []);
        $status = [];
        
        foreach ($config as $name => $binary) {
            $status[$name] = self::binaryExists($binary);
        }
        
        return [
            'binaries' => $status,
            'queue_enabled' => config('image_optimizer.queue.enabled'),
            'cache_enabled' => config('image_optimizer.cache.enabled'),
            'cdn_enabled' => config('image_optimizer.cdn.enabled'),
        ];
    }

    /**
     * Legacy compatibility method
     */
    public static function optimizeUploadedImage(UploadedFile $file, string $directory = 'uploads', array $options = []): array
    {
        return self::handleUpload($file, $directory, $options);
    }

    /**
     * Legacy method - use processStoredImage instead
     */
    public static function optimizeExistingImage(string $filePath, array $options = []): bool
    {
        try {
            self::processStoredImage($filePath, $options);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Batch optimize directory
     */
    public static function batchOptimizeDirectory(string $directory, array $options = []): array
    {
        $config = config('image_optimizer');
        $results = ['processed' => 0, 'errors' => 0, 'files' => []];
        
        $files = Storage::disk('public')->files($directory);
        
        foreach ($files as $file) {
            if (!in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                continue;
            }

            try {
                if ($config['queue']['enabled']) {
                    OptimizeImageJob::dispatch($file, $options)->onQueue($config['queue']['queue_name']);
                } else {
                    $result = self::processStoredImage($file, $options);
                    $results['files'][] = $result;
                }
                $results['processed']++;
            } catch (\Exception $e) {
                $results['errors']++;
                Log::error('Batch optimization failed', ['file' => $file, 'error' => $e->getMessage()]);
            }
        }

        return $results;
    }

    /**
     * Generate lazy loading placeholder
     */
    public static function lazyPlaceholder(int $width = 400, int $height = 300): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode(
            "<svg width='$width' height='$height' xmlns='http://www.w3.org/2000/svg'><rect width='100%' height='100%' fill='#f8f9fa'/><text x='50%' y='50%' text-anchor='middle' dy='.3em' fill='#6c757d'>Loading...</text></svg>"
        );
    }
}