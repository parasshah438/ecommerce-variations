<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'video', 'category_id', 'brand_id', 'price', 'mrp', 'active', 'reviews_count', 'average_rating'];

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function variationImages()
    {
        return $this->hasMany(ProductVariationImage::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Return images for a given variation id, or product-level images as fallback.
     * @param int|null $variationId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function galleryForVariation(?int $variationId = null)
    {
        // 1) Prefer dedicated variation images table if present
        if ($variationId && class_exists(ProductVariationImage::class)) {
            $imgs = ProductVariationImage::where('product_id', $this->id)
                ->where('product_variation_id', $variationId)
                ->orderBy('position')
                ->get();
            if ($imgs->isNotEmpty()) return $imgs;
        }

        // 2) Fallback to product_images rows attached to the variation
        if ($variationId) {
            $imgs = $this->images()->where('product_variation_id', $variationId)->orderBy('position')->get();
            if ($imgs->isNotEmpty()) return $imgs;
        }

        // 3) Finally, return general product-level images
        return $this->images()->whereNull('product_variation_id')->orderBy('position')->get();
    }
    
    /**
     * Update product review statistics
     * This method is called automatically when reviews are added/updated/deleted
     */
    public function updateReviewStats()
    {
        $stats = $this->reviews()
            ->where('is_approved', true)
            ->selectRaw('COUNT(*) as count, AVG(rating) as average')
            ->first();
        
        $this->update([
            'reviews_count' => $stats->count ?? 0,
            'average_rating' => $stats->average ? round($stats->average, 2) : null
        ]);
    }

    /**
     * Get the best thumbnail image for display in listings
     * Priority: variation images > product images
     */
    public function getThumbnailImage()
    {
        // 1) First try to get any variation image (using eager loaded relationship)
        if ($this->relationLoaded('variationImages') && $this->variationImages->isNotEmpty()) {
            return $this->variationImages->sortBy('position')->first();
        } elseif (class_exists(ProductVariationImage::class)) {
            $variationImage = ProductVariationImage::where('product_id', $this->id)
                ->orderBy('position')
                ->first();
            if ($variationImage) {
                return $variationImage;
            }
        }

        // 2) Try product images attached to any variation (using eager loaded relationship)
        if ($this->relationLoaded('images')) {
            $variationImage = $this->images
                ->where('product_variation_id', '!=', null)
                ->sortBy('position')
                ->first();
            if ($variationImage) {
                return $variationImage;
            }
        } else {
            $variationImage = $this->images()
                ->whereNotNull('product_variation_id')
                ->orderBy('position')
                ->first();
            if ($variationImage) {
                return $variationImage;
            }
        }

        // 3) Fallback to product-level images (using eager loaded relationship)
        if ($this->relationLoaded('images')) {
            return $this->images
                ->where('product_variation_id', null)
                ->sortBy('position')
                ->first();
        } else {
            return $this->images()
                ->whereNull('product_variation_id')
                ->orderBy('position')
                ->first();
        }
    }
}
