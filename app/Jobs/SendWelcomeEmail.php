<?php

namespace App\Jobs;

use App\Mail\WelcomeEmail;
use App\Models\User;
use App\Services\ReliableEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmail implements ShouldQueue
{
    use Queueable;

    public $user;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 1; // Let ReliableEmailService handle retries

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $emailService = app(ReliableEmailService::class);
            $welcomeEmail = new WelcomeEmail($this->user);
            
            $emailLog = $emailService->sendEmail(
                emailType: 'welcome',
                recipientEmail: $this->user->email,
                mailable: $welcomeEmail,
                user: $this->user,
                emailData: [
                    'user_name' => $this->user->name,
                    'registration_date' => $this->user->created_at->toDateTimeString(),
                ],
                maxAttempts: 5 // Allow 5 attempts for welcome emails
            );
            
            Log::info('Welcome email job completed', [
                'user_id' => $this->user->id,
                'email_log_id' => $emailLog->id,
                'status' => $emailLog->status
            ]);
            
        } catch (\Exception $e) {
            Log::error('Welcome email job failed', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Welcome email job permanently failed', [
            'user_id' => $this->user->id,
            'error' => $exception->getMessage()
        ]);
    }
}
