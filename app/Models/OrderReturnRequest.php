<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderReturnRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'status',
        'customer_reason',
        'return_items',
        'refund_amount',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
        'shiprocket_return_order_id',
        'shiprocket_shipment_id',
        'pickup_awb',
        'pickup_courier',
        'pickup_scheduled_date',
        'picked_up_at',
        'refund_id',
        'refund_method',
        'refunded_at',
    ];

    protected $casts = [
        'return_items' => 'array',
        'refund_amount' => 'decimal:2',
        'reviewed_at' => 'datetime',
        'pickup_scheduled_date' => 'datetime',
        'picked_up_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PICKUP_SCHEDULED = 'pickup_scheduled';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_REFUNDED = 'refunded';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_PICKUP_SCHEDULED => 'Pickup Scheduled',
            self::STATUS_PICKED_UP => 'Picked Up',
            self::STATUS_REFUNDED => 'Refunded',
        ];
    }

    public function getFormattedStatusAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_PICKUP_SCHEDULED => 'info',
            self::STATUS_PICKED_UP => 'primary',
            self::STATUS_REFUNDED => 'secondary',
            default => 'secondary',
        };
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeRejected(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
