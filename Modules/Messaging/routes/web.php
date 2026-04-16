<?php

use Illuminate\Support\Facades\Route;
use Modules\Messaging\Http\Controllers\GroupController;
use Modules\Messaging\Http\Controllers\MessageController;
use Modules\Messaging\Http\Controllers\MessagingController;
use Modules\Messaging\Http\Controllers\ParticipantDirectoryController;
use Modules\Messaging\Http\Controllers\ChatController;
use Modules\Messaging\Http\Controllers\OnlineStatusController;

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
    Route::get('/messages/dashboard', [ChatController::class, 'dashboard'])
        ->name('messages.dashboard');

    Route::get('/messages/direct', [ChatController::class, 'directConversation'])
        ->name('messages.direct');

    Route::get('/messages/participants', [ParticipantDirectoryController::class, 'index'])
        ->name('messages.participants');

    Route::post('/messages/groups', [GroupController::class, 'store'])
        ->name('messages.groups.store');

    Route::get('/messages/groups/{conversationId}', [GroupController::class, 'show'])
        ->name('messages.groups.show');

    Route::post('/messages/groups/{conversationId}/members', [GroupController::class, 'addMembers'])
        ->name('messages.groups.members.store');

    Route::delete('/messages/groups/{conversationId}/members/{memberType}/{memberId}', [GroupController::class, 'removeMember'])
        ->name('messages.groups.members.destroy');

    Route::post('/messages/groups/{conversationId}/leave', [GroupController::class, 'leave'])
        ->name('messages.groups.leave');

    // Send message
    Route::post('/send-message', [MessageController::class, 'send'])
        ->name('messages.send');

    // Mark messages as read
    Route::post('/mark-read', [MessageController::class, 'markRead'])
        ->name('messages.markRead');

    // Get conversations for message icon
    Route::get('/messages/conversations', [MessagingController::class, 'getConversations'])
        ->name('messages.conversations');

    // Load messages for a conversation (web route - session auth works here)
    Route::get('/messages/{conversationId}', [MessagingController::class, 'messagesWeb'])
        ->name('messages.load');

    // Chat with specific user - /chat/{userId}/{type} (e.g., /chat/1/admin)
    Route::get('/chat/{userId}/{type}', [ChatController::class, 'dashboard'])
        ->name('chat.index');

    // Chat by conversation ID - /chat/{conversationId}
    Route::get('/chat/{conversationId}', [ChatController::class, 'dashboard'])
        ->name('chat.show');
});
