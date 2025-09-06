<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'category_id', 'brand_id', 'price', 'mrp', 'active'];

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
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
}
