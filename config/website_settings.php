<?php

return [

    'tabs' => [
        'general' => [
            'label' => 'General',
            'icon' => 'fa-globe',
            'description' => 'Site name, URL, and application environment',
            'keys' => [
                'APP_NAME' => ['label' => 'Application Name', 'type' => 'text', 'rules' => 'required|string|max:255'],
                'APP_URL' => ['label' => 'Site URL', 'type' => 'url', 'rules' => 'required|url|max:255'],
                'APP_ENV' => ['label' => 'Environment', 'type' => 'select', 'options' => ['local' => 'Local', 'staging' => 'Staging', 'production' => 'Production'], 'rules' => 'required|in:local,staging,production'],
                'APP_DEBUG' => ['label' => 'Debug Mode', 'type' => 'boolean', 'rules' => 'required|boolean'],
                'LOG_LEVEL' => ['label' => 'Log Level', 'type' => 'select', 'options' => ['debug' => 'Debug', 'info' => 'Info', 'warning' => 'Warning', 'error' => 'Error'], 'rules' => 'required|in:debug,info,warning,error'],
                'CACHE_STORE' => ['label' => 'Cache Driver', 'type' => 'select', 'options' => ['file' => 'File', 'database' => 'Database', 'redis' => 'Redis'], 'rules' => 'required|in:file,database,redis'],
            ],
        ],

        'mail' => [
            'label' => 'SMTP / Email',
            'icon' => 'fa-envelope',
            'description' => 'Outgoing mail server configuration (.env MAIL_*)',
            'keys' => [
                'MAIL_MAILER' => ['label' => 'Mail Driver', 'type' => 'select', 'options' => ['smtp' => 'SMTP', 'log' => 'Log (testing)', 'sendmail' => 'Sendmail'], 'rules' => 'required|in:smtp,log,sendmail'],
                'MAIL_HOST' => ['label' => 'SMTP Host', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                'MAIL_PORT' => ['label' => 'SMTP Port', 'type' => 'number', 'rules' => 'nullable|integer|min:1|max:65535'],
                'MAIL_USERNAME' => ['label' => 'SMTP Username', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                'MAIL_PASSWORD' => ['label' => 'SMTP Password', 'type' => 'password', 'sensitive' => true, 'rules' => 'nullable|string|max:255'],
                'MAIL_FROM_ADDRESS' => ['label' => 'From Email', 'type' => 'email', 'rules' => 'nullable|email|max:255'],
                'MAIL_FROM_NAME' => ['label' => 'From Name', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
            ],
        ],

        'payment' => [
            'label' => 'Payments',
            'icon' => 'fa-credit-card',
            'description' => 'Razorpay API keys and webhook settings',
            'keys' => [
                'RAZORPAY_KEY' => ['label' => 'Razorpay Key ID', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                'RAZORPAY_SECRET' => ['label' => 'Razorpay Secret', 'type' => 'password', 'sensitive' => true, 'rules' => 'nullable|string|max:255'],
                'RAZORPAY_WEBHOOK_SECRET' => ['label' => 'Webhook Secret', 'type' => 'password', 'sensitive' => true, 'rules' => 'nullable|string|max:255'],
                'RAZORPAY_SKIP_SSL_VERIFICATION' => ['label' => 'Skip SSL Verification (local only)', 'type' => 'boolean', 'rules' => 'nullable|boolean'],
            ],
        ],

        'shipping' => [
            'label' => 'Shipping',
            'icon' => 'fa-truck',
            'description' => 'Shiprocket integration credentials',
            'keys' => [
                'SHIPROCKET_EMAIL' => ['label' => 'Shiprocket Email', 'type' => 'text', 'rules' => 'nullable|email|max:255'],
                'SHIPROCKET_PASSWORD' => ['label' => 'Shiprocket Password', 'type' => 'password', 'sensitive' => true, 'rules' => 'nullable|string|max:255'],
                'SHIPROCKET_BASE_URL' => ['label' => 'API Base URL', 'type' => 'text', 'rules' => 'nullable|url|max:255'],
                'SHIPROCKET_PICKUP_POSTCODE' => ['label' => 'Pickup Postcode', 'type' => 'text', 'rules' => 'nullable|string|max:20'],
                'SHIPROCKET_DEFAULT_PICKUP_LOCATION' => ['label' => 'Default Pickup Location', 'type' => 'text', 'rules' => 'nullable|string|max:100'],
                'SHIPROCKET_AUTO_CREATE_SHIPMENTS' => ['label' => 'Auto Create Shipments', 'type' => 'boolean', 'rules' => 'nullable|boolean'],
            ],
        ],

        'database' => [
            'label' => 'Database',
            'icon' => 'fa-database',
            'description' => 'Database connection settings (changes require care)',
            'keys' => [
                'DB_CONNECTION' => ['label' => 'Connection', 'type' => 'select', 'options' => ['mysql' => 'MySQL', 'sqlite' => 'SQLite', 'pgsql' => 'PostgreSQL'], 'rules' => 'required|in:mysql,sqlite,pgsql'],
                'DB_HOST' => ['label' => 'Host', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                'DB_PORT' => ['label' => 'Port', 'type' => 'number', 'rules' => 'nullable|integer|min:1|max:65535'],
                'DB_DATABASE' => ['label' => 'Database Name', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                'DB_USERNAME' => ['label' => 'Username', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                'DB_PASSWORD' => ['label' => 'Password', 'type' => 'password', 'sensitive' => true, 'rules' => 'nullable|string|max:255'],
            ],
        ],
    ],

    'readonly_keys' => [
        'APP_KEY',
    ],

];
