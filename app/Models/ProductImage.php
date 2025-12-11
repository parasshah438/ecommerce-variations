<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'product_variation_id', 'path', 'alt', 'position'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    /**
     * Get the image URL attribute
     */
    public function getImageUrlAttribute()
    {
        if ($this->path) {
            return Storage::disk('public')->url($this->path);
        }
        return asset('images/product-placeholder.jpg');
    }

    /**
     * Get optimized image URL (WebP if available, fallback to original)
     */
    public function getOptimizedImageUrlAttribute()
    {
        if (!$this->path) {
            return asset('images/product-placeholder.jpg');
        }
        
        $pathInfo = pathinfo($this->path);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        
        // Try WebP first
        $webpPath = $directory . '/' . $filename . '.webp';
        if (Storage::disk('public')->exists($webpPath)) {
            return Storage::disk('public')->url($webpPath);
        }
        
        return Storage::disk('public')->url($this->path);
    }

    /**
     * Get optimized image URL (method version)
     */
    public function getOptimizedImageUrl()
    {
        return $this->getOptimizedImageUrlAttribute();
    }

    /**
     * Get WebP image URL
     */
    public function getWebPUrl()
    {
        if (!$this->path) {
            return asset('images/product-placeholder.jpg');
        }
        
        $pathInfo = pathinfo($this->path);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        
        $webpPath = $directory . '/' . $filename . '.webp';
        if (Storage::disk('public')->exists($webpPath)) {
            return Storage::disk('public')->url($webpPath);
        }
        
        // Fallback to original image
        return Storage::disk('public')->url($this->path);
    }

    /**
     * Get thumbnail image URL
     */
    public function getThumbnailUrl($size = 300)
    {
        if (!$this->path) {
            return asset('images/product-placeholder.jpg');
        }
        
        $pathInfo = pathinfo($this->path);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? 'jpg';
        
        $thumbPath = $directory . '/' . $filename . '_' . $size . '.' . $extension;
        if (Storage::disk('public')->exists($thumbPath)) {
            return Storage::disk('public')->url($thumbPath);
        }
        
        // Fallback to optimized image
        return $this->optimized_image_url;
    }

    /**
     * Generate responsive image HTML
     */
    public function getResponsiveImageHtml($alt = '', $class = 'img-fluid')
    {
        if (!$this->path) {
            return '<img src="' . asset('images/product-placeholder.jpg') . '" alt="' . htmlspecialchars($alt ?: $this->alt) . '" class="' . $class . '">';
        }
        
        return \App\Helpers\ImageOptimizer::generateResponsiveImage(
            $this->image_url,
            $alt ?: $this->alt,
            $class
        );
    }

    /**
     * Delete image and all related optimized files
     */
    public function deleteImageFiles()
    {
        if (!$this->path) {
            return;
        }

        try {
            // Delete main image
            if (Storage::disk('public')->exists($this->path)) {
                Storage::disk('public')->delete($this->path);
            }
            
            // Get file info for related files
            $pathInfo = pathinfo($this->path);
            $directory = $pathInfo['dirname'];
            $filename = $pathInfo['filename'];
            $extension = $pathInfo['extension'] ?? '';
            
            // Delete WebP version
            $webpPath = $directory . '/' . $filename . '.webp';
            if (Storage::disk('public')->exists($webpPath)) {
                Storage::disk('public')->delete($webpPath);
            }
            
            // Delete thumbnails (products: 150, 300, 600)
            $thumbnailSizes = [150, 300, 600];
            foreach ($thumbnailSizes as $size) {
                $thumbPath = $directory . '/' . $filename . '_' . $size . '.' . $extension;
                if (Storage::disk('public')->exists($thumbPath)) {
                    Storage::disk('public')->delete($thumbPath);
                }
            }
            
        } catch (\Exception $e) {
            \Log::warning('Failed to delete some product image files: ' . $e->getMessage(), [
                'image_path' => $this->path,
                'image_id' => $this->id
            ]);
        }
    }

    /**
     * Override delete to clean up files
     */
    public function delete()
    {
        $this->deleteImageFiles();
        return parent::delete();
    }
}
