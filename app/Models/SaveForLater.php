<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaveForLater extends Model
{
    use HasFactory;

    protected $table = 'save_for_later';

    protected $fillable = [
        'user_id',
        'guest_uuid',
        'product_variation_id',
        'quantity',
        'price',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get the user that owns the saved item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product variation for this saved item.
     */
    public function productVariation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class);
    }

    /**
     * Get the product through the variation.
     */
    public function product()
    {
        return $this->productVariation->product ?? null;
    }

    /**
     * Scope for filtering by user or guest
     */
    public function scopeForUserOrGuest($query, $user = null, $guestUuid = null)
    {
        if ($user) {
            return $query->where('user_id', $user->id);
        } elseif ($guestUuid) {
            return $query->where('guest_uuid', $guestUuid);
        }
        
        return $query->whereRaw('1 = 0'); // Return empty result
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return '₹' . number_format($this->price, 2);
    }

    /**
     * Get total amount for this saved item
     */
    public function getTotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotalAttribute(): string
    {
        return '₹' . number_format($this->total, 2);
    }
}