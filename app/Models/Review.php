<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_REPORTED = 'reported';

    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'title',
        'comment',
        'verified_purchase',
        'is_approved',
        'status',
        'admin_notes',
        'moderated_at',
        'moderated_by',
    ];

    protected $casts = [
        'verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
        'rating' => 'integer',
        'moderated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    protected static function booted(): void
    {
        static::created(function (Review $review) {
            $review->product->updateReviewStats();
        });

        static::updated(function (Review $review) {
            if ($review->wasChanged(['is_approved', 'status', 'rating'])) {
                $review->product->updateReviewStats();
            }
        });

        static::deleted(function (Review $review) {
            $review->product->updateReviewStats();
        });
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_REPORTED => 'Reported',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst($this->status);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'bg-success',
            self::STATUS_PENDING => 'bg-warning text-dark',
            self::STATUS_REJECTED => 'bg-danger',
            self::STATUS_REPORTED => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    public function markApproved(?int $moderatorId = null, ?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'is_approved' => true,
            'admin_notes' => $notes ?? $this->admin_notes,
            'moderated_at' => now(),
            'moderated_by' => $moderatorId ?? auth()->id(),
        ]);
    }

    public function markRejected(?int $moderatorId = null, ?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'is_approved' => false,
            'admin_notes' => $notes,
            'moderated_at' => now(),
            'moderated_by' => $moderatorId ?? auth()->id(),
        ]);
    }

    public function markReported(?int $moderatorId = null, ?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_REPORTED,
            'is_approved' => false,
            'admin_notes' => $notes,
            'moderated_at' => now(),
            'moderated_by' => $moderatorId ?? auth()->id(),
        ]);
    }

    public function markPending(): void
    {
        $this->update([
            'status' => self::STATUS_PENDING,
            'is_approved' => false,
            'moderated_at' => null,
            'moderated_by' => null,
        ]);
    }

    /**
     * Publish immediately on customer submit (Amazon/Flipkart style).
     */
    public function publishImmediately(): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'is_approved' => true,
            'moderated_at' => null,
            'moderated_by' => null,
        ]);
    }

    /**
     * Attributes for creating/updating a directly published review.
     */
    public static function directPublishAttributes(): array
    {
        return [
            'is_approved' => true,
            'status' => self::STATUS_APPROVED,
            'moderated_at' => null,
            'moderated_by' => null,
        ];
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED)->where('is_approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopeByStatus($query, ?string $status)
    {
        if ($status) {
            $query->where('status', $status);
        }

        return $query;
    }
}
