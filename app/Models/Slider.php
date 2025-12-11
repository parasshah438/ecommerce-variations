<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Slider extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'image', 'link', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the image URL attribute
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('uploads/' . $this->image);
        }
        return asset('images/slider-placeholder.jpg');
    }

    /**
     * Get optimized image URL (WebP if available, fallback to original)
     */
    public function getOptimizedImageUrlAttribute()
    {
        if (!$this->image) {
            return asset('images/slider-placeholder.jpg');
        }
        
        $pathInfo = pathinfo($this->image);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        
        // Try WebP first
        $webpPath = $directory . '/' . $filename . '.webp';
        if (Storage::disk('public')->exists($webpPath)) {
            return asset('uploads/' . $webpPath);
        }
        
        return asset('uploads/' . $this->image);
    }

    /**
     * Get thumbnail image URL
     */
    public function getThumbnailUrl($size = 300)
    {
        if (!$this->image) {
            return asset('images/slider-placeholder.jpg');
        }
        
        $pathInfo = pathinfo($this->image);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? 'jpg';
        
        $thumbPath = $directory . '/' . $filename . '_' . $size . '.' . $extension;
        if (Storage::disk('public')->exists($thumbPath)) {
            return asset('uploads/' . $thumbPath);
        }
        
        // Fallback to optimized image
        return $this->optimized_image_url;
    }

    /**
     * Delete image when slider is deleted
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
            $thumbnailSizes = [300, 600, 900, 1200];
            foreach ($thumbnailSizes as $size) {
                $thumbPath = $directory . '/' . $filename . '_' . $size . '.' . $extension;
                if (Storage::disk('public')->exists($thumbPath)) {
                    Storage::disk('public')->delete($thumbPath);
                }
            }
            
        } catch (\Exception $e) {
            \Log::warning('Failed to delete some slider image files: ' . $e->getMessage(), [
                'image_path' => $imagePath
            ]);
        }
    }

    /**
     * Scope to get active sliders
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
