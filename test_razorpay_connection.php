<?php

// Quick test script to verify Razorpay connection
// Run with: php test_razorpay_connection.php

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Illuminate\Support\Facades\Http;

$key = $_ENV['RAZORPAY_KEY'];
$secret = $_ENV['RAZORPAY_SECRET'];

echo "Testing Razorpay API connection...\n";
echo "Environment: " . ($_ENV['APP_ENV'] ?? 'local') . "\n";
echo "SSL Skip: " . ($_ENV['RAZORPAY_SKIP_SSL_VERIFICATION'] ?? 'false') . "\n\n";

try {
    // Test connection with SSL verification disabled
    $httpOptions = ['verify' => false];
    
    $response = \Illuminate\Support\Facades\Http::withBasicAuth($key, $secret)
        ->withOptions($httpOptions)
        ->timeout(30)
        ->post('https://api.razorpay.com/v1/orders', [
            'amount' => 100, // 1 INR in paise
            'currency' => 'INR',
            'receipt' => 'test_' . time(),
        ]);

    if ($response->successful()) {
        echo "✅ SUCCESS: Razorpay API connection working!\n";
        echo "Order ID: " . $response->json()['id'] . "\n";
        echo "Status: " . $response->json()['status'] . "\n";
    } else {
        echo "❌ API Error: " . $response->status() . "\n";
        echo "Response: " . $response->body() . "\n";
    }

} catch (Exception $e) {
    echo "❌ Connection Error: " . $e->getMessage() . "\n";
    
    // Try to identify if it's still an SSL issue
    if (strpos($e->getMessage(), 'SSL') !== false || strpos($e->getMessage(), 'certificate') !== false) {
        echo "\n🔧 This is still an SSL issue. Possible solutions:\n";
        echo "1. Restart your web server after the changes\n";
        echo "2. Check if curl.cainfo is set in php.ini\n";
        echo "3. Download cacert.pem and update php.ini\n";
    }
}

echo "\nTest completed.\n";