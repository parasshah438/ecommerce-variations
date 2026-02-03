<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ShiprocketOrderProcessor;
use App\Services\ShiprocketManager;
use App\Services\ShippingCalculatorService;

class ShiprocketServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register services as singletons but don't instantiate immediately
        $this->app->singleton(ShiprocketManager::class, function ($app) {
            return new ShiprocketManager();
        });

        $this->app->singleton(ShiprocketOrderProcessor::class, function ($app) {
            return new ShiprocketOrderProcessor(
                $app->make(ShiprocketManager::class),
                $app->make(ShippingCalculatorService::class)
            );
        });

        // Make services conditional based on configuration
        $this->app->bind('shiprocket.enabled', function () {
            return !empty(config('services.shiprocket.email')) && 
                   !empty(config('services.shiprocket.password'));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}