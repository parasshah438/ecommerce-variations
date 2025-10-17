<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tax Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how taxes are calculated in your e-commerce application.
    |
    */

    'tax' => [
        'rate' => env('TAX_RATE', 0.18), // 18% GST by default
        
        // When to apply tax in relation to discounts
        // 'before_discount' - Calculate tax on original subtotal, then apply discount
        // 'after_discount' - Apply discount first, then calculate tax on reduced amount
        'calculate_on' => env('TAX_CALCULATE_ON', 'after_discount'),
        
        'enabled' => env('TAX_ENABLED', true),
        'name' => env('TAX_NAME', 'GST'),
        'inclusive' => env('TAX_INCLUSIVE', false), // false = tax exclusive (added to price)
    ],

    /*
    |--------------------------------------------------------------------------
    | Shipping Configuration
    |--------------------------------------------------------------------------
    */

    'shipping' => [
        'free_shipping_threshold' => env('FREE_SHIPPING_THRESHOLD', 500),
        'default_cost' => env('DEFAULT_SHIPPING_COST', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Coupon Configuration
    |--------------------------------------------------------------------------
    */

    'coupons' => [
        'enabled' => env('COUPONS_ENABLED', true),
        'max_discount_percentage' => env('MAX_DISCOUNT_PERCENTAGE', 90), // Maximum 90% discount
        'minimum_order_value' => env('MINIMUM_ORDER_VALUE', 0),
    ],

];