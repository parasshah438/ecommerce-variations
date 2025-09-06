<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'identifier',
        'identifier_type',
        'otp',
        'attempts',
        'expires_at',
        'verified_at',
        'is_used',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'is_used' => 'boolean'
    ];

    /**
     * Maximum OTP attempts allowed
     */
    const MAX_ATTEMPTS = 3;

    /**
     * OTP expiry time in minutes
     */
    const EXPIRY_MINUTES = 5;

    /**
     * Cooldown time between OTP requests in minutes
     */
    const COOLDOWN_MINUTES = 1;

    /**
     * Generate a new OTP for identifier
     */
    public static function generateOtp($identifier, $type = 'email', $testMode = false)
    {
        // For testing, use default OTP 123456
        $otp = $testMode ? '123456' : str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        // Clean up expired OTPs for this identifier
        static::where('identifier', $identifier)
            ->where('expires_at', '<', now())
            ->delete();

        // Check if there's a recent OTP request (cooldown)
        $recentOtp = static::where('identifier', $identifier)
            ->where('created_at', '>', now()->subMinutes(static::COOLDOWN_MINUTES))
            ->where('is_used', false)
            ->first();

        if ($recentOtp) {
            return [
                'success' => false,
                'message' => 'Please wait before requesting another OTP',
                'cooldown_until' => $recentOtp->created_at->addMinutes(static::COOLDOWN_MINUTES),
                'remaining_seconds' => max(0, (int) now()->diffInSeconds($recentOtp->created_at->addMinutes(static::COOLDOWN_MINUTES), false))
            ];
        }

        // Create new OTP
        $userOtp = static::create([
            'identifier' => $identifier,
            'identifier_type' => $type,
            'otp' => $otp,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(static::EXPIRY_MINUTES),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return [
            'success' => true,
            'otp_record' => $userOtp,
            'expires_in_minutes' => static::EXPIRY_MINUTES
        ];
    }

    /**
     * Verify OTP
     */
    public static function verifyOtp($identifier, $otp)
    {
        $otpRecord = static::where('identifier', $identifier)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ];
        }

        // Increment attempts
        $otpRecord->increment('attempts');

        // Check if max attempts exceeded
        if ($otpRecord->attempts > static::MAX_ATTEMPTS) {
            $otpRecord->update(['is_used' => true]);
            return [
                'success' => false,
                'message' => 'Maximum OTP attempts exceeded. Please request a new OTP.'
            ];
        }

        // Verify OTP
        if ($otpRecord->otp === $otp) {
            $otpRecord->update([
                'verified_at' => now(),
                'is_used' => true
            ]);

            return [
                'success' => true,
                'message' => 'OTP verified successfully',
                'otp_record' => $otpRecord
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid OTP',
            'attempts_remaining' => static::MAX_ATTEMPTS - $otpRecord->attempts
        ];
    }

    /**
     * Check if identifier can request new OTP
     */
    public static function canRequestOtp($identifier)
    {
        $recentOtp = static::where('identifier', $identifier)
            ->where('created_at', '>', now()->subMinutes(static::COOLDOWN_MINUTES))
            ->where('is_used', false)
            ->first();

        if ($recentOtp) {
            return [
                'can_request' => false,
                'cooldown_until' => $recentOtp->created_at->addMinutes(static::COOLDOWN_MINUTES),
                'remaining_seconds' => max(0, (int) now()->diffInSeconds($recentOtp->created_at->addMinutes(static::COOLDOWN_MINUTES), false))
            ];
        }

        return ['can_request' => true];
    }

    /**
     * Get active OTP for identifier
     */
    public static function getActiveOtp($identifier)
    {
        return static::where('identifier', $identifier)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Cleanup expired OTPs
     */
    public static function cleanup()
    {
        return static::where('expires_at', '<', now()->subHours(24))->delete();
    }

    /**
     * Get OTP statistics
     */
    public static function getStats($identifier = null)
    {
        $query = static::query();
        
        if ($identifier) {
            $query->where('identifier', $identifier);
        }

        return [
            'total_generated' => $query->count(),
            'total_verified' => $query->whereNotNull('verified_at')->count(),
            'total_expired' => $query->where('expires_at', '<', now())->where('verified_at', null)->count(),
            'success_rate' => $query->count() > 0 ? round(($query->whereNotNull('verified_at')->count() / $query->count()) * 100, 2) : 0
        ];
    }

    /**
     * Scope for active OTPs
     */
    public function scopeActive($query)
    {
        return $query->where('is_used', false)
            ->where('expires_at', '>', now());
    }

    /**
     * Scope for expired OTPs
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired()
    {
        return $this->expires_at < now();
    }

    /**
     * Check if OTP is verified
     */
    public function isVerified()
    {
        return !is_null($this->verified_at);
    }

    /**
     * Get remaining time for OTP
     */
    public function getRemainingTime()
    {
        if ($this->isExpired()) {
            return 0;
        }

        // Return integer seconds to avoid decimal display issues
        return max(0, (int) now()->diffInSeconds($this->expires_at, false));
    }

    /**
     * Get time until next OTP can be requested
     */
    public function getCooldownTime()
    {
        $cooldownUntil = $this->created_at->addMinutes(static::COOLDOWN_MINUTES);
        
        if (now() > $cooldownUntil) {
            return 0;
        }

        // Return integer seconds to avoid decimal display issues
        return max(0, (int) now()->diffInSeconds($cooldownUntil, false));
    }
}