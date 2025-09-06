<?php

use Illuminate\Support\Facades\Route;
use App\Mail\WelcomeEmail;
use App\Models\User;

Route::get('/preview-welcome-email', function () {
    // Get the first user or create a sample one for preview
    $user = User::first() ?? new User([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'country_code' => '+91',
        'mobile_number' => '9876543210',
        'created_at' => now()
    ]);

    return new WelcomeEmail($user);
})->name('preview.welcome.email');
