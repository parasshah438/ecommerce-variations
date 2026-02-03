<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('shiprocket:test-connection', function () {
    $this->info('🔄 Testing Shiprocket connection...');

    try {
        // Check if credentials are configured
        $email = config('services.shiprocket.email');
        $password = config('services.shiprocket.password');

        if (empty($email) || empty($password)) {
            $this->error('❌ Shiprocket credentials not configured');
            $this->info('Please set the following in your .env file:');
            $this->info('SHIPROCKET_EMAIL=your_email@example.com');
            $this->info('SHIPROCKET_PASSWORD=your_password');
            return 1;
        }

        $this->info("📧 Email: {$email}");
        $this->info("🔐 Password: " . str_repeat('*', strlen($password)));

        // Test authentication
        $shiprocketService = app(\App\Services\ShiprocketService::class);
        $tokenResponse = $shiprocketService->generateToken();

        if (isset($tokenResponse['token'])) {
            $this->info('✅ Authentication successful!');
            $this->info('🎟️  Token generated: ' . substr($tokenResponse['token'], 0, 20) . '...');
            
            // Test a simple API call
            try {
                $profile = $shiprocketService->getProfile();
                if (isset($profile['first_name'])) {
                    $this->info("👤 Connected as: {$profile['first_name']} {$profile['last_name']}");
                    $this->info("🏢 Company: " . ($profile['company_name'] ?? 'N/A'));
                }
            } catch (Exception $e) {
                $this->warn('⚠️  Profile fetch failed: ' . $e->getMessage());
            }

            $this->info('🚀 Shiprocket integration is ready to use!');
            return 0;
        } else {
            $this->error('❌ Authentication failed');
            $this->error('Response: ' . json_encode($tokenResponse));
            return 1;
        }

    } catch (Exception $e) {
        $this->error('❌ Connection test failed');
        $this->error('Error: ' . $e->getMessage());
        
        if (str_contains($e->getMessage(), 'credentials not configured')) {
            $this->info('💡 Please configure your Shiprocket credentials:');
            $this->info('1. Get your credentials from https://app.shiprocket.in/');
            $this->info('2. Add them to your .env file:');
            $this->info('   SHIPROCKET_EMAIL=your_email@example.com');
            $this->info('   SHIPROCKET_PASSWORD=your_password');
        }

        return 1;
    }
})->purpose('Test Shiprocket API connection and authentication');

Artisan::command('shiprocket:process', function() {
    $this->info('🔄 Processing orders for Shiprocket shipment...');

    try {
        $shiprocketProcessor = app(\App\Services\ShiprocketOrderProcessor::class);
        
        // Get confirmed orders that are paid but not yet shipped
        $orders = \App\Models\Order::where('status', 'confirmed')
                                   ->where('payment_status', 'paid')
                                   ->whereNull('shipment_id')
                                   ->get();

        if ($orders->count() == 0) {
            $this->info('📭 No orders found to process');
            return 0;
        }

        $this->info("📦 Found {$orders->count()} orders to process");

        foreach ($orders as $order) {
            try {
                $result = $shiprocketProcessor->processConfirmedOrder($order);
                
                if ($result['success']) {
                    $this->info("✅ Order #{$order->id} processed successfully - Shipment ID: {$result['shipment_id']}");
                } else {
                    $this->error("❌ Order #{$order->id} failed: {$result['message']}");
                }
            } catch (Exception $e) {
                $this->error("❌ Order #{$order->id} error: {$e->getMessage()}");
            }
        }

        return 0;

    } catch (Exception $e) {
        $this->error('❌ Processing failed');
        $this->error('Error: ' . $e->getMessage());
        return 1;
    }
})->purpose('Process pending orders for Shiprocket shipment');

// Image queue processing for GoDaddy shared hosting
Schedule::command('queue:work --queue=images --stop-when-empty --timeout=45 --memory=128 --tries=2')
    ->everyMinute()
    ->withoutOverlapping(2) // Prevent overlap with 2 min lock
    ->runInBackground();

// Clean up failed jobs weekly  
Schedule::command('queue:prune-failed --hours=168')
    ->weekly();

// Optional: Clean up old cache entries
Schedule::command('cache:prune-stale-tags')
    ->hourly();
