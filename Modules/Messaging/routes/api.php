<?php

use Illuminate\Support\Facades\Route;
use Modules\Messaging\Http\Middleware\EnsureMessagingFeatureEnabled;
use Modules\Messaging\Http\Controllers\MessagingController;

// API routes - requires sanctum (for mobile/external apps)
// For web, use web routes
Route::middleware(['auth:sanctum', EnsureMessagingFeatureEnabled::class . ':enabled'])->prefix('v1')->group(function () {
    Route::apiResource('messagings', MessagingController::class)->names('messaging');
    Route::get('messagings/{conversationId}/messages', [MessagingController::class, 'messages']);
});

// Web-based message loading (for admin panel / web app)
Route::middleware(['web', 'auth:admin,web', EnsureMessagingFeatureEnabled::class . ':enabled'])->prefix('web')->group(function () {
    Route::get('messagings/{conversationId}/messages', [MessagingController::class, 'messagesWeb']);
});
