<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shiprocket API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Shiprocket shipping service integration
    |
    */

    'base_url' => env('SHIPROCKET_BASE_URL', 'https://apiv2.shiprocket.in/v1/external'),
    
    'email' => env('SHIPROCKET_EMAIL'),
    
    'password' => env('SHIPROCKET_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Token Settings
    |--------------------------------------------------------------------------
    */
    
    'token_cache_duration' => env('SHIPROCKET_TOKEN_CACHE_DURATION', 3600), // 1 hour

    /*
    |--------------------------------------------------------------------------
    | Return Settings
    |--------------------------------------------------------------------------
    */
    
    'return_window_days' => env('SHIPROCKET_RETURN_WINDOW_DAYS', 7),
    
    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */
    
    'default_weight' => env('SHIPROCKET_DEFAULT_WEIGHT', 0.5),
    
    'default_dimensions' => [
        'length' => env('SHIPROCKET_DEFAULT_LENGTH', 10),
        'breadth' => env('SHIPROCKET_DEFAULT_BREADTH', 10),
        'height' => env('SHIPROCKET_DEFAULT_HEIGHT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeout Settings
    |--------------------------------------------------------------------------
    */
    
    'timeout' => env('SHIPROCKET_TIMEOUT', 30),
    
    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    
    'log_requests' => env('SHIPROCKET_LOG_REQUESTS', true),
    
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    
    'rate_limit_per_minute' => env('SHIPROCKET_RATE_LIMIT', 60),

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    */
    
    'webhook_secret' => env('SHIPROCKET_WEBHOOK_SECRET'),
    
    'webhook_url' => env('SHIPROCKET_WEBHOOK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Courier Preferences
    |--------------------------------------------------------------------------
    */
    
    'preferred_couriers' => [
        'domestic' => ['Blue Dart', 'FedEx', 'Delhivery'],
        'international' => ['FedEx', 'Blue Dart'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Rules
    |--------------------------------------------------------------------------
    */
    
    'auto_create_shipments' => env('SHIPROCKET_AUTO_CREATE_SHIPMENTS', true),
    
    'auto_assign_courier' => env('SHIPROCKET_AUTO_ASSIGN_COURIER', false),
    
    'prefer_cheapest' => env('SHIPROCKET_PREFER_CHEAPEST', true),
    
    'max_courier_rate' => env('SHIPROCKET_MAX_COURIER_RATE', null),
    
    'max_delivery_days' => env('SHIPROCKET_MAX_DELIVERY_DAYS', null),

    /*
    |--------------------------------------------------------------------------
    | Pickup Settings
    |--------------------------------------------------------------------------
    */
    
    'default_pickup_location' => env('SHIPROCKET_DEFAULT_PICKUP_LOCATION', 'Primary'),
    
    'pickup_postcode' => env('SHIPROCKET_PICKUP_POSTCODE'),
    
    /*
    |--------------------------------------------------------------------------
    | Channel Settings
    |--------------------------------------------------------------------------
    */
    
    'channel_id' => env('SHIPROCKET_CHANNEL_ID', ''),

];