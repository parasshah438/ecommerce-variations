<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Service Provider
    |--------------------------------------------------------------------------
    |
    | This option controls which WhatsApp service provider to use.
    | Currently supported: "ultramsg"
    |
    */
    'provider' => env('WHATSAPP_PROVIDER', 'ultramsg'),

    /*
    |--------------------------------------------------------------------------
    | Ultramsg Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Ultramsg WhatsApp API service
    |
    */
    'ultramsg' => [
        'base_url' => env('ULTRAMSG_BASE_URL', 'https://api.ultramsg.com'),
        'instance_id' => env('ULTRAMSG_INSTANCE_ID'),
        'token' => env('ULTRAMSG_TOKEN'),
        'timeout' => env('ULTRAMSG_TIMEOUT', 30),
        'webhook_secret' => env('ULTRAMSG_WEBHOOK_SECRET'),
        'verify_ssl' => env('ULTRAMSG_VERIFY_SSL', false), // Set to false for local development
        'use_http' => env('ULTRAMSG_USE_HTTP', false), // Option to use HTTP instead of HTTPS
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Settings
    |--------------------------------------------------------------------------
    |
    | General message settings and limits
    |
    */
    'message_settings' => [
        'max_text_length' => env('WHATSAPP_MAX_TEXT_LENGTH', 1000),
        'max_caption_length' => env('WHATSAPP_MAX_CAPTION_LENGTH', 500),
        'max_bulk_recipients' => env('WHATSAPP_MAX_BULK_RECIPIENTS', 100),
        'rate_limit_per_minute' => env('WHATSAPP_RATE_LIMIT_PER_MINUTE', 20),
        'retry_attempts' => env('WHATSAPP_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('WHATSAPP_RETRY_DELAY', 5), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    |
    | Settings for media file uploads
    |
    */
    'file_settings' => [
        'max_file_size' => env('WHATSAPP_MAX_FILE_SIZE', 51200), // KB (50MB)
        'max_image_size' => env('WHATSAPP_MAX_IMAGE_SIZE', 10240), // KB (10MB)
        'max_audio_size' => env('WHATSAPP_MAX_AUDIO_SIZE', 10240), // KB (10MB)
        'max_video_size' => env('WHATSAPP_MAX_VIDEO_SIZE', 51200), // KB (50MB)
        'allowed_image_types' => ['jpeg', 'jpg', 'png', 'gif', 'webp'],
        'allowed_document_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'],
        'allowed_audio_types' => ['mp3', 'wav', 'ogg', 'aac', 'm4a'],
        'allowed_video_types' => ['mp4', 'avi', 'mov', 'wmv', 'flv'],
        'storage_path' => 'whatsapp',
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    |
    | Settings for webhook handling
    |
    */
    'webhook_settings' => [
        'verify_signature' => env('WHATSAPP_VERIFY_WEBHOOK_SIGNATURE', true),
        'auto_respond' => env('WHATSAPP_AUTO_RESPOND', false),
        'store_incoming_messages' => env('WHATSAPP_STORE_INCOMING_MESSAGES', true),
        'log_webhooks' => env('WHATSAPP_LOG_WEBHOOKS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Settings
    |--------------------------------------------------------------------------
    |
    | Settings for message templates
    |
    */
    'template_settings' => [
        'max_templates_per_user' => env('WHATSAPP_MAX_TEMPLATES_PER_USER', 50),
        'template_variables' => [
            'user_name' => 'User Name',
            'user_email' => 'User Email',
            'user_phone' => 'User Phone',
            'current_date' => 'Current Date',
            'current_time' => 'Current Time',
            'site_name' => 'Site Name',
            'site_url' => 'Site URL',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    |
    | Settings for message queuing and background processing
    |
    */
    'queue_settings' => [
        'use_queue' => env('WHATSAPP_USE_QUEUE', true),
        'queue_name' => env('WHATSAPP_QUEUE_NAME', 'whatsapp'),
        'queue_connection' => env('WHATSAPP_QUEUE_CONNECTION', 'database'),
        'bulk_chunk_size' => env('WHATSAPP_BULK_CHUNK_SIZE', 10),
        'bulk_delay_between_chunks' => env('WHATSAPP_BULK_DELAY_BETWEEN_CHUNKS', 30), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Settings
    |--------------------------------------------------------------------------
    |
    | Settings for analytics and reporting
    |
    */
    'analytics_settings' => [
        'track_delivery' => env('WHATSAPP_TRACK_DELIVERY', true),
        'track_read_receipts' => env('WHATSAPP_TRACK_READ_RECEIPTS', true),
        'retention_days' => env('WHATSAPP_RETENTION_DAYS', 90),
        'generate_reports' => env('WHATSAPP_GENERATE_REPORTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security and permission settings
    |
    */
    'security_settings' => [
        'require_authentication' => env('WHATSAPP_REQUIRE_AUTHENTICATION', true),
        'admin_only_broadcast' => env('WHATSAPP_ADMIN_ONLY_BROADCAST', true),
        'phone_number_validation' => env('WHATSAPP_PHONE_NUMBER_VALIDATION', true),
        'content_filtering' => env('WHATSAPP_CONTENT_FILTERING', false),
        'spam_protection' => env('WHATSAPP_SPAM_PROTECTION', true),
        'daily_limit_per_user' => env('WHATSAPP_DAILY_LIMIT_PER_USER', 200),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Country Settings
    |--------------------------------------------------------------------------
    |
    | Default country code and formatting settings
    |
    */
    'country_settings' => [
        'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '91'), // India
        'phone_number_format' => env('WHATSAPP_PHONE_NUMBER_FORMAT', 'international'), // international, national, e164
        'validate_country_code' => env('WHATSAPP_VALIDATE_COUNTRY_CODE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable/disable specific features
    |
    */
    'features' => [
        'bulk_messaging' => env('WHATSAPP_FEATURE_BULK_MESSAGING', true),
        'templates' => env('WHATSAPP_FEATURE_TEMPLATES', true),
        'media_messages' => env('WHATSAPP_FEATURE_MEDIA_MESSAGES', true),
        'location_messages' => env('WHATSAPP_FEATURE_LOCATION_MESSAGES', true),
        'contact_messages' => env('WHATSAPP_FEATURE_CONTACT_MESSAGES', true),
        'group_messaging' => env('WHATSAPP_FEATURE_GROUP_MESSAGING', true),
        'auto_responses' => env('WHATSAPP_FEATURE_AUTO_RESPONSES', false),
        'scheduled_messages' => env('WHATSAPP_FEATURE_SCHEDULED_MESSAGES', false),
        'chatbot_integration' => env('WHATSAPP_FEATURE_CHATBOT_INTEGRATION', false),
        'analytics_dashboard' => env('WHATSAPP_FEATURE_ANALYTICS_DASHBOARD', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Settings
    |--------------------------------------------------------------------------
    |
    | User interface customization settings
    |
    */
    'ui_settings' => [
        'theme' => env('WHATSAPP_UI_THEME', 'default'), // default, dark, custom
        'items_per_page' => env('WHATSAPP_UI_ITEMS_PER_PAGE', 20),
        'show_preview' => env('WHATSAPP_UI_SHOW_PREVIEW', true),
        'auto_refresh_interval' => env('WHATSAPP_UI_AUTO_REFRESH_INTERVAL', 30), // seconds
        'enable_sound_notifications' => env('WHATSAPP_UI_ENABLE_SOUND_NOTIFICATIONS', true),
    ],
];