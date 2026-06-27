<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register shiprocket.enabled binding for use across the app
        $this->app->bind('shiprocket.enabled', function () {
            return !empty(config('shiprocket.email')) && !empty(config('shiprocket.password'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        
        // Use Bootstrap 4 pagination view (updated with Bootstrap 5 styling)
        Paginator::defaultView('pagination::bootstrap-4');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-4');

        // Explicit route model binding for return requests
        \Illuminate\Support\Facades\Route::model('returnRequest', \App\Models\OrderReturnRequest::class);
    }
}
