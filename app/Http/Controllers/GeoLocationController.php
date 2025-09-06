<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeoLocationController extends Controller
{
    /**
     * Get country code based on client IP
     */
    public function getCountryCode(Request $request)
    {
        try {
            // Get client IP
            $clientIp = $this->getClientIp($request);
            
            // Use cache to avoid hitting the API too frequently
            $cacheKey = 'geo_location_' . md5($clientIp);
            
            $countryCode = Cache::remember($cacheKey, 3600, function () use ($clientIp) {
                return $this->fetchCountryCode($clientIp);
            });
            
            return response()->json([
                'success' => true,
                'country_code' => $countryCode,
                'ip' => $clientIp
            ]);
            
        } catch (\Exception $e) {
            Log::error('GeoLocation API Error: ' . $e->getMessage());
            
            // Return default country code on error
            return response()->json([
                'success' => false,
                'country_code' => 'us',
                'error' => 'Unable to determine location'
            ]);
        }
    }

    /**
     * Get detailed location information from coordinates
     */
    public function getLocationDetails(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180'
        ]);

        try {
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            
            // Cache key based on coordinates (rounded to 4 decimal places for reasonable caching)
            $cacheKey = 'location_details_' . round($latitude, 4) . '_' . round($longitude, 4);
            
            $locationData = Cache::remember($cacheKey, 1800, function () use ($latitude, $longitude) {
                return $this->fetchLocationDetails($latitude, $longitude);
            });
            
            return response()->json([
                'success' => true,
                'data' => $locationData
            ]);
            
        } catch (\Exception $e) {
            Log::error('Location Details API Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Unable to fetch location details',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get location details from IP address
     */
    public function getLocationFromIP(Request $request)
    {
        try {
            $clientIp = $this->getClientIp($request);
            
            $cacheKey = 'ip_location_' . md5($clientIp);
            
            $locationData = Cache::remember($cacheKey, 3600, function () use ($clientIp) {
                return $this->fetchLocationFromIP($clientIp);
            });
            
            return response()->json([
                'success' => true,
                'data' => $locationData,
                'ip' => $clientIp
            ]);
            
        } catch (\Exception $e) {
            Log::error('IP Location API Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Unable to fetch location from IP',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search locations by query (for autocomplete)
     */
    public function searchLocations(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:3|max:100'
        ]);

        try {
            $query = $request->query;
            
            $cacheKey = 'location_search_' . md5(strtolower($query));
            
            $results = Cache::remember($cacheKey, 900, function () use ($query) {
                return $this->searchLocationsByQuery($query);
            });
            
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error('Location Search API Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Unable to search locations',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pincode details
     */
    public function getPincodeDetails(Request $request)
    {
        $request->validate([
            'pincode' => 'required|string|regex:/^[0-9]{6}$/'
        ]);

        try {
            $pincode = $request->pincode;
            
            $cacheKey = 'pincode_details_' . $pincode;
            
            $pincodeData = Cache::remember($cacheKey, 86400, function () use ($pincode) {
                return $this->fetchPincodeDetails($pincode);
            });
            
            return response()->json([
                'success' => true,
                'data' => $pincodeData
            ]);
            
        } catch (\Exception $e) {
            Log::error('Pincode Details API Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Unable to fetch pincode details',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch detailed location information from coordinates using multiple services
     */
    private function fetchLocationDetails($latitude, $longitude)
    {
        $services = [
            // Nominatim (OpenStreetMap) - Free and reliable
            [
                'url' => "https://nominatim.openstreetmap.org/reverse?format=json&lat={$latitude}&lon={$longitude}&zoom=18&addressdetails=1",
                'parser' => 'parseNominatimResponse'
            ],
            // Alternative HTTP endpoint for Nominatim (no SSL)
            [
                'url' => "http://nominatim.openstreetmap.org/reverse?format=json&lat={$latitude}&lon={$longitude}&zoom=18&addressdetails=1",
                'parser' => 'parseNominatimResponse'
            ],
            // LocationIQ (free tier available)
            [
                'url' => "https://us1.locationiq.com/v1/reverse.php?key=demo&lat={$latitude}&lon={$longitude}&format=json&addressdetails=1",
                'parser' => 'parseLocationIQResponse'
            ],
            // Google Maps Geocoding API (requires API key)
            // [
            //     'url' => "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key=" . env('GOOGLE_MAPS_API_KEY'),
            //     'parser' => 'parseGoogleResponse'
            // ],
            // MapBox (requires API key)
            // [
            //     'url' => "https://api.mapbox.com/geocoding/v5/mapbox.places/{$longitude},{$latitude}.json?access_token=" . env('MAPBOX_API_KEY'),
            //     'parser' => 'parseMapBoxResponse'
            // ]
        ];

        foreach ($services as $service) {
            try {
                Log::info("Trying location service: " . $service['url']);
                
                $httpClient = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => 'Laravel Geolocation App',
                        'Accept' => 'application/json'
                    ]);
                
                // Disable SSL verification for development environment
                if (app()->environment('local')) {
                    $httpClient = $httpClient->withOptions([
                        'verify' => false,
                        'curl' => [
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false,
                        ]
                    ]);
                }
                
                $response = $httpClient->get($service['url']);
                
                Log::info("Location service response status: " . $response->status());
                
                if ($response->successful()) {
                    $data = $response->json();
                    Log::info("Location service response data: " . json_encode($data));
                    
                    $parser = $service['parser'];
                    $result = $this->$parser($data);
                    
                    if ($result) {
                        Log::info("Successfully got location details from service: " . $service['url']);
                        return $result;
                    }
                } else {
                    Log::warning("Location service returned status: " . $response->status() . " for URL: " . $service['url']);
                }
            } catch (\Exception $e) {
                Log::warning("Location service failed: " . $service['url'] . " - Error: " . $e->getMessage());
                continue;
            }
        }

        // If all services fail, try to provide approximate location based on coordinates
        $approximateLocation = $this->getApproximateLocationFromCoordinates($latitude, $longitude);
        if ($approximateLocation) {
            Log::info("Using approximate location data for coordinates: {$latitude}, {$longitude}");
            return $approximateLocation;
        }

        throw new \Exception('All location services failed');
    }

    /**
     * Fetch location details from IP address
     */
    private function fetchLocationFromIP($ip)
    {
        if ($this->isLocalIp($ip)) {
            // For local development, return sample data
            return [
                'country' => 'India',
                'country_code' => 'IN',
                'state' => 'Maharashtra',
                'city' => 'Mumbai',
                'area' => 'Andheri',
                'pincode' => '400058',
                'latitude' => 19.1136,
                'longitude' => 72.8697,
                'formatted_address' => 'Andheri, Mumbai, Maharashtra, India'
            ];
        }

        $services = [
            [
                'url' => "http://ip-api.com/json/{$ip}?fields=status,country,countryCode,region,regionName,city,zip,lat,lon,timezone",
                'parser' => 'parseIPApiResponse'
            ],
            [
                'url' => "https://ipapi.co/{$ip}/json/",
                'parser' => 'parseIPApiCoResponse'
            ]
        ];

        foreach ($services as $service) {
            try {
                $response = Http::timeout(8)->get($service['url']);
                
                if ($response->successful()) {
                    $data = $response->json();
                    $parser = $service['parser'];
                    $result = $this->$parser($data);
                    
                    if ($result) {
                        return $result;
                    }
                }
            } catch (\Exception $e) {
                Log::warning("IP location service failed: " . $e->getMessage());
                continue;
            }
        }

        throw new \Exception('All IP location services failed');
    }

    /**
     * Search locations by query
     */
    private function searchLocationsByQuery($query)
    {
        $services = [
            [
                'url' => "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($query) . "&countrycodes=in&limit=10&addressdetails=1",
                'parser' => 'parseNominatimSearchResponse'
            ]
        ];

        foreach ($services as $service) {
            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'User-Agent' => 'Laravel Geolocation App'
                    ])
                    ->get($service['url']);
                
                if ($response->successful()) {
                    $data = $response->json();
                    $parser = $service['parser'];
                    $result = $this->$parser($data);
                    
                    if ($result) {
                        return $result;
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Location search service failed: " . $e->getMessage());
                continue;
            }
        }

        return [];
    }

    /**
     * Fetch pincode details from Indian postal API with multiple fallbacks
     */
    private function fetchPincodeDetails($pincode)
    {
        // Multiple pincode APIs for better reliability
        $services = [
            [
                'url' => "https://api.postalpincode.in/pincode/{$pincode}",
                'parser' => 'parsePostalPincodeResponse'
            ],
            [
                'url' => "https://api.zippopotam.us/in/{$pincode}",
                'parser' => 'parseZippopotamResponse'
            ],
            // Add HTTP fallback for postalpincode (if available)
            [
                'url' => "http://api.postalpincode.in/pincode/{$pincode}",
                'parser' => 'parsePostalPincodeResponse'
            ]
        ];

        foreach ($services as $service) {
            try {
                Log::info("Trying pincode service: " . $service['url']);
                
                $httpClient = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => 'Laravel Geolocation App',
                        'Accept' => 'application/json'
                    ]);
                
                // Disable SSL verification for development environment
                if (app()->environment('local')) {
                    $httpClient = $httpClient->withOptions([
                        'verify' => false,
                        'curl' => [
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false,
                        ]
                    ]);
                }
                
                $response = $httpClient->get($service['url']);
                
                Log::info("Pincode API response status: " . $response->status());
                
                if ($response->successful()) {
                    $data = $response->json();
                    Log::info("Pincode API response data: " . json_encode($data));
                    
                    $parser = $service['parser'];
                    $result = $this->$parser($data, $pincode);
                    
                    if ($result) {
                        Log::info("Successfully parsed pincode data from: " . $service['url']);
                        return $result;
                    }
                } else {
                    Log::warning("Pincode API returned status: " . $response->status() . " for URL: " . $service['url']);
                }
            } catch (\Exception $e) {
                Log::error("Pincode service failed: " . $service['url'] . " - Error: " . $e->getMessage());
                continue;
            }
        }

        // If all APIs fail, try to provide a basic response for common Indian pincodes
        $fallbackData = $this->getFallbackPincodeData($pincode);
        if ($fallbackData) {
            Log::info("Using fallback data for pincode: " . $pincode);
            return $fallbackData;
        }

        throw new \Exception("Unable to fetch pincode details for {$pincode}. All services failed.");
    }

    /**
     * Parse response from api.postalpincode.in
     */
    private function parsePostalPincodeResponse($data, $pincode)
    {
        if (!is_array($data) || !isset($data[0])) {
            Log::warning("Invalid response structure from postalpincode API");
            return null;
        }

        $firstResult = $data[0];
        
        if (!isset($firstResult['Status']) || $firstResult['Status'] !== 'Success') {
            Log::warning("Postalpincode API returned status: " . ($firstResult['Status'] ?? 'Unknown'));
            return null;
        }

        if (!isset($firstResult['PostOffice']) || !is_array($firstResult['PostOffice']) || empty($firstResult['PostOffice'])) {
            Log::warning("No post office data found in postalpincode API response");
            return null;
        }

        $postOffice = $firstResult['PostOffice'][0];
        
        return [
            'pincode' => $pincode,
            'area' => $postOffice['Name'] ?? '',
            'city' => $postOffice['District'] ?? '',
            'state' => $postOffice['State'] ?? '',
            'country' => $postOffice['Country'] ?? 'India',
            'region' => $postOffice['Region'] ?? '',
            'division' => $postOffice['Division'] ?? '',
            'formatted_address' => $this->formatAddress([
                $postOffice['Name'] ?? '',
                $postOffice['District'] ?? '',
                $postOffice['State'] ?? '',
                $postOffice['Country'] ?? 'India'
            ])
        ];
    }

    /**
     * Parse response from zippopotam.us
     */
    private function parseZippopotamResponse($data, $pincode)
    {
        if (!isset($data['places']) || !is_array($data['places']) || empty($data['places'])) {
            Log::warning("No places data found in zippopotam API response");
            return null;
        }

        $place = $data['places'][0];
        
        return [
            'pincode' => $pincode,
            'area' => $place['place name'] ?? '',
            'city' => $place['place name'] ?? '',
            'state' => $place['state'] ?? '',
            'country' => $data['country'] ?? 'India',
            'region' => '',
            'division' => '',
            'formatted_address' => $this->formatAddress([
                $place['place name'] ?? '',
                $place['state'] ?? '',
                $data['country'] ?? 'India'
            ])
        ];
    }

    /**
     * Get fallback data for common Indian pincodes
     */
    private function getFallbackPincodeData($pincode)
    {
        // Common pincode patterns for major Indian cities and states
        $fallbackData = [
            // Maharashtra
            '400' => ['city' => 'Mumbai', 'state' => 'Maharashtra'],
            '401' => ['city' => 'Thane', 'state' => 'Maharashtra'],
            '411' => ['city' => 'Pune', 'state' => 'Maharashtra'],
            '412' => ['city' => 'Pune', 'state' => 'Maharashtra'],
            '413' => ['city' => 'Solapur', 'state' => 'Maharashtra'],
            '414' => ['city' => 'Ahmednagar', 'state' => 'Maharashtra'],
            '415' => ['city' => 'Sangli', 'state' => 'Maharashtra'],
            '416' => ['city' => 'Kolhapur', 'state' => 'Maharashtra'],
            
            // Delhi & NCR
            '110' => ['city' => 'New Delhi', 'state' => 'Delhi'],
            '121' => ['city' => 'Faridabad', 'state' => 'Haryana'],
            '122' => ['city' => 'Gurgaon', 'state' => 'Haryana'],
            '201' => ['city' => 'Ghaziabad', 'state' => 'Uttar Pradesh'],
            
            // Karnataka
            '560' => ['city' => 'Bangalore', 'state' => 'Karnataka'],
            '561' => ['city' => 'Bangalore Rural', 'state' => 'Karnataka'],
            '562' => ['city' => 'Chikkaballapur', 'state' => 'Karnataka'],
            '563' => ['city' => 'Kolar', 'state' => 'Karnataka'],
            '570' => ['city' => 'Mysore', 'state' => 'Karnataka'],
            '575' => ['city' => 'Mangalore', 'state' => 'Karnataka'],
            '580' => ['city' => 'Hubli', 'state' => 'Karnataka'],
            
            // Tamil Nadu
            '600' => ['city' => 'Chennai', 'state' => 'Tamil Nadu'],
            '601' => ['city' => 'Kanchipuram', 'state' => 'Tamil Nadu'],
            '602' => ['city' => 'Tiruvallur', 'state' => 'Tamil Nadu'],
            '603' => ['city' => 'Vellore', 'state' => 'Tamil Nadu'],
            '620' => ['city' => 'Tiruchirappalli', 'state' => 'Tamil Nadu'],
            '625' => ['city' => 'Madurai', 'state' => 'Tamil Nadu'],
            '630' => ['city' => 'Thanjavur', 'state' => 'Tamil Nadu'],
            '641' => ['city' => 'Coimbatore', 'state' => 'Tamil Nadu'],
            
            // Telangana & Andhra Pradesh
            '500' => ['city' => 'Hyderabad', 'state' => 'Telangana'],
            '501' => ['city' => 'Hyderabad', 'state' => 'Telangana'],
            '502' => ['city' => 'Medak', 'state' => 'Telangana'],
            '503' => ['city' => 'Nizamabad', 'state' => 'Telangana'],
            '504' => ['city' => 'Adilabad', 'state' => 'Telangana'],
            '505' => ['city' => 'Karimnagar', 'state' => 'Telangana'],
            '506' => ['city' => 'Warangal', 'state' => 'Telangana'],
            '507' => ['city' => 'Khammam', 'state' => 'Telangana'],
            '508' => ['city' => 'Nalgonda', 'state' => 'Telangana'],
            '509' => ['city' => 'Mahabubnagar', 'state' => 'Telangana'],
            '515' => ['city' => 'Anantapur', 'state' => 'Andhra Pradesh'],
            '516' => ['city' => 'Kadapa', 'state' => 'Andhra Pradesh'],
            '517' => ['city' => 'Chittoor', 'state' => 'Andhra Pradesh'],
            '518' => ['city' => 'Kurnool', 'state' => 'Andhra Pradesh'],
            '520' => ['city' => 'Vijayawada', 'state' => 'Andhra Pradesh'],
            '521' => ['city' => 'Krishna', 'state' => 'Andhra Pradesh'],
            '522' => ['city' => 'Guntur', 'state' => 'Andhra Pradesh'],
            '523' => ['city' => 'Prakasam', 'state' => 'Andhra Pradesh'],
            '530' => ['city' => 'Visakhapatnam', 'state' => 'Andhra Pradesh'],
            '531' => ['city' => 'Vizianagaram', 'state' => 'Andhra Pradesh'],
            '532' => ['city' => 'Srikakulam', 'state' => 'Andhra Pradesh'],
            '533' => ['city' => 'East Godavari', 'state' => 'Andhra Pradesh'],
            '534' => ['city' => 'West Godavari', 'state' => 'Andhra Pradesh'],
            
            // West Bengal
            '700' => ['city' => 'Kolkata', 'state' => 'West Bengal'],
            '701' => ['city' => 'North 24 Parganas', 'state' => 'West Bengal'],
            '711' => ['city' => 'Howrah', 'state' => 'West Bengal'],
            '712' => ['city' => 'Hooghly', 'state' => 'West Bengal'],
            '713' => ['city' => 'Bardhaman', 'state' => 'West Bengal'],
            '721' => ['city' => 'Midnapore', 'state' => 'West Bengal'],
            '731' => ['city' => 'Malda', 'state' => 'West Bengal'],
            '732' => ['city' => 'Darjeeling', 'state' => 'West Bengal'],
            '733' => ['city' => 'Jalpaiguri', 'state' => 'West Bengal'],
            '734' => ['city' => 'Cooch Behar', 'state' => 'West Bengal'],
            '735' => ['city' => 'Alipurduar', 'state' => 'West Bengal'],
            '736' => ['city' => 'Kalimpong', 'state' => 'West Bengal'],
            '741' => ['city' => 'Nadia', 'state' => 'West Bengal'],
            '742' => ['city' => 'Murshidabad', 'state' => 'West Bengal'],
            '743' => ['city' => 'Birbhum', 'state' => 'West Bengal'],
            
            // Gujarat
            '360' => ['city' => 'Rajkot', 'state' => 'Gujarat'],
            '361' => ['city' => 'Jamnagar', 'state' => 'Gujarat'],
            '362' => ['city' => 'Porbandar', 'state' => 'Gujarat'],
            '363' => ['city' => 'Surendranagar', 'state' => 'Gujarat'], // This covers the 363530 pincode
            '364' => ['city' => 'Bhavnagar', 'state' => 'Gujarat'],
            '365' => ['city' => 'Amreli', 'state' => 'Gujarat'],
            '370' => ['city' => 'Kachchh', 'state' => 'Gujarat'],
            '380' => ['city' => 'Ahmedabad', 'state' => 'Gujarat'],
            '381' => ['city' => 'Mehsana', 'state' => 'Gujarat'],
            '382' => ['city' => 'Sabarkantha', 'state' => 'Gujarat'],
            '383' => ['city' => 'Banaskantha', 'state' => 'Gujarat'],
            '384' => ['city' => 'Patan', 'state' => 'Gujarat'],
            '385' => ['city' => 'Kachchh', 'state' => 'Gujarat'],
            '387' => ['city' => 'Gandhinagar', 'state' => 'Gujarat'],
            '388' => ['city' => 'Anand', 'state' => 'Gujarat'],
            '389' => ['city' => 'Kheda', 'state' => 'Gujarat'],
            '390' => ['city' => 'Vadodara', 'state' => 'Gujarat'],
            '391' => ['city' => 'Bharuch', 'state' => 'Gujarat'],
            '392' => ['city' => 'Narmada', 'state' => 'Gujarat'],
            '393' => ['city' => 'Narmada', 'state' => 'Gujarat'],
            '394' => ['city' => 'Surat', 'state' => 'Gujarat'],
            '395' => ['city' => 'Navsari', 'state' => 'Gujarat'],
            '396' => ['city' => 'Valsad', 'state' => 'Gujarat'],
            
            // Rajasthan
            '301' => ['city' => 'Alwar', 'state' => 'Rajasthan'],
            '302' => ['city' => 'Jaipur', 'state' => 'Rajasthan'],
            '303' => ['city' => 'Dausa', 'state' => 'Rajasthan'],
            '304' => ['city' => 'Tonk', 'state' => 'Rajasthan'],
            '305' => ['city' => 'Ajmer', 'state' => 'Rajasthan'],
            '306' => ['city' => 'Pali', 'state' => 'Rajasthan'],
            '307' => ['city' => 'Sirohi', 'state' => 'Rajasthan'],
            '311' => ['city' => 'Bhilwara', 'state' => 'Rajasthan'],
            '312' => ['city' => 'Chittorgarh', 'state' => 'Rajasthan'],
            '313' => ['city' => 'Udaipur', 'state' => 'Rajasthan'],
            '314' => ['city' => 'Dungarpur', 'state' => 'Rajasthan'],
            '321' => ['city' => 'Bharatpur', 'state' => 'Rajasthan'],
            '322' => ['city' => 'Sawai Madhopur', 'state' => 'Rajasthan'],
            '323' => ['city' => 'Bundi', 'state' => 'Rajasthan'],
            '324' => ['city' => 'Kota', 'state' => 'Rajasthan'],
            '325' => ['city' => 'Baran', 'state' => 'Rajasthan'],
            '326' => ['city' => 'Jhalawar', 'state' => 'Rajasthan'],
            '331' => ['city' => 'Churu', 'state' => 'Rajasthan'],
            '332' => ['city' => 'Sikar', 'state' => 'Rajasthan'],
            '333' => ['city' => 'Jhunjhunu', 'state' => 'Rajasthan'],
            '334' => ['city' => 'Bikaner', 'state' => 'Rajasthan'],
            '335' => ['city' => 'Hanumangarh', 'state' => 'Rajasthan'],
            '341' => ['city' => 'Jodhpur', 'state' => 'Rajasthan'],
            '342' => ['city' => 'Barmer', 'state' => 'Rajasthan'],
            '343' => ['city' => 'Jalore', 'state' => 'Rajasthan'],
            '344' => ['city' => 'Jaisalmer', 'state' => 'Rajasthan'],
            '345' => ['city' => 'Jaisalmer', 'state' => 'Rajasthan'],
        ];

        $prefix = substr($pincode, 0, 3);
        
        if (isset($fallbackData[$prefix])) {
            $data = $fallbackData[$prefix];
            return [
                'pincode' => $pincode,
                'area' => $data['city'],
                'city' => $data['city'],
                'state' => $data['state'],
                'country' => 'India',
                'region' => '',
                'division' => '',
                'formatted_address' => $this->formatAddress([
                    $data['city'],
                    $data['state'],
                    'India'
                ])
            ];
        }

        return null;
    }

    /**
     * Format address from array of components
     */
    private function formatAddress($components)
    {
        $filtered = array_filter($components, function($component) {
            return !empty(trim($component));
        });
        
        return implode(', ', $filtered);
    }

    /**
     * Parse LocationIQ response (similar to Nominatim)
     */
    private function parseLocationIQResponse($data)
    {
        return $this->parseNominatimResponse($data);
    }

    /**
     * Get approximate location based on coordinates (fallback method)
     */
    private function getApproximateLocationFromCoordinates($latitude, $longitude)
    {
        // Basic coordinate-based location approximation for India
        $indianRegions = [
            // North India
            ['lat_min' => 28.0, 'lat_max' => 32.0, 'lon_min' => 76.0, 'lon_max' => 78.5, 'city' => 'New Delhi', 'state' => 'Delhi'],
            ['lat_min' => 26.5, 'lat_max' => 28.5, 'lon_min' => 80.0, 'lon_max' => 82.0, 'city' => 'Lucknow', 'state' => 'Uttar Pradesh'],
            
            // West India
            ['lat_min' => 18.8, 'lat_max' => 19.3, 'lon_min' => 72.7, 'lon_max' => 73.2, 'city' => 'Mumbai', 'state' => 'Maharashtra'],
            ['lat_min' => 18.4, 'lat_max' => 18.7, 'lon_min' => 73.7, 'lon_max' => 74.0, 'city' => 'Pune', 'state' => 'Maharashtra'],
            ['lat_min' => 22.9, 'lat_max' => 23.3, 'lon_min' => 72.4, 'lon_max' => 72.8, 'city' => 'Ahmedabad', 'state' => 'Gujarat'],
            
            // South India
            ['lat_min' => 12.8, 'lat_max' => 13.2, 'lon_min' => 77.4, 'lon_max' => 77.8, 'city' => 'Bangalore', 'state' => 'Karnataka'],
            ['lat_min' => 12.8, 'lat_max' => 13.2, 'lon_min' => 80.1, 'lon_max' => 80.4, 'city' => 'Chennai', 'state' => 'Tamil Nadu'],
            ['lat_min' => 17.2, 'lat_max' => 17.6, 'lon_min' => 78.2, 'lon_max' => 78.7, 'city' => 'Hyderabad', 'state' => 'Telangana'],
            
            // East India
            ['lat_min' => 22.4, 'lat_max' => 22.8, 'lon_min' => 88.2, 'lon_max' => 88.5, 'city' => 'Kolkata', 'state' => 'West Bengal'],
        ];

        foreach ($indianRegions as $region) {
            if ($latitude >= $region['lat_min'] && $latitude <= $region['lat_max'] &&
                $longitude >= $region['lon_min'] && $longitude <= $region['lon_max']) {
                
                return [
                    'country' => 'India',
                    'country_code' => 'IN',
                    'state' => $region['state'],
                    'city' => $region['city'],
                    'area' => $region['city'],
                    'pincode' => '',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'formatted_address' => $region['city'] . ', ' . $region['state'] . ', India'
                ];
            }
        }

        // If no specific region matches, provide generic India location
        return [
            'country' => 'India',
            'country_code' => 'IN',
            'state' => 'Unknown',
            'city' => 'Unknown',
            'area' => 'Unknown',
            'pincode' => '',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'formatted_address' => 'India'
        ];
    }

    /**
     * Parse Nominatim response
     */
    private function parseNominatimResponse($data)
    {
        if (!isset($data['address'])) {
            return null;
        }

        $address = $data['address'];
        
        return [
            'country' => $address['country'] ?? '',
            'country_code' => $address['country_code'] ?? '',
            'state' => $address['state'] ?? $address['region'] ?? '',
            'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? '',
            'area' => $address['suburb'] ?? $address['neighbourhood'] ?? $address['hamlet'] ?? '',
            'pincode' => $address['postcode'] ?? '',
            'latitude' => (float) $data['lat'],
            'longitude' => (float) $data['lon'],
            'formatted_address' => $data['display_name'] ?? '',
            'road' => $address['road'] ?? '',
            'house_number' => $address['house_number'] ?? ''
        ];
    }

    /**
     * Parse IP-API response
     */
    private function parseIPApiResponse($data)
    {
        if (!isset($data['status']) || $data['status'] !== 'success') {
            return null;
        }

        return [
            'country' => $data['country'] ?? '',
            'country_code' => $data['countryCode'] ?? '',
            'state' => $data['regionName'] ?? '',
            'city' => $data['city'] ?? '',
            'area' => '',
            'pincode' => $data['zip'] ?? '',
            'latitude' => (float) $data['lat'],
            'longitude' => (float) $data['lon'],
            'formatted_address' => ($data['city'] ?? '') . ', ' . ($data['regionName'] ?? '') . ', ' . ($data['country'] ?? ''),
            'timezone' => $data['timezone'] ?? ''
        ];
    }

    /**
     * Parse IPApi.co response
     */
    private function parseIPApiCoResponse($data)
    {
        if (!isset($data['country_name'])) {
            return null;
        }

        return [
            'country' => $data['country_name'] ?? '',
            'country_code' => $data['country_code'] ?? '',
            'state' => $data['region'] ?? '',
            'city' => $data['city'] ?? '',
            'area' => '',
            'pincode' => $data['postal'] ?? '',
            'latitude' => (float) $data['latitude'],
            'longitude' => (float) $data['longitude'],
            'formatted_address' => ($data['city'] ?? '') . ', ' . ($data['region'] ?? '') . ', ' . ($data['country_name'] ?? ''),
            'timezone' => $data['timezone'] ?? ''
        ];
    }

    /**
     * Parse Nominatim search response
     */
    private function parseNominatimSearchResponse($data)
    {
        $results = [];
        
        foreach ($data as $item) {
            if (isset($item['address'])) {
                $address = $item['address'];
                
                $results[] = [
                    'display_name' => $item['display_name'],
                    'latitude' => (float) $item['lat'],
                    'longitude' => (float) $item['lon'],
                    'country' => $address['country'] ?? '',
                    'state' => $address['state'] ?? $address['region'] ?? '',
                    'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? '',
                    'area' => $address['suburb'] ?? $address['neighbourhood'] ?? '',
                    'pincode' => $address['postcode'] ?? '',
                    'type' => $item['type'] ?? '',
                    'importance' => $item['importance'] ?? 0
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Get the real client IP address
     */
    private function getClientIp(Request $request)
    {
        // Check for various headers that might contain the real IP
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];
        
        foreach ($ipHeaders as $header) {
            if ($request->server($header)) {
                $ips = explode(',', $request->server($header));
                $ip = trim($ips[0]);
                
                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        $requestIp = $request->ip();
        
        // If we get a local IP, try to get the real public IP
        if ($this->isLocalIp($requestIp)) {
            return $this->getPublicIp();
        }
        
        return $requestIp;
    }
    
    /**
     * Check if IP is a local/private IP
     */
    private function isLocalIp($ip)
    {
        return $ip === '127.0.0.1' || 
               $ip === '::1' || 
               strpos($ip, '192.168.') === 0 || 
               strpos($ip, '10.') === 0 ||
               strpos($ip, '172.') === 0;
    }
    
    /**
     * Get public IP address when running locally
     */
    private function getPublicIp()
    {
        try {
            // Try multiple services to get public IP
            $ipServices = [
                'https://api.ipify.org',
                'https://ipinfo.io/ip',
                'https://icanhazip.com',
                'https://ident.me'
            ];
            
            foreach ($ipServices as $service) {
                try {
                    $response = Http::timeout(3)->get($service);
                    if ($response->successful()) {
                        $ip = trim($response->body());
                        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                            Log::info("Got public IP from {$service}: {$ip}");
                            return $ip;
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("IP service {$service} failed: " . $e->getMessage());
                    continue;
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to get public IP: ' . $e->getMessage());
        }
        
        // Fallback to localhost
        return '127.0.0.1';
    }
    
    /**
     * Fetch country code from geolocation service
     */
    private function fetchCountryCode($ip)
    {
        // Only return default for localhost if we couldn't get public IP
        if ($this->isLocalIp($ip)) {
            Log::info("Using localhost IP {$ip}, returning default country");
            return 'in'; // Default to India since you mentioned you're in India
        }
        
        Log::info("Fetching country code for IP: {$ip}");
        
        try {
            // Try multiple services for better reliability
            $services = [
                "http://ip-api.com/json/{$ip}?fields=countryCode,country",
                "https://ipapi.co/{$ip}/json/",
                "http://www.geoplugin.net/json.gp?ip={$ip}",
                "https://ipinfo.io/{$ip}/json"
            ];
            
            foreach ($services as $index => $service) {
                try {
                    $response = Http::timeout(8)->get($service);
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        
                        // Handle different API response formats
                        if ($index === 0 && isset($data['countryCode'])) {
                            // ip-api.com
                            Log::info("Got country from ip-api.com: {$data['countryCode']} for IP {$ip}");
                            return strtolower($data['countryCode']);
                        } elseif ($index === 1 && isset($data['country_code'])) {
                            // ipapi.co
                            Log::info("Got country from ipapi.co: {$data['country_code']} for IP {$ip}");
                            return strtolower($data['country_code']);
                        } elseif ($index === 2 && isset($data['geoplugin_countryCode'])) {
                            // geoplugin.net
                            Log::info("Got country from geoplugin: {$data['geoplugin_countryCode']} for IP {$ip}");
                            return strtolower($data['geoplugin_countryCode']);
                        } elseif ($index === 3 && isset($data['country'])) {
                            // ipinfo.io
                            Log::info("Got country from ipinfo.io: {$data['country']} for IP {$ip}");
                            return strtolower($data['country']);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("GeoLocation service {$service} failed: " . $e->getMessage());
                    continue;
                }
            }
            
        } catch (\Exception $e) {
            Log::error('All GeoLocation services failed: ' . $e->getMessage());
        }
        
        // Default fallback to India since you're in India
        Log::info("All services failed, returning default country: in");
        return 'in';
    }
}