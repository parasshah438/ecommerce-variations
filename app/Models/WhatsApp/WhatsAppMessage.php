<?php

namespace App\Models\WhatsApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class WhatsAppMessage extends Model
{
    use HasFactory;

    protected $table = 'whats_app_messages';

    protected $fillable = [
        'user_id',
        'phone',
        'message_type',
        'content',
        'media_path',
        'media_url',
        'template_id',
        'contact_id',
        'bulk_message_id',
        'batch_id',
        'ultramsg_id',
        'status',
        'error_message',
        'response_data',
        'delivered_at',
        'read_at',
        'failed_at'
    ];

    protected $casts = [
        'response_data' => 'array',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'delivered_at',
        'read_at',
        'failed_at'
    ];

    /**
     * Get the user that owns the message
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the template associated with the message
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(WhatsAppTemplate::class, 'template_id');
    }

    /**
     * Get the contact associated with the message
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(WhatsAppContact::class, 'contact_id');
    }

    /**
     * Get the bulk message this message belongs to
     */
    public function bulkMessage(): BelongsTo
    {
        return $this->belongsTo(WhatsAppBulkMessage::class, 'bulk_message_id');
    }

    /**
     * Scope for successful messages
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['sent', 'delivered', 'read']);
    }

    /**
     * Scope for failed messages
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for pending messages
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for messages by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('message_type', $type);
    }

    /**
     * Scope for messages sent today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for messages in date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get formatted phone number for display
     */
    public function getFormattedPhoneAttribute()
    {
        $phone = $this->phone;
        
        // Remove country code for display
        if (str_starts_with($phone, '91') && strlen($phone) > 10) {
            $phone = substr($phone, 2);
        }
        
        // Format as +91 XXXXX XXXXX
        if (strlen($phone) == 10) {
            return '+91 ' . substr($phone, 0, 5) . ' ' . substr($phone, 5);
        }
        
        return $this->phone;
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'sent' => '<span class="badge bg-success">Sent</span>',
            'delivered' => '<span class="badge bg-info">Delivered</span>',
            'read' => '<span class="badge bg-primary">Read</span>',
            'failed' => '<span class="badge bg-danger">Failed</span>'
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>';
    }

    /**
     * Get message type icon
     */
    public function getTypeIconAttribute()
    {
        $icons = [
            'text' => '<i class="bi bi-chat-text"></i>',
            'image' => '<i class="bi bi-image"></i>',
            'document' => '<i class="bi bi-file-text"></i>',
            'audio' => '<i class="bi bi-mic"></i>',
            'video' => '<i class="bi bi-camera-video"></i>',
            'contact' => '<i class="bi bi-person-circle"></i>',
            'location' => '<i class="bi bi-geo-alt"></i>',
            'template' => '<i class="bi bi-file-earmark-text"></i>'
        ];

        return $icons[$this->message_type] ?? '<i class="bi bi-chat-square"></i>';
    }

    /**
     * Check if message has media
     */
    public function hasMedia()
    {
        return !empty($this->media_path) || !empty($this->media_url);
    }

    /**
     * Get media URL for display
     */
    public function getMediaDisplayUrl()
    {
        if ($this->media_url) {
            return $this->media_url;
        }
        
        if ($this->media_path) {
            return asset('storage/' . $this->media_path);
        }
        
        return null;
    }

    /**
     * Mark message as delivered
     */
    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now()
        ]);
    }

    /**
     * Mark message as read
     */
    public function markAsRead()
    {
        $this->update([
            'status' => 'read',
            'read_at' => now()
        ]);
    }

    /**
     * Mark message as failed
     */
    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $errorMessage
        ]);
    }

    /**
     * Get message content for display (truncated)
     */
    public function getShortContentAttribute()
    {
        if (empty($this->content)) {
            return $this->message_type === 'image' ? '[Image]' : '[' . ucfirst($this->message_type) . ']';
        }
        
        return \Illuminate\Support\Str::limit($this->content, 50);
    }

    /**
     * Get time ago for display
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}