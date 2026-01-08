<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'video', 'category_id', 'brand_id', 'price', 'mrp', 'weight', 'length', 'width', 'height', 'volumetric_weight', 'active', 'reviews_count', 'average_rating'];

    protected $casts = [
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'volumetric_weight' => 'decimal:2',
        'price' => 'decimal:2',
        'mrp' => 'decimal:2',
    ];

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

    public function activeSales()
    {
        return $this->belongsToMany(Sale::class, 'sale_products')
                    ->where('is_active', true)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    public function orderItems()
    {
        return $this->hasManyThrough(OrderItem::class, ProductVariation::class);
    }

    public function getBestSalePrice()
    {
        $activeSales = $this->activeSales;
        
        if ($activeSales->isEmpty()) {
            return $this->price;
        }
        
        $bestPrice = $this->price;
        
        foreach ($activeSales as $sale) {
            $discount = $sale->getDiscountForProduct($this);
            $salePrice = $sale->calculateSalePrice($this->price, $discount);
            $bestPrice = min($bestPrice, $salePrice);
        }
        
        return $bestPrice;
    }

    public function getDiscountPercentage()
    {
        $salePrice = $this->getBestSalePrice();
        if ($salePrice < $this->price) {
            return round((($this->price - $salePrice) / $this->price) * 100);
        }
        return 0;
    }

    public function hasActiveSale()
    {
        return $this->activeSales()->exists();
    }

    public function getActiveSale()
    {
        return $this->activeSales()->first();
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

    /**
     * Calculate volumetric weight if dimensions are available
     * Formula: (L × W × H) / 5000
     */
    public function calculateVolumetricWeight()
    {
        if ($this->length && $this->width && $this->height) {
            return ($this->length * $this->width * $this->height) / 5000;
        }
        return null;
    }

    /**
     * Get the final weight considering volumetric weight
     * Returns the higher of actual weight or volumetric weight
     */
    public function getFinalWeight()
    {
        $actualWeight = $this->weight ?? 200; // Default 200g for clothing
        $volumetricWeight = $this->calculateVolumetricWeight();
        
        return $volumetricWeight ? max($actualWeight, $volumetricWeight) : $actualWeight;
    }

    /**
     * Get weight category for admin reference
     */
    public function getWeightCategory()
    {
        $weight = $this->getFinalWeight();
        
        if ($weight <= 150) return 'Very Light';
        if ($weight <= 300) return 'Light';
        if ($weight <= 600) return 'Medium';
        if ($weight <= 1000) return 'Heavy';
        return 'Very Heavy';
    }

}
