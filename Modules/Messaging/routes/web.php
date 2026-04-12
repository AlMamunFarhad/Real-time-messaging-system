<?php

use Illuminate\Support\Facades\Route;
use Modules\Messaging\Http\Controllers\MessageController;
use Modules\Messaging\Http\Controllers\MessagingController;
use Modules\Messaging\Http\Controllers\ChatController;
use Modules\Messaging\Http\Controllers\OnlineStatusController;
use Modules\Messaging\Helpers\AuthParticipant;

// Send message - works for both user and admin via AuthParticipant
Route::post('/send-message', [MessageController::class, 'send'])
    ->middleware(['auth:admin,web'])
    ->name('messages.send');

// Online status routes
Route::post('/online-heartbeat', [OnlineStatusController::class, 'heartbeat'])
    ->middleware(['auth:admin,web'])
    ->name('online.heartbeat');

// Get online status - public for checking, requires web middleware for session
Route::middleware(['web'])->group(function () {
    Route::get('/online-status/{userId}/{type}', [OnlineStatusController::class, 'check'])
        ->name('online.check');
});

Route::middleware(['web', 'auth:admin,web'])->group(function () {
    // Send message
    Route::post('/send-message', [MessageController::class, 'send'])
        ->name('messages.send');

    // Mark messages as read
    Route::post('/mark-read', [MessageController::class, 'markRead'])
        ->name('messages.markRead');

    Route::middleware(['web', 'auth:admin,web'])->group(function () {
        // Get conversations for message icon
        Route::get('/messages/conversations', [MessagingController::class, 'getConversations'])
            ->name('messages.conversations');
    });

    // Load messages for a conversation (web route - session auth works here)
    Route::get('/messages/{conversationId}', [MessagingController::class, 'messagesWeb'])
        ->name('messages.load');

    // Chat with specific user - /chat/{userId}/{type} (e.g., /chat/1/admin)
    Route::get('/chat/{userId}/{type}', [ChatController::class, 'index'])
        ->name('chat.index');

    // Chat by conversation ID - /chat/{conversationId}
    Route::get('/chat/{conversationId}', [ChatController::class, 'show'])
        ->name('chat.show');
});
