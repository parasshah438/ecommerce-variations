<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Address;

/**
 * AI-Powered Location Intelligence Service
 * Uses machine learning concepts for smart location suggestions
 */
class LocationIntelligenceService
{
    /**
     * Predict user's likely location based on patterns
     */
    public function predictUserLocation($userId = null)
    {
        $userId = $userId ?? Auth::id();
        if (!$userId) return null;
        
        $cacheKey = "location_prediction_{$userId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($userId) {
            // Get user's historical data
            $user = User::with(['addresses', 'orders.address'])->find($userId);
            
            if (!$user) return null;
            
            // Analyze patterns
            $locationScore = [];
            
            // 1. Most frequently used addresses (weight: 40%)
            foreach ($user->addresses as $address) {
                $key = $this->getLocationKey($address);
                $locationScore[$key] = ($locationScore[$key] ?? 0) + 40;
            }
            
            // 2. Recent order addresses (weight: 30%)
            $recentOrders = $user->orders()
                ->with('address')
                ->where('created_at', '>=', now()->subDays(30))
                ->get();
                
            foreach ($recentOrders as $order) {
                if ($order->address) {
                    $key = $this->getLocationKey($order->address);
                    $locationScore[$key] = ($locationScore[$key] ?? 0) + 30;
                }
            }
            
            // 3. Default address (weight: 20%)
            $defaultAddress = $user->addresses()->where('is_default', true)->first();
            if ($defaultAddress) {
                $key = $this->getLocationKey($defaultAddress);
                $locationScore[$key] = ($locationScore[$key] ?? 0) + 20;
            }
            
            // 4. Time-based patterns (weight: 10%)
            $currentHour = now()->hour;
            if ($currentHour >= 9 && $currentHour <= 18) {
                // Business hours - prefer office addresses
                foreach ($user->addresses as $address) {
                    if (isset($address->type) && $address->type === 'office') {
                        $key = $this->getLocationKey($address);
                        $locationScore[$key] = ($locationScore[$key] ?? 0) + 10;
                    }
                }
            } else {
                // Non-business hours - prefer home addresses
                foreach ($user->addresses as $address) {
                    if (!isset($address->type) || $address->type === 'home') {
                        $key = $this->getLocationKey($address);
                        $locationScore[$key] = ($locationScore[$key] ?? 0) + 10;
                    }
                }
            }
            
            // Get highest scoring location
            if (empty($locationScore)) return null;
            
            arsort($locationScore);
            $topLocationKey = array_key_first($locationScore);
            
            // Find the address for this location
            $bestAddress = $user->addresses()
                ->where('city', 'LIKE', "%{$topLocationKey}%")
                ->orWhere('pincode', 'LIKE', "%{$topLocationKey}%")
                ->first();
                
            if (!$bestAddress) {
                $bestAddress = $user->addresses()->first();
            }
            
            return $bestAddress ? [
                'address' => $bestAddress,
                'confidence' => max($locationScore) / 100,
                'prediction_reason' => $this->getPredictionReason(max($locationScore))
            ] : null;
        });
    }
    
    /**
     * Smart address suggestions based on partial input
     */
    public function getSmartSuggestions($query, $userId = null)
    {
        $userId = $userId ?? Auth::id();
        
        $suggestions = [];
        
        // 1. User's saved addresses (highest priority)
        if ($userId) {
            $userAddresses = Address::where('user_id', $userId)
                ->where(function($q) use ($query) {
                    $q->where('area', 'LIKE', "%{$query}%")
                      ->orWhere('city', 'LIKE', "%{$query}%")
                      ->orWhere('pincode', 'LIKE', "%{$query}%")
                      ->orWhere('address_line', 'LIKE', "%{$query}%");
                })
                ->limit(3)
                ->get();
                
            foreach ($userAddresses as $address) {
                $suggestions[] = [
                    'type' => 'saved_address',
                    'display_name' => $address->area . ', ' . $address->city,
                    'full_address' => $address->address_line,
                    'pincode' => $address->pincode,
                    'priority' => 100,
                    'icon' => $address->type === 'office' ? 'building' : 'home'
                ];
            }
        }
        
        // 2. Popular locations in the same city (medium priority)
        $popularLocations = $this->getPopularLocations($query);
        foreach ($popularLocations as $location) {
            $suggestions[] = array_merge($location, [
                'type' => 'popular_location',
                'priority' => 75
            ]);
        }
        
        // 3. External API suggestions (lower priority)
        // This would integrate with Google Places, Foursquare, etc.
        
        // Sort by priority
        usort($suggestions, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });
        
        return array_slice($suggestions, 0, 10);
    }
    
    /**
     * Get location key for scoring
     */
    private function getLocationKey($address)
    {
        return $address->city . '_' . $address->pincode;
    }
    
    /**
     * Get prediction reason for user feedback
     */
    private function getPredictionReason($score)
    {
        if ($score >= 80) return 'Based on your frequent orders';
        if ($score >= 60) return 'Based on your saved addresses';
        if ($score >= 40) return 'Based on recent activity';
        return 'Based on your preferences';
    }
    
    /**
     * Get popular locations (can be enhanced with real data)
     */
    private function getPopularLocations($query)
    {
        // This is a simple implementation
        // In production, you'd query a database of popular locations
        $popularPlaces = [
            'mumbai' => [
                ['display_name' => 'Andheri West, Mumbai', 'pincode' => '400058'],
                ['display_name' => 'Bandra West, Mumbai', 'pincode' => '400050'],
                ['display_name' => 'Powai, Mumbai', 'pincode' => '400076'],
            ],
            'bangalore' => [
                ['display_name' => 'Koramangala, Bangalore', 'pincode' => '560034'],
                ['display_name' => 'Whitefield, Bangalore', 'pincode' => '560066'],
                ['display_name' => 'Indiranagar, Bangalore', 'pincode' => '560038'],
            ],
            'delhi' => [
                ['display_name' => 'Connaught Place, New Delhi', 'pincode' => '110001'],
                ['display_name' => 'Lajpat Nagar, New Delhi', 'pincode' => '110024'],
                ['display_name' => 'Karol Bagh, New Delhi', 'pincode' => '110005'],
            ]
        ];
        
        foreach ($popularPlaces as $city => $places) {
            if (stripos($query, $city) !== false) {
                return $places;
            }
        }
        
        return [];
    }
}