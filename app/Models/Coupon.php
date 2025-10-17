<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 
        'discount', 
        'type', 
        'valid_from', 
        'valid_until',
        'minimum_cart_value',
        'maximum_discount_limit',
        'usage_limit',
        'used_count'
    ];

    /**
     * Get all carts that have this coupon applied.
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Check if the coupon is currently valid.
     */
    public function isValid(): bool
    {
        $today = now()->toDateString();
        
        if ($this->valid_from && $this->valid_from > $today) {
            return false;
        }
        
        if ($this->valid_until && $this->valid_until < $today) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if coupon can be applied to cart based on minimum cart value.
     */
    public function canApplyToCart($cartSubtotal): bool
    {
        return $cartSubtotal >= $this->minimum_cart_value;
    }

    /**
     * Check if coupon has reached usage limit.
     */
    public function hasReachedUsageLimit(): bool
    {
        if (!$this->usage_limit) {
            return false; // No limit set
        }
        
        return $this->used_count >= $this->usage_limit;
    }

    /**
     * Calculate the maximum discount this coupon can provide.
     */
    public function calculateDiscount($cartSubtotal): float
    {
        if ($this->type === 'percentage') {
            $discount = ($cartSubtotal * $this->discount) / 100;
        } else {
            $discount = $this->discount;
        }

        // Apply maximum discount limit if set
        if ($this->maximum_discount_limit && $discount > $this->maximum_discount_limit) {
            $discount = $this->maximum_discount_limit;
        }

        // Ensure discount doesn't exceed cart subtotal
        return min($discount, $cartSubtotal);
    }

    /**
     * Get validation error message for this coupon.
     */
    public function getValidationError($cartSubtotal): ?string
    {
        if (!$this->isValid()) {
            if ($this->valid_from && $this->valid_from > now()->toDateString()) {
                return "This coupon is not yet valid. Valid from " . $this->valid_from;
            }
            if ($this->valid_until && $this->valid_until < now()->toDateString()) {
                return "This coupon has expired on " . $this->valid_until;
            }
        }

        if (!$this->canApplyToCart($cartSubtotal)) {
            return "Minimum cart value of ₹" . number_format($this->minimum_cart_value, 2) . " required for this coupon";
        }

        if ($this->hasReachedUsageLimit()) {
            return "This coupon has reached its usage limit";
        }

        return null;
    }
}
