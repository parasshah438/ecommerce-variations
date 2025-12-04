<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsApp\WhatsAppController;

/*
|--------------------------------------------------------------------------
| WhatsApp Routes
|--------------------------------------------------------------------------
|
| Here are all the routes related to WhatsApp messaging functionality
| using Ultramsg API integration
|
*/

Route::prefix('whatsapp')->name('whatsapp.')->middleware(['auth'])->group(function () {
    
    // Dashboard & Overview
    Route::get('/', [WhatsAppController::class, 'index'])->name('index');
    Route::get('/dashboard', [WhatsAppController::class, 'index'])->name('dashboard');
    
    // Send Messages
    Route::get('/send', [WhatsAppController::class, 'sendForm'])->name('send.form');
    Route::post('/send-text', [WhatsAppController::class, 'sendText'])->name('send.text');
    Route::post('/send-image', [WhatsAppController::class, 'sendImage'])->name('send.image');
    Route::post('/send-document', [WhatsAppController::class, 'sendDocument'])->name('send.document');
    Route::post('/send-audio', [WhatsAppController::class, 'sendAudio'])->name('send.audio');
    Route::post('/send-video', [WhatsAppController::class, 'sendImage'])->name('send.video');
    Route::post('/send-contact', [WhatsAppController::class, 'sendContact'])->name('send.contact');
    Route::post('/send-location', [WhatsAppController::class, 'sendContact'])->name('send.location');
    Route::post('/send-template', [WhatsAppController::class, 'sendText'])->name('send.template');
    
    // Bulk Messaging
    Route::get('/bulk', [WhatsAppController::class, 'sendForm'])->name('bulk.form');
    Route::post('/bulk-send', [WhatsAppController::class, 'bulkSend'])->name('bulk.send');
    Route::get('/bulk-status/{batchId}', [WhatsAppController::class, 'getBulkStatus'])->name('bulk.status');
    
    // Message Status & Tracking
    Route::get('/messages', [WhatsAppController::class, 'messagesList'])->name('messages.list');
    Route::get('/message/{messageId}', [WhatsAppController::class, 'messagesList'])->name('message.details');
    Route::get('/message-status/{messageId}', [WhatsAppController::class, 'instanceStatus'])->name('message.status');
    
    // Templates Management
    Route::get('/templates', [WhatsAppController::class, 'templates'])->name('templates.index');
    Route::get('/templates/create', [WhatsAppController::class, 'templates'])->name('templates.create');
    Route::post('/templates/store', [WhatsAppController::class, 'templates'])->name('templates.store');
    Route::get('/templates/{template}/edit', [WhatsAppController::class, 'templates'])->name('templates.edit');
    Route::put('/templates/{template}', [WhatsAppController::class, 'templates'])->name('templates.update');
    Route::delete('/templates/{template}', [WhatsAppController::class, 'templates'])->name('templates.delete');
    
    // Contacts Management
    Route::get('/contacts', [WhatsAppController::class, 'contacts'])->name('contacts.index');
    Route::post('/contacts/import', [WhatsAppController::class, 'contacts'])->name('contacts.import');
    Route::post('/contacts/export', [WhatsAppController::class, 'contacts'])->name('contacts.export');
    Route::post('/contacts/create', [WhatsAppController::class, 'contacts'])->name('contacts.create');
    Route::put('/contacts/{contact}', [WhatsAppController::class, 'contacts'])->name('contacts.update');
    Route::delete('/contacts/{contact}', [WhatsAppController::class, 'contacts'])->name('contacts.delete');
    
    // Groups Management
    Route::get('/groups', [WhatsAppController::class, 'index'])->name('groups.index');
    Route::post('/groups/create', [WhatsAppController::class, 'index'])->name('groups.create');
    Route::post('/groups/{groupId}/add-member', [WhatsAppController::class, 'index'])->name('groups.add.member');
    Route::delete('/groups/{groupId}/remove-member', [WhatsAppController::class, 'index'])->name('groups.remove.member');
    Route::delete('/groups/{groupId}', [WhatsAppController::class, 'index'])->name('groups.delete');
    
    // Settings & Configuration
    Route::get('/settings', [WhatsAppController::class, 'settings'])->name('settings');
    Route::post('/settings/update', [WhatsAppController::class, 'updateSettings'])->name('settings.update');
    Route::post('/webhook/verify', [WhatsAppController::class, 'settings'])->name('webhook.verify');
    
    // Analytics & Reports
    Route::get('/analytics', [WhatsAppController::class, 'analytics'])->name('analytics');
    Route::get('/reports', [WhatsAppController::class, 'analytics'])->name('reports');
    Route::get('/reports/download', [WhatsAppController::class, 'analytics'])->name('reports.download');
    
    // Instance Management
    Route::get('/instance/status', [WhatsAppController::class, 'instanceStatus'])->name('instance.status');
    Route::post('/instance/restart', [WhatsAppController::class, 'instanceStatus'])->name('instance.restart');
    Route::get('/qr-code', [WhatsAppController::class, 'getQRCode'])->name('qr.code');
    
    // API Testing & Setup
    Route::get('/setup', function() { return view('whatsapp.setup-guide'); })->name('setup.guide');
    Route::get('/test', [WhatsAppController::class, 'settings'])->name('test.api');
    Route::post('/test-connection', [WhatsAppController::class, 'testConnection'])->name('test.connection');
});

// Public Webhook Routes (No authentication required)
Route::prefix('whatsapp/webhook')->name('whatsapp.webhook.')->group(function () {
    Route::post('/message', [WhatsAppController::class, 'handleIncomingMessage'])->name('message');
    Route::post('/status', [WhatsAppController::class, 'handleIncomingMessage'])->name('status');
    Route::post('/instance', [WhatsAppController::class, 'handleIncomingMessage'])->name('instance');
});

// Admin WhatsApp Routes (Admin authentication required)
Route::prefix('admin/whatsapp')->name('admin.whatsapp.')->middleware(['auth'])->group(function () {
    Route::get('/', [WhatsAppController::class, 'index'])->name('index');
    Route::get('/users', [WhatsAppController::class, 'index'])->name('users');
    Route::get('/usage-stats', [WhatsAppController::class, 'analytics'])->name('usage.stats');
    Route::post('/broadcast-all', [WhatsAppController::class, 'bulkSend'])->name('broadcast.all');
    Route::get('/system-logs', [WhatsAppController::class, 'analytics'])->name('system.logs');
});

// API Routes for AJAX calls
Route::prefix('api/whatsapp')->name('api.whatsapp.')->middleware(['auth'])->group(function () {
    Route::get('/contacts/search', [WhatsAppController::class, 'contacts'])->name('contacts.search');
    Route::get('/templates/search', [WhatsAppController::class, 'templates'])->name('templates.search');
    Route::post('/validate-phone', [WhatsAppController::class, 'validatePhoneNumber'])->name('validate.phone');
    Route::get('/message-stats', [WhatsAppController::class, 'analytics'])->name('message.stats');
    Route::post('/upload-media', [WhatsAppController::class, 'uploadMedia'])->name('upload.media');
});