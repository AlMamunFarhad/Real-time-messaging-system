<?php

use Illuminate\Support\Facades\Route;
use Modules\Messaging\Http\Controllers\GroupController;
use Modules\Messaging\Http\Controllers\MessageController;
use Modules\Messaging\Http\Controllers\MessagingController;
use Modules\Messaging\Http\Controllers\ParticipantDirectoryController;
use Modules\Messaging\Http\Controllers\ChatController;
use Modules\Messaging\Http\Controllers\OnlineStatusController;
use Modules\Messaging\Http\Controllers\SummaryController;
use Modules\Messaging\Http\Controllers\VoiceCallController;
use Modules\Messaging\Http\Middleware\EnsureMessagingFeatureEnabled;


// Online status routes
Route::post('/online-heartbeat', [OnlineStatusController::class, 'heartbeat'])
    ->middleware(['auth:admin,web', EnsureMessagingFeatureEnabled::class . ':online_status'])
    ->name('online.heartbeat');

// Get online status - public for checking, requires web middleware for session
Route::middleware(['web'])->group(function () {
    Route::get('/online-status/{userId}/{type}', [OnlineStatusController::class, 'check'])
        ->middleware(EnsureMessagingFeatureEnabled::class . ':online_status')
        ->name('online.check');
});

Route::middleware(['web', 'auth:admin,web', EnsureMessagingFeatureEnabled::class . ':enabled'])->group(function () {
    Route::get('/messages/dashboard', [ChatController::class, 'dashboard'])
        ->name('messages.dashboard');

    Route::get('/messages/direct', [ChatController::class, 'directConversation'])
        ->name('messages.direct');

    Route::get('/messages/participants', [ParticipantDirectoryController::class, 'index'])
        ->name('messages.participants');

    Route::post('/messages/groups', [GroupController::class, 'store'])
        ->middleware(EnsureMessagingFeatureEnabled::class . ':groups')
        ->name('messages.groups.store');

    Route::get('/messages/groups/{conversationId}', [GroupController::class, 'show'])
        ->middleware(EnsureMessagingFeatureEnabled::class . ':groups')
        ->name('messages.groups.show');

    Route::post('/messages/groups/{conversationId}/members', [GroupController::class, 'addMembers'])
        ->middleware(EnsureMessagingFeatureEnabled::class . ':groups')
        ->name('messages.groups.members.store');

    Route::delete('/messages/groups/{conversationId}/members/{memberType}/{memberId}', [GroupController::class, 'removeMember'])
        ->middleware(EnsureMessagingFeatureEnabled::class . ':groups')
        ->name('messages.groups.members.destroy');

    Route::post('/messages/groups/{conversationId}/leave', [GroupController::class, 'leave'])
        ->middleware(EnsureMessagingFeatureEnabled::class . ':groups')
        ->name('messages.groups.leave');

    // Send message
    Route::post('/send-message', [MessageController::class, 'send'])
        ->name('messages.send');

    // Download attachment
    Route::get('/messages/download-attachment', [MessageController::class, 'downloadAttachment'])
        ->name('messages.download-attachment');

    // Mark messages as read
    Route::post('/mark-read', [MessageController::class, 'markRead'])
        ->name('messages.markRead');

    Route::patch('/messages/{message}', [MessageController::class, 'update'])
        ->name('messages.update');

    Route::delete('/messages/{message}', [MessageController::class, 'destroy'])
        ->name('messages.destroy');

    Route::delete('/messages/conversations/{conversationId}/clear', [MessageController::class, 'clearConversation'])
        ->name('messages.clear');

    // Get conversations for message icon
    Route::get('/messages/conversations', [MessagingController::class, 'getConversations'])
        ->name('messages.conversations');

    Route::get('/messages/notifications/feed', [MessagingController::class, 'notificationsFeed'])
        ->middleware(EnsureMessagingFeatureEnabled::class . ':notifications')
        ->name('messages.notifications.feed');

    // Load messages for a conversation (web route - session auth works here)
    Route::get('/messages/{conversationId}', [MessagingController::class, 'messagesWeb'])
        ->name('messages.load');

    // AI Summary and PDF Download
    Route::get('/messages/{conversationId}/summary', [SummaryController::class, 'generate'])
        ->middleware(EnsureMessagingFeatureEnabled::class . ':ai_summary')
        ->name('messages.summary');

    // Chat with specific user - /chat/{userId}/{type} (e.g., /chat/1/admin)
    Route::get('/chat/{userId}/{type}', [ChatController::class, 'dashboard'])
        ->name('chat.index');

    // Chat by conversation ID - /chat/{conversationId}
    Route::get('/chat/{conversationId}', [ChatController::class, 'dashboard'])
        ->name('chat.show');

    // Voice Call Signaling Routes
    Route::post('/voice-call/initiate', [VoiceCallController::class, 'initiateCall'])
        ->middleware(EnsureMessagingFeatureEnabled::class . ':audio_call,video_call')
        ->name('voice.initiate');
    Route::post('/voice-call/answer', [VoiceCallController::class, 'answerCall'])
        ->middleware(EnsureMessagingFeatureEnabled::class . ':audio_call,video_call')
        ->name('voice.answer');
    Route::post('/voice-call/reject', [VoiceCallController::class, 'rejectCall'])
        ->middleware(EnsureMessagingFeatureEnabled::class . ':audio_call,video_call')
        ->name('voice.reject');
    Route::post('/voice-call/ringing', [VoiceCallController::class, 'ringCall'])
        ->middleware(EnsureMessagingFeatureEnabled::class . ':audio_call,video_call')
        ->name('voice.ringing');
    Route::post('/voice-call/ice-candidate', [VoiceCallController::class, 'sendIceCandidate'])
        ->middleware(EnsureMessagingFeatureEnabled::class . ':audio_call,video_call')
        ->name('voice.ice');
    Route::post('/voice-call/hangup', [VoiceCallController::class, 'hangupCall'])
        ->middleware(EnsureMessagingFeatureEnabled::class . ':audio_call,video_call')
        ->name('voice.hangup');
    Route::get('/voice-call/poll', [VoiceCallController::class, 'pollSignals'])
        ->middleware(EnsureMessagingFeatureEnabled::class . ':audio_call,video_call')
        ->name('voice.poll');

    Route::post('/messages/toggle-pin', [ChatController::class, 'togglePin'])
        ->middleware(EnsureMessagingFeatureEnabled::class . ':pinning')
        ->name('messages.toggle-pin');
});
