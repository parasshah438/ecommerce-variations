<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'image', 'parent_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the image URL attribute
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::url($this->image);
        }
        return asset('images/category-placeholder.svg');
    }

    /**
     * Get optimized image URL (WebP if available, fallback to original)
     */
    public function getOptimizedImageUrlAttribute()
    {
        if (!$this->image) {
            return asset('images/category-placeholder.svg');
        }
        
        $pathInfo = pathinfo($this->image);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        
        // Try WebP first
        $webpPath = $directory . '/' . $filename . '.webp';
        if (Storage::disk('public')->exists($webpPath)) {
            return Storage::url($webpPath);
        }
        
        return Storage::url($this->image);
    }

    /**
     * Get thumbnail image URL
     */
    public function getThumbnailUrl($size = 300)
    {
        if (!$this->image) {
            return asset('images/category-placeholder.svg');
        }
        
        $pathInfo = pathinfo($this->image);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? 'jpg';
        
        $thumbPath = $directory . '/' . $filename . '_' . $size . '.' . $extension;
        if (Storage::disk('public')->exists($thumbPath)) {
            return Storage::url($thumbPath);
        }
        
        // Fallback to original image
        return $this->optimized_image_url;
    }

    /**
     * Generate responsive image HTML
     */
    public function getResponsiveImageHtml($alt = '', $class = 'img-fluid')
    {
        if (!$this->image) {
            return '<img src="' . asset('images/category-placeholder.svg') . '" alt="' . htmlspecialchars($alt ?: $this->name) . '" class="' . $class . '">';
        }
        
        return \App\Helpers\ImageOptimizer::generateResponsiveImage(
            $this->image_url,
            $alt ?: $this->name,
            $class
        );
    }

    /**
     * Delete image when category is deleted (updated for optimized images)
     */
    public function deleteImage()
    {
        if ($this->image) {
            $this->deleteImageFiles($this->image);
        }
    }

    /**
     * Delete image and all related optimized files
     */
    private function deleteImageFiles($imagePath)
    {
        try {
            // Delete main image
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            
            // Get file info for related files
            $pathInfo = pathinfo($imagePath);
            $directory = $pathInfo['dirname'];
            $filename = $pathInfo['filename'];
            $extension = $pathInfo['extension'] ?? '';
            
            // Delete WebP version
            $webpPath = $directory . '/' . $filename . '.webp';
            if (Storage::disk('public')->exists($webpPath)) {
                Storage::disk('public')->delete($webpPath);
            }
            
            // Delete thumbnails
            $thumbnailSizes = [150, 300];
            foreach ($thumbnailSizes as $size) {
                $thumbPath = $directory . '/' . $filename . '_' . $size . '.' . $extension;
                if (Storage::disk('public')->exists($thumbPath)) {
                    Storage::disk('public')->delete($thumbPath);
                }
            }
            
        } catch (\Exception $e) {
            \Log::warning('Failed to delete some category image files: ' . $e->getMessage(), [
                'image_path' => $imagePath
            ]);
        }
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the full breadcrumb path for this category
     */
    public function getBreadcrumbPath()
    {
        $path = collect([$this]);
        
        $current = $this;
        while ($current->parent) {
            $current = $current->parent;
            $path->prepend($current);
        }
        
        return $path;
    }

    /**
     * Get formatted breadcrumb string
     */
    public function getBreadcrumbString($separator = ' > ')
    {
        return $this->getBreadcrumbPath()->pluck('name')->implode($separator);
    }
}
