<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'address_id', 
        'status', 
        'total', 
        'payment_method',
        'payment_gateway',
        'payment_status',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'payment_data',
        'notes',
        'cancelled_at',
        'returned_at',
        'refunded_at'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'payment_data' => 'array',
        'cancelled_at' => 'datetime',
        'returned_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    // Order statuses
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_RETURNED = 'returned';
    const STATUS_REFUNDED = 'refunded';

    // Payment statuses
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_FAILED = 'failed';
    const PAYMENT_REFUNDED = 'refunded';

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latest();
    }

    public function successfulPayments()
    {
        return $this->hasMany(Payment::class)->where('payment_status', Payment::PAYMENT_STATUS_PAID);
    }

    /**
     * Get all possible order statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_SHIPPED => 'Shipped',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_RETURNED => 'Returned',
            self::STATUS_REFUNDED => 'Refunded',
        ];
    }

    /**
     * Get all possible payment statuses
     */
    public static function getPaymentStatuses()
    {
        return [
            self::PAYMENT_PENDING => 'Pending',
            self::PAYMENT_PAID => 'Paid',
            self::PAYMENT_FAILED => 'Failed',
            self::PAYMENT_REFUNDED => 'Refunded',
        ];
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
        ]);
    }

    /**
     * Check if order can be returned
     */
    public function canBeReturned()
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute()
    {
        return self::getStatuses()[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get formatted payment status
     */
    public function getFormattedPaymentStatusAttribute()
    {
        return self::getPaymentStatuses()[$this->payment_status] ?? ucfirst($this->payment_status);
    }
}
