<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'shiprocket_order_id',
        'shiprocket_shipment_id',
        'status',
        'carrier',
        'tracking_number',
        'awb_code',
        'estimated_delivery',
        'actual_delivery',
        'pickup_scheduled_date',
        'shipped_date',
        'delivered_date',
        'shiprocket_response',
        'courier_response',
        'tracking_data',
        'return_data',
        'notes'
    ];

    protected $casts = [
        'estimated_delivery' => 'datetime',
        'actual_delivery' => 'datetime',
        'pickup_scheduled_date' => 'datetime',
        'shipped_date' => 'datetime',
        'delivered_date' => 'datetime',
        'shiprocket_response' => 'array',
        'courier_response' => 'array',
        'tracking_data' => 'array',
        'return_data' => 'array',
    ];

    // Shipment statuses
    const STATUS_CREATED = 'created';
    const STATUS_COURIER_ASSIGNED = 'courier_assigned';
    const STATUS_PICKUP_SCHEDULED = 'pickup_scheduled';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_RTO = 'rto';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_LOST = 'lost';
    const STATUS_DAMAGED = 'damaged';

    /**
     * Get all possible shipment statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_CREATED => 'Created',
            self::STATUS_COURIER_ASSIGNED => 'Courier Assigned',
            self::STATUS_PICKUP_SCHEDULED => 'Pickup Scheduled',
            self::STATUS_PICKED_UP => 'Picked Up',
            self::STATUS_IN_TRANSIT => 'In Transit',
            self::STATUS_OUT_FOR_DELIVERY => 'Out for Delivery',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_RTO => 'RTO (Return to Origin)',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_LOST => 'Lost',
            self::STATUS_DAMAGED => 'Damaged',
        ];
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Relationship with Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if shipment is trackable
     */
    public function isTrackable(): bool
    {
        return !empty($this->tracking_number) || !empty($this->awb_code);
    }

    /**
     * Check if shipment is delivered
     */
    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Check if shipment is in transit
     */
    public function isInTransit(): bool
    {
        return in_array($this->status, [
            self::STATUS_PICKED_UP,
            self::STATUS_IN_TRANSIT,
            self::STATUS_OUT_FOR_DELIVERY
        ]);
    }

    /**
     * Check if shipment has issues
     */
    public function hasIssues(): bool
    {
        return in_array($this->status, [
            self::STATUS_RTO,
            self::STATUS_LOST,
            self::STATUS_DAMAGED
        ]);
    }

    /**
     * Get tracking URL
     */
    public function getTrackingUrlAttribute(): ?string
    {
        if (!$this->tracking_number) {
            return null;
        }

        // Return Shiprocket tracking URL
        return "https://shiprocket.co/tracking/{$this->tracking_number}";
    }

    /**
     * Get estimated delivery date in readable format
     */
    public function getEstimatedDeliveryFormattedAttribute(): string
    {
        if (!$this->estimated_delivery) {
            return 'Not Available';
        }

        return $this->estimated_delivery->format('M d, Y');
    }

    /**
     * Get days since shipment created
     */
    public function getDaysSinceCreatedAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Scope for active shipments
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
            self::STATUS_RTO
        ]);
    }

    /**
     * Scope for delivered shipments
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    /**
     * Scope for problem shipments
     */
    public function scopeWithIssues($query)
    {
        return $query->whereIn('status', [
            self::STATUS_RTO,
            self::STATUS_LOST,
            self::STATUS_DAMAGED
        ]);
    }
}