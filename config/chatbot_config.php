<?php

/**
 * Ecommerce Chatbot Configuration
 * Customize all chatbot settings here
 */

return [
    'groq' => [
        'api_key' => "",
        'model' => 'llama-3.3-70b-versatile',
        'temperature' => 0.7,
        'max_tokens' => 1024,
        'timeout' => 30,
    ],

    // ============================================
    // WEBSITE INFORMATION
    // ============================================
    'site' => [
        'name' => 'Your Ecommerce Store',
        'url' => 'https://yourdomain.com',
        'email' => 'support@yourdomain.com',
        'phone' => '+91-XXXXXXXXXX',
        'description' => 'Your store description here',
    ],

    // ============================================
    // CHATBOT BEHAVIOR
    // ============================================
    'chatbot' => [
        'name' => 'Shopping Assistant',
        'greeting' => 'Hello! I\'m your shopping assistant. How can I help you today?',
        'fallback_message' => 'I\'m here to help with our website and products. How can I assist you with shopping?',
        'error_message' => 'Sorry, I encountered an error. Please try again.',
        'typing_delay' => 500, // milliseconds
    ],

    // ============================================
    // SHIPPING & DELIVERY
    // ============================================
    'shipping' => [
        'free_shipping_threshold' => 500, // ₹500
        'standard_delivery_days' => '5-7',
        'express_delivery_days' => '2-3',
        'return_policy_days' => 30,
    ],

    // ============================================
    // PAYMENT METHODS
    // ============================================
    'payment_methods' => [
        'credit_card' => true,
        'debit_card' => true,
        'digital_wallet' => true,
        'bank_transfer' => true,
        'cash_on_delivery' => true,
    ],

    // ============================================
    // DATABASE QUERIES
    // ============================================
    'database' => [
        'products_limit' => 50,
        'categories_limit' => 20,
        'cache_duration' => 3600, // 1 hour in seconds
    ],

    // ============================================
    // SUPPORT INFORMATION
    // ============================================
    'support' => [
        'email' => 'support@yourdomain.com',
        'phone' => '+91-XXXXXXXXXX',
        'live_chat_hours' => '9 AM - 9 PM IST',
        'response_time' => '24 hours',
        'faq_url' => 'https://yourdomain.com/faq',
        'contact_url' => 'https://yourdomain.com/contact',
    ],

    // ============================================
    // FEATURES & SERVICES
    // ============================================
    'features' => [
        'search' => true,
        'filters' => true,
        'wishlist' => true,
        'save_for_later' => true,
        'reviews' => true,
        'recommendations' => true,
        'order_tracking' => true,
        'returns' => true,
    ],

    // ============================================
    // SUGGESTED QUESTIONS
    // ============================================
    'suggested_questions' => [
        'What products do you have?',
        'How does the checkout process work?',
        'What is your return policy?',
        'How long does delivery take?',
        'Do you offer free shipping?',
        'How do I track my order?',
        'What payment methods do you accept?',
        'Can I cancel my order?',
    ],

    // ============================================
    // COMMON QUESTIONS & ANSWERS
    // ============================================
    'faq' => [
        [
            'question' => 'How do I track my order?',
            'answer' => 'Log into your account, go to "My Orders", and click on the order to see real-time tracking.'
        ],
        [
            'question' => 'What is your return policy?',
            'answer' => 'We offer 30-day returns for unused items in original packaging. Contact support for return authorization.'
        ],
        [
            'question' => 'How long does delivery take?',
            'answer' => 'Standard delivery: 5-7 business days. Express delivery: 2-3 business days.'
        ],
        [
            'question' => 'Is my payment secure?',
            'answer' => 'Yes, we use SSL encryption and PCI-DSS compliance for all transactions.'
        ],
        [
            'question' => 'Can I cancel my order?',
            'answer' => 'Orders can be cancelled within 24 hours of placement. Contact support immediately.'
        ],
        [
            'question' => 'Do you offer international shipping?',
            'answer' => 'Currently, we ship within India only.'
        ],
        [
            'question' => 'How do I use a coupon code?',
            'answer' => 'Enter the code at checkout in the "Promo Code" field before payment.'
        ],
        [
            'question' => 'What if my item arrives damaged?',
            'answer' => 'Contact support with photos within 48 hours for replacement or refund.'
        ],
    ],

    // ============================================
    // SECURITY
    // ============================================
    'security' => [
        'rate_limit_enabled' => true,
        'rate_limit_per_minute' => 10,
        'validate_input' => true,
        'max_message_length' => 1000,
        'log_conversations' => false, // Set to true if you want to log all conversations
    ],

    // ============================================
    // UI/UX SETTINGS
    // ============================================
    'ui' => [
        'theme' => 'light', // 'light' or 'dark'
        'position' => 'bottom-right', // 'bottom-right', 'bottom-left', 'top-right', 'top-left'
        'width' => '600px',
        'height' => '700px',
        'show_avatar' => true,
        'show_timestamp' => true,
        'show_suggestions' => true,
    ],

];
