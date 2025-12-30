<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;

    protected $table = 'wishlists';

    protected $fillable = [
        'user_id',
        'product_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the wishlist item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product in the wishlist.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope to get wishlist items for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if a product is in user's wishlist
     */
    public static function isInWishlist($productId, $userId)
    {
        return static::where('product_id', $productId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get wishlist count for a user
     */
    public static function getCountForUser($userId)
    {
        try {
            return static::where('user_id', $userId)->count();
        } catch (\Exception $e) {
            return 0;
        }
    }
}