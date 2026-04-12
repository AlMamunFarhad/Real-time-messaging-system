<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use Modules\Messaging\Models\ConversationParticipant;
use Illuminate\Support\Facades\Log;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.admin.{id}', function ($user, $id) {
    return Auth::guard('admin')->check() && (int) Auth::guard('admin')->id() === (int) $id;
});

Broadcast::channel('user.user.{id}', function ($user, $id) {
    return Auth::guard('web')->check() && (int) Auth::guard('web')->id() === (int) $id;
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    Log::info('Channel authorization attempt', [
        'conversationId' => $conversationId,
        'user' => $user
    ]);
    
    $isAdmin = Auth::guard('admin')->check();
    $isWeb = Auth::guard('web')->check();

    if ($isAdmin) {
        $userId = Auth::guard('admin')->id();
        $userType = \App\Models\Admin::class;
        $userTypeShort = 'admin';
        Log::info('Admin auth for channel', ['userId' => $userId, 'type' => $userType]);
    } elseif ($isWeb) {
        $userId = Auth::guard('web')->id();
        $userType = \App\Models\User::class;
        $userTypeShort = 'user';
        Log::info('Web auth for channel', ['userId' => $userId, 'type' => $userType]);
    } else {
        $userId = $user->id ?? null;
        $userType = get_class($user ?? '');
        $userTypeShort = strtolower(class_basename($user ?? ''));
        Log::info('Other auth for channel', ['userId' => $userId, 'type' => $userType]);
    }

    if (!$userId) {
        Log::warning('No user ID found for channel authorization');
        return false;
    }

    $result = ConversationParticipant::where('conversation_id', $conversationId)
        ->where('participant_id', $userId)
        ->whereIn('participant_type', [$userType, $userTypeShort])
        ->exists();

    Log::info('Channel authorization result', [
        'conversationId' => $conversationId,
        'userId' => $userId,
        'userType' => $userType,
        'userTypeShort' => $userTypeShort,
        'result' => $result
    ]);

    return $result;
});
