<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

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
