<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

/**
 * Location Privacy & Security Service
 * Handles user location data with privacy compliance
 */
class LocationPrivacyService
{
    /**
     * Hash sensitive location data for privacy
     */
    public function hashLocationData($locationData)
    {
        if (!is_array($locationData)) {
            return $locationData;
        }
        
        $hashedData = $locationData;
        
        // Hash sensitive fields
        $sensitiveFields = ['latitude', 'longitude', 'formatted_address'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($hashedData[$field])) {
                $hashedData[$field . '_hash'] = Hash::make($hashedData[$field]);
                // Keep approximate values for functionality
                if ($field === 'latitude' || $field === 'longitude') {
                    $hashedData[$field] = round($hashedData[$field], 2); // Reduce precision
                }
            }
        }
        
        return $hashedData;
    }
    
    /**
     * Check if user has given location consent
     */
    public function hasLocationConsent($userId = null)
    {
        $userId = $userId ?? auth()->id();
        if (!$userId) return false;
        
        return Cache::remember("location_consent_{$userId}", 86400, function () use ($userId) {
            // Check user preferences or consent records
            // This should be implemented based on your user preferences system
            return true; // Default: assume consent for now
        });
    }
    
    /**
     * Log location access for audit trail
     */
    public function logLocationAccess($type, $data = [])
    {
        $logData = [
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'access_type' => $type,
            'timestamp' => now()->toISOString(),
            'data' => $this->sanitizeLogData($data)
        ];
        
        Log::channel('location_audit')->info('Location Access', $logData);
    }
    
    /**
     * Sanitize log data to remove sensitive information
     */
    private function sanitizeLogData($data)
    {
        $sanitized = $data;
        
        // Remove or mask sensitive data
        $sensitiveKeys = ['exact_latitude', 'exact_longitude', 'full_address'];
        
        foreach ($sensitiveKeys as $key) {
            if (isset($sanitized[$key])) {
                $sanitized[$key] = '***MASKED***';
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Get location data retention period
     */
    public function getDataRetentionPeriod()
    {
        return config('location.data_retention_days', 90); // Default 90 days
    }
    
    /**
     * Clean up old location data
     */
    public function cleanupOldLocationData()
    {
        $retentionDays = $this->getDataRetentionPeriod();
        $cutoffDate = now()->subDays($retentionDays);
        
        // Clear old cache entries
        $cacheKeys = Cache::getRedis()->keys('location_*');
        foreach ($cacheKeys as $key) {
            $keyWithoutPrefix = str_replace(config('cache.prefix') . ':', '', $key);
            Cache::forget($keyWithoutPrefix);
        }
        
        Log::info("Location data cleanup completed for data older than {$cutoffDate}");
    }
}