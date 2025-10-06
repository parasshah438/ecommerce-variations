<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id', 
        'rating',
        'title',
        'comment',
        'verified_purchase',
        'is_approved'
    ];

    protected $casts = [
        'verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
        'rating' => 'integer'
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Model Events to automatically update product stats
    protected static function booted()
    {
        static::created(function ($review) {
            $review->product->updateReviewStats();
        });

        static::updated(function ($review) {
            $review->product->updateReviewStats();
        });

        static::deleted(function ($review) {
            $review->product->updateReviewStats();
        });
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }
}
