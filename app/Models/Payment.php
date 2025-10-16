<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'payment_id',
        'gateway',
        'gateway_payment_id',
        'gateway_order_id',
        'transaction_id',
        'status',
        'amount',
        'currency',
        'method',
        'payment_method',
        'payment_status',
        'gateway_response',
        'metadata',
        'failure_reason',
        'receipt_number',
        'paid_at',
        'failed_at',
        'cancelled_at',
        'refunded_at',
        'ip_address',
        'user_agent',
        'billing_details'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'metadata' => 'array',
        'billing_details' => 'array',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    // Payment statuses
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_REFUNDED = 'refunded';
    const PAYMENT_STATUS_CANCELLED = 'cancelled';

    // Payment gateways
    const GATEWAY_RAZORPAY = 'razorpay';
    const GATEWAY_COD = 'cod';
    const GATEWAY_STRIPE = 'stripe';

    /**
     * Relationships
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeSuccessful($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PAID);
    }

    public function scopeFailed($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_FAILED);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PENDING);
    }

    public function scopeByGateway($query, $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Accessors
     */
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getIsSuccessfulAttribute()
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function getIsFailedAttribute()
    {
        return $this->payment_status === self::PAYMENT_STATUS_FAILED;
    }

    public function getIsPendingAttribute()
    {
        return $this->payment_status === self::PAYMENT_STATUS_PENDING;
    }

    /**
     * Helper methods
     */
    public function markAsPaid($gatewayResponse = null)
    {
        $this->update([
            'payment_status' => self::PAYMENT_STATUS_PAID,
            'status' => self::STATUS_COMPLETED,
            'paid_at' => now(),
            'gateway_response' => $gatewayResponse
        ]);
    }

    public function markAsFailed($reason = null, $gatewayResponse = null)
    {
        $this->update([
            'payment_status' => self::PAYMENT_STATUS_FAILED,
            'status' => self::STATUS_FAILED,
            'failed_at' => now(),
            'failure_reason' => $reason,
            'gateway_response' => $gatewayResponse
        ]);
    }

    public function markAsCancelled($reason = null)
    {
        $this->update([
            'payment_status' => self::PAYMENT_STATUS_CANCELLED,
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'failure_reason' => $reason
        ]);
    }

    public function markAsRefunded($gatewayResponse = null)
    {
        $this->update([
            'payment_status' => self::PAYMENT_STATUS_REFUNDED,
            'status' => self::STATUS_REFUNDED,
            'refunded_at' => now(),
            'gateway_response' => $gatewayResponse
        ]);
    }

    /**
     * Generate unique payment ID
     */
    public static function generatePaymentId()
    {
        return 'PAY_' . strtoupper(uniqid()) . '_' . time();
    }

    /**
     * Get all possible statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REFUNDED => 'Refunded',
        ];
    }

    public static function getPaymentStatuses()
    {
        return [
            self::PAYMENT_STATUS_PENDING => 'Pending',
            self::PAYMENT_STATUS_PAID => 'Paid',
            self::PAYMENT_STATUS_FAILED => 'Failed',
            self::PAYMENT_STATUS_REFUNDED => 'Refunded',
            self::PAYMENT_STATUS_CANCELLED => 'Cancelled',
        ];
    }
}
