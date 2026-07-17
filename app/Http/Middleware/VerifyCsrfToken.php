<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'admin/users/test',
        'admin/users/debug',
        'admin/users/statistics',

        // Chatbot endpoint (avoid 419 Page Expired)
        'chatbot/chat',
        '/chatbot/chat',
        'chatbot/chat/',
        '/chatbot/chat/',

        // Shiprocket webhooks - external server posts without a CSRF token,
        // so these must be excluded or every callback returns 419 and is dropped.
        'webhooks/shiprocket/*',
    ];
}
