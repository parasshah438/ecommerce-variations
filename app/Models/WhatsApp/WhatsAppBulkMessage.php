<?php

namespace App\Models\WhatsApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class WhatsAppBulkMessage extends Model
{
    use HasFactory;

    protected $table = 'whats_app_bulk_messages';

    protected $fillable = [
        'user_id',
        'batch_id',
        'name',
        'message_type',
        'content',
        'template_id',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'failed_count',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'error_message'
    ];

    protected $casts = [
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'delivered_count' => 'integer',
        'failed_count' => 'integer',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'scheduled_at',
        'started_at',
        'completed_at'
    ];

    /**
     * Get the user that owns the bulk message
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the template used for this bulk message
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(WhatsAppTemplate::class, 'template_id');
    }

    /**
     * Get individual messages in this bulk send
     */
    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'bulk_message_id');
    }

    /**
     * Scope for completed bulk messages
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for processing bulk messages
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope for scheduled bulk messages
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope for failed bulk messages
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Get success rate percentage
     */
    public function getSuccessRateAttribute()
    {
        if ($this->total_recipients == 0) {
            return 0;
        }

        return round(($this->sent_count / $this->total_recipients) * 100, 2);
    }

    /**
     * Get delivery rate percentage
     */
    public function getDeliveryRateAttribute()
    {
        if ($this->sent_count == 0) {
            return 0;
        }

        return round(($this->delivered_count / $this->sent_count) * 100, 2);
    }

    /**
     * Get failure rate percentage
     */
    public function getFailureRateAttribute()
    {
        if ($this->total_recipients == 0) {
            return 0;
        }

        return round(($this->failed_count / $this->total_recipients) * 100, 2);
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'scheduled' => '<span class="badge bg-warning">Scheduled</span>',
            'processing' => '<span class="badge bg-info">Processing</span>',
            'completed' => '<span class="badge bg-success">Completed</span>',
            'failed' => '<span class="badge bg-danger">Failed</span>',
            'cancelled' => '<span class="badge bg-dark">Cancelled</span>'
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>';
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->total_recipients == 0) {
            return 0;
        }

        $processed = $this->sent_count + $this->failed_count;
        return round(($processed / $this->total_recipients) * 100, 2);
    }

    /**
     * Mark as started
     */
    public function markAsStarted()
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now()
        ]);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted()
    {
        // Update counts from individual messages
        $this->updateCounts();
        
        $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now()
        ]);
    }

    /**
     * Update message counts from individual messages
     */
    public function updateCounts()
    {
        $this->update([
            'sent_count' => $this->messages()->where('status', 'sent')->count(),
            'delivered_count' => $this->messages()->where('status', 'delivered')->count(),
            'failed_count' => $this->messages()->where('status', 'failed')->count()
        ]);
    }

    /**
     * Get duration in human readable format
     */
    public function getDurationAttribute()
    {
        if (!$this->started_at) {
            return 'Not started';
        }

        $endTime = $this->completed_at ?? now();
        return $this->started_at->diffForHumans($endTime, true);
    }

    /**
     * Get estimated completion time
     */
    public function getEstimatedCompletionAttribute()
    {
        if ($this->status !== 'processing') {
            return null;
        }

        if (!$this->started_at || $this->sent_count == 0) {
            return null;
        }

        $elapsed = now()->diffInSeconds($this->started_at);
        $rate = $this->sent_count / $elapsed; // messages per second
        $remaining = $this->total_recipients - ($this->sent_count + $this->failed_count);
        
        if ($rate > 0) {
            $estimatedSeconds = $remaining / $rate;
            return now()->addSeconds($estimatedSeconds);
        }

        return null;
    }

    /**
     * Cancel bulk message
     */
    public function cancel()
    {
        if (in_array($this->status, ['draft', 'scheduled', 'processing'])) {
            $this->update(['status' => 'cancelled']);
            return true;
        }

        return false;
    }

    /**
     * Get short content for display
     */
    public function getShortContentAttribute()
    {
        if (empty($this->content)) {
            return $this->template ? $this->template->short_content : '[No content]';
        }
        
        return \Illuminate\Support\Str::limit($this->content, 50);
    }
}