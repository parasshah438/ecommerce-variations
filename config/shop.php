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

    /*
    |--------------------------------------------------------------------------
    | Free Shipping Configuration
    |--------------------------------------------------------------------------
    */
    'free_shipping_threshold' => env('SHOP_FREE_SHIPPING_THRESHOLD', 999),
    'free_shipping_enabled' => env('SHOP_FREE_SHIPPING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Default Product Weights (in grams)
    |--------------------------------------------------------------------------
    */
    'default_weights' => [
        'tshirt' => 200,
        'shirt' => 300,
        'jeans' => 600,
        'dress' => 400,
        'jacket' => 800,
        'shoes' => 500,
        'cap' => 100,
        'socks' => 50,
        'default' => 200,
    ],

    /*
    |--------------------------------------------------------------------------
    | Weight Categories for Admin
    |--------------------------------------------------------------------------
    */
    'weight_categories' => [
        'very_light' => ['max' => 150, 'label' => 'Very Light', 'examples' => 'Socks, Underwear, Scarves'],
        'light' => ['max' => 300, 'label' => 'Light', 'examples' => 'T-shirts, Tops, Thin Shirts'],
        'medium' => ['max' => 600, 'label' => 'Medium', 'examples' => 'Shirts, Dresses, Light Pants'],
        'heavy' => ['max' => 1000, 'label' => 'Heavy', 'examples' => 'Jeans, Jackets, Heavy Dresses'],
        'very_heavy' => ['max' => null, 'label' => 'Very Heavy', 'examples' => 'Coats, Boots, Heavy Jackets'],
    ],

    /*
    |--------------------------------------------------------------------------
    | COD Configuration
    |--------------------------------------------------------------------------
    */
    'cod' => [
        'enabled' => env('SHOP_COD_ENABLED', true),
        'max_amount' => env('SHOP_COD_MAX_AMOUNT', 5000),
        'charges' => env('SHOP_COD_CHARGES', 25),
        'free_above' => env('SHOP_COD_FREE_ABOVE', 1999),
    ],

];