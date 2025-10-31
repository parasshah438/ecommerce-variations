<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Sale extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'banner_image', 'type', 
        'discount_value', 'max_discount', 'min_order_value',
        'start_date', 'end_date', 'is_active', 'applicable_categories',
        'applicable_brands', 'usage_limit', 'usage_count'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'applicable_categories' => 'array',
        'applicable_brands' => 'array',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'sale_products')
                    ->withPivot('custom_discount')
                    ->withTimestamps();
    }

    public function isActive()
    {
        return $this->is_active && 
               Carbon::now()->between($this->start_date, $this->end_date) &&
               ($this->usage_limit === null || $this->usage_count < $this->usage_limit);
    }

    public function getDiscountForProduct($product)
    {
        $saleProduct = $this->products()->where('product_id', $product->id)->first();
        
        if ($saleProduct && $saleProduct->pivot->custom_discount) {
            return $saleProduct->pivot->custom_discount;
        }
        
        return $this->discount_value;
    }

    public function calculateSalePrice($originalPrice, $discount = null)
    {
        $discount = $discount ?? $this->discount_value;
        
        if ($this->type === 'percentage') {
            $discountAmount = ($originalPrice * $discount) / 100;
            if ($this->max_discount) {
                $discountAmount = min($discountAmount, $this->max_discount);
            }
            return $originalPrice - $discountAmount;
        }
        
        if ($this->type === 'fixed') {
            return max(0, $originalPrice - $discount);
        }
        
        return $originalPrice;
    }

    public function getTimeRemaining()
    {
        if (!$this->isActive()) {
            return null;
        }
        
        $now = Carbon::now();
        $endDate = Carbon::parse($this->end_date);
        
        return $endDate->diff($now);
    }

    public function getSaleTypeLabel()
    {
        return match($this->type) {
            'percentage' => 'Percentage Off',
            'fixed' => 'Fixed Amount Off',
            'bogo' => 'Buy One Get One',
            default => 'Sale'
        };
    }
}
