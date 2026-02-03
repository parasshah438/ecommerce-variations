<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ShiprocketService;
use Exception;

class TestShiprocketConnection extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'shiprocket:test-connection';

    /**
     * The console command description.
     */
    protected $description = 'Test Shiprocket API connection and authentication';

    /**
     * Execute the console command.
     */
    public function handle()
    {
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
            $shiprocketService = app(ShiprocketService::class);
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
    }
}