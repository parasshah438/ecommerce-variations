<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    protected $fillable = [
        'email_type',
        'recipient_email',
        'user_id',
        'subject',
        'status',
        'attempts',
        'max_attempts',
        'error_message',
        'email_data',
        'sent_at',
        'last_attempt_at',
        'next_retry_at',
    ];

    protected $casts = [
        'email_data' => 'array',
        'sent_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'next_retry_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_RETRY = 'retry';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this email can be retried
     */
    public function canRetry(): bool
    {
        return $this->attempts < $this->max_attempts && 
               in_array($this->status, [self::STATUS_PENDING, self::STATUS_RETRY, self::STATUS_FAILED]);
    }

    /**
     * Mark email as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'error_message' => null
        ]);
    }

    /**
     * Mark email as failed with error message
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->increment('attempts');
        
        if ($this->attempts >= $this->max_attempts) {
            $this->update([
                'status' => self::STATUS_FAILED,
                'error_message' => $errorMessage,
                'last_attempt_at' => now(),
                'next_retry_at' => null
            ]);
        } else {
            // Schedule retry with exponential backoff
            $retryDelay = pow(2, $this->attempts) * 5; // 5, 10, 20 minutes
            
            $this->update([
                'status' => self::STATUS_RETRY,
                'error_message' => $errorMessage,
                'last_attempt_at' => now(),
                'next_retry_at' => now()->addMinutes($retryDelay)
            ]);
        }
    }

    /**
     * Get emails ready for retry
     */
    public static function getRetryableEmails()
    {
        return self::where('status', self::STATUS_RETRY)
            ->where('next_retry_at', '<=', now())
            ->where('attempts', '<', \DB::raw('max_attempts'))
            ->get();
    }

    /**
     * Get failed emails for manual review
     */
    public static function getFailedEmails()
    {
        return self::where('status', self::STATUS_FAILED)->get();
    }
}
