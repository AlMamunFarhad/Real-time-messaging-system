<?php

use Illuminate\Support\Facades\Route;
use Modules\Messaging\Http\Controllers\MessageController;
use Modules\Messaging\Http\Controllers\MessagingController;
use Modules\Messaging\Http\Controllers\ChatController;
use Modules\Messaging\Helpers\AuthParticipant;

// Send message - works for both user and admin via AuthParticipant
Route::post('/send-message', [MessageController::class, 'send'])
    ->middleware(['auth:admin,web'])
    ->name('messages.send');

Route::middleware(['web', 'auth:admin,web'])->group(function () {
    // Send message
    Route::post('/send-message', [MessageController::class, 'send'])
        ->name('messages.send');
    
    // Mark messages as read
    Route::post('/mark-read', [MessageController::class, 'markRead'])
        ->name('messages.markRead');
    
    // Chat with specific user - /chat/{userId}/{type} (e.g., /chat/1/admin)
    Route::get('/chat/{userId}/{type}', [ChatController::class, 'index'])
        ->name('chat.index');
    
    // Chat by conversation ID - /chat/{conversationId}
    Route::get('/chat/{conversationId}', [ChatController::class, 'show'])
        ->name('chat.show');
});
