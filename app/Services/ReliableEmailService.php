<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class ReliableEmailService
{
    /**
     * Send email with automatic logging and retry mechanism
     */
    public function sendEmail(
        string $emailType,
        string $recipientEmail,
        Mailable $mailable,
        ?User $user = null,
        array $emailData = [],
        int $maxAttempts = 3
    ): EmailLog {
        // Create email log entry
        $emailLog = EmailLog::create([
            'email_type' => $emailType,
            'recipient_email' => $recipientEmail,
            'user_id' => $user?->id,
            'subject' => $this->extractSubject($mailable),
            'status' => EmailLog::STATUS_PENDING,
            'attempts' => 0,
            'max_attempts' => $maxAttempts,
            'email_data' => $emailData,
        ]);

        // Attempt to send email
        $this->attemptSend($emailLog, $mailable);

        return $emailLog;
    }

    /**
     * Attempt to send a single email
     */
    public function attemptSend(EmailLog $emailLog, ?Mailable $mailable = null): bool
    {
        if (!$emailLog->canRetry()) {
            return false;
        }

        try {
            // If no mailable provided, reconstruct it from log data
            if (!$mailable) {
                $mailable = $this->reconstructMailable($emailLog);
            }

            // Attempt to send
            Mail::to($emailLog->recipient_email)->send($mailable);
            
            // Mark as sent
            $emailLog->markAsSent();
            
            Log::info("Email sent successfully", [
                'email_log_id' => $emailLog->id,
                'email_type' => $emailLog->email_type,
                'recipient' => $emailLog->recipient_email,
                'attempts' => $emailLog->attempts + 1
            ]);
            
            return true;
            
        } catch (Exception $e) {
            // Mark as failed/retry
            $emailLog->markAsFailed($e->getMessage());
            
            Log::error("Email send failed", [
                'email_log_id' => $emailLog->id,
                'email_type' => $emailLog->email_type,
                'recipient' => $emailLog->recipient_email,
                'attempts' => $emailLog->attempts,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Process retry queue - call this from cron
     */
    public function processRetryQueue(): array
    {
        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        $retryableEmails = EmailLog::getRetryableEmails();
        
        foreach ($retryableEmails as $emailLog) {
            $results['processed']++;
            
            try {
                if ($this->attemptSend($emailLog)) {
                    $results['successful']++;
                } else {
                    $results['failed']++;
                }
            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Email ID {$emailLog->id}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Get email statistics
     */
    public function getStats(): array
    {
        return [
            'total_emails' => EmailLog::count(),
            'sent' => EmailLog::where('status', EmailLog::STATUS_SENT)->count(),
            'pending' => EmailLog::where('status', EmailLog::STATUS_PENDING)->count(),
            'retry' => EmailLog::where('status', EmailLog::STATUS_RETRY)->count(),
            'failed' => EmailLog::where('status', EmailLog::STATUS_FAILED)->count(),
            'recent_failures' => EmailLog::where('status', EmailLog::STATUS_FAILED)
                ->where('last_attempt_at', '>=', now()->subHours(24))
                ->count(),
        ];
    }

    /**
     * Manually retry failed emails
     */
    public function retryFailedEmails(): array
    {
        $failedEmails = EmailLog::getFailedEmails();
        $results = ['retried' => 0, 'successful' => 0];
        
        foreach ($failedEmails as $emailLog) {
            // Reset attempts to allow retry
            $emailLog->update([
                'status' => EmailLog::STATUS_RETRY,
                'attempts' => 0,
                'next_retry_at' => now()
            ]);
            
            $results['retried']++;
            
            if ($this->attemptSend($emailLog)) {
                $results['successful']++;
            }
        }
        
        return $results;
    }

    /**
     * Extract subject from mailable
     */
    private function extractSubject(Mailable $mailable): string
    {
        try {
            $envelope = $mailable->envelope();
            return $envelope->subject ?? 'No Subject';
        } catch (Exception $e) {
            return 'Unknown Subject';
        }
    }

    /**
     * Reconstruct mailable from email log data
     */
    private function reconstructMailable(EmailLog $emailLog): ?Mailable
    {
        try {
            switch ($emailLog->email_type) {
                case 'welcome':
                    if ($emailLog->user) {
                        return new \App\Mail\WelcomeEmail($emailLog->user);
                    }
                    break;
                
                case 'order_confirmation':
                    // Add order confirmation email reconstruction
                    break;
                
                case 'password_reset':
                    // Add password reset email reconstruction
                    break;
                
                // Add more email types as needed
            }
        } catch (Exception $e) {
            Log::error("Failed to reconstruct mailable for email log {$emailLog->id}: " . $e->getMessage());
        }
        
        return null;
    }
}
