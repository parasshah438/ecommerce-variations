<?php

namespace App\Services;

use App\Models\UserOtp;
use App\Mail\OtpMail;
use App\Services\ReliableEmailService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Carbon;

class OtpService
{
    protected $reliableEmailService;

    public function __construct(ReliableEmailService $reliableEmailService)
    {
        $this->reliableEmailService = $reliableEmailService;
    }

    /**
     * Rate limiting settings
     */
    const MAX_OTP_REQUESTS_PER_HOUR = 50;  // Increased for development
    const MAX_OTP_REQUESTS_PER_DAY = 100;  // Increased for development

    /**
     * Send OTP to email
     */
    public function sendOtp($identifier, $type = 'email', $testMode = false)
    {
        try {
            // Check rate limiting
            $rateLimitCheck = $this->checkRateLimit($identifier);
            if (!$rateLimitCheck['allowed']) {
                return $rateLimitCheck;
            }

            // Check if user can request OTP (cooldown)
            $canRequest = UserOtp::canRequestOtp($identifier);
            if (!$canRequest['can_request']) {
                return [
                    'success' => false,
                    'message' => 'Please wait before requesting another OTP',
                    'remaining_seconds' => $canRequest['remaining_seconds'],
                    'type' => 'cooldown'
                ];
            }

            // Generate OTP
            $otpResult = UserOtp::generateOtp($identifier, $type, $testMode);
            
            if (!$otpResult['success']) {
                return $otpResult;
            }

            $otpRecord = $otpResult['otp_record'];

            // Apply rate limiting
            $this->applyRateLimit($identifier);

            // Send OTP via email
            if ($type === 'email') {
                $emailResult = $this->sendOtpEmail($identifier, $otpRecord->otp, $testMode);
                
                if (!$emailResult['success']) {
                    // Mark OTP as failed
                    $otpRecord->update(['is_used' => true]);
                    
                    return [
                        'success' => false,
                        'message' => 'Failed to send OTP email. Please try again.',
                        'error' => $emailResult['message'] ?? 'Email delivery failed'
                    ];
                }
                
                Log::info('OTP email queued successfully', [
                    'identifier' => $identifier,
                    'email_log_id' => $emailResult['email_log_id'] ?? null
                ]);
            }

            Log::info('OTP sent successfully', [
                'identifier' => $identifier,
                'type' => $type,
                'test_mode' => $testMode,
                'expires_at' => $otpRecord->expires_at
            ]);

            return [
                'success' => true,
                'message' => $testMode ? 'Test OTP sent (check logs for OTP)' : 'OTP sent successfully',
                'expires_in_minutes' => UserOtp::EXPIRY_MINUTES,
                'expires_at' => $otpRecord->expires_at,
                'test_otp' => $testMode ? $otpRecord->otp : null,
                'otp_id' => $otpRecord->id
            ];

        } catch (\Exception $e) {
            Log::error('OTP sending failed', [
                'identifier' => $identifier,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again later.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOtp($identifier, $otp)
    {
        try {
            $result = UserOtp::verifyOtp($identifier, $otp);

            Log::info('OTP verification attempt', [
                'identifier' => $identifier,
                'success' => $result['success'],
                'message' => $result['message']
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('OTP verification failed', [
                'identifier' => $identifier,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'OTP verification failed. Please try again.'
            ];
        }
    }

    /**
     * Get OTP status for identifier
     */
    public function getOtpStatus($identifier)
    {
        $activeOtp = UserOtp::getActiveOtp($identifier);
        
        if (!$activeOtp) {
            return [
                'has_active_otp' => false,
                'can_request_new' => true
            ];
        }

        $canRequest = UserOtp::canRequestOtp($identifier);

        return [
            'has_active_otp' => true,
            'otp_id' => $activeOtp->id,
            'expires_at' => $activeOtp->expires_at,
            'remaining_seconds' => $activeOtp->getRemainingTime(),
            'attempts_made' => $activeOtp->attempts,
            'max_attempts' => UserOtp::MAX_ATTEMPTS,
            'can_request_new' => $canRequest['can_request'],
            'cooldown_remaining' => $canRequest['can_request'] ? 0 : $canRequest['remaining_seconds']
        ];
    }

    /**
     * Check rate limiting
     */
    protected function checkRateLimit($identifier)
    {
        $hourlyKey = 'otp_hourly:' . $identifier;
        $dailyKey = 'otp_daily:' . $identifier;

        // Check hourly limit
        if (RateLimiter::tooManyAttempts($hourlyKey, self::MAX_OTP_REQUESTS_PER_HOUR)) {
            $retryAfter = RateLimiter::availableIn($hourlyKey);
            return [
                'allowed' => false,
                'success' => false,
                'message' => 'Too many OTP requests. Please try again in ' . gmdate('H:i:s', $retryAfter),
                'retry_after_seconds' => $retryAfter,
                'type' => 'rate_limit_hourly'
            ];
        }

        // Check daily limit
        if (RateLimiter::tooManyAttempts($dailyKey, self::MAX_OTP_REQUESTS_PER_DAY)) {
            $retryAfter = RateLimiter::availableIn($dailyKey);
            return [
                'allowed' => false,
                'success' => false,
                'message' => 'Daily OTP limit exceeded. Please try again tomorrow.',
                'retry_after_seconds' => $retryAfter,
                'type' => 'rate_limit_daily'
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Apply rate limiting
     */
    protected function applyRateLimit($identifier)
    {
        $hourlyKey = 'otp_hourly:' . $identifier;
        $dailyKey = 'otp_daily:' . $identifier;

        // Apply hourly rate limit (1 hour)
        RateLimiter::hit($hourlyKey, 3600);
        
        // Apply daily rate limit (24 hours)
        RateLimiter::hit($dailyKey, 86400);
    }

    /**
     * Send OTP email
     */
    protected function sendOtpEmail($email, $otp, $testMode = false)
    {
        try {
            $otpMail = new OtpMail($otp, $testMode);
            
            $emailLog = $this->reliableEmailService->sendEmail(
                'otp_verification',
                $email,
                $otpMail,
                null,
                [
                    'otp' => $otp,
                    'test_mode' => $testMode,
                    'expires_at' => now()->addMinutes(UserOtp::EXPIRY_MINUTES)
                ]
            );

            if ($testMode) {
                Log::info('Test OTP Email', [
                    'email' => $email,
                    'otp' => $otp,
                    'email_log_id' => $emailLog->id
                ]);
            }

            return [
                'success' => true,
                'email_log_id' => $emailLog->id
            ];

        } catch (\Exception $e) {
            Log::error('OTP email sending failed', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Cleanup expired OTPs
     */
    public function cleanup()
    {
        return UserOtp::cleanup();
    }

    /**
     * Get OTP statistics
     */
    public function getStats($identifier = null)
    {
        $stats = UserOtp::getStats($identifier);
        
        // Add rate limiting info
        if ($identifier) {
            $hourlyKey = 'otp_hourly:' . $identifier;
            $dailyKey = 'otp_daily:' . $identifier;
            
            $stats['rate_limit'] = [
                'hourly_requests' => RateLimiter::attempts($hourlyKey),
                'hourly_limit' => self::MAX_OTP_REQUESTS_PER_HOUR,
                'daily_requests' => RateLimiter::attempts($dailyKey),
                'daily_limit' => self::MAX_OTP_REQUESTS_PER_DAY,
                'hourly_reset_in' => RateLimiter::availableIn($hourlyKey),
                'daily_reset_in' => RateLimiter::availableIn($dailyKey)
            ];
        }

        return $stats;
    }

    /**
     * Force expire OTP
     */
    public function expireOtp($identifier)
    {
        $activeOtp = UserOtp::getActiveOtp($identifier);
        
        if ($activeOtp) {
            $activeOtp->update(['is_used' => true]);
            return true;
        }

        return false;
    }

    /**
     * Resend OTP (with cooldown check)
     */
    public function resendOtp($identifier, $type = 'email', $testMode = false)
    {
        // First expire current OTP
        $this->expireOtp($identifier);

        // Then send new one
        return $this->sendOtp($identifier, $type, $testMode);
    }
}
