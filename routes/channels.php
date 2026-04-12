<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use Modules\Messaging\Models\ConversationParticipant;

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
    $isAdmin = Auth::guard('admin')->check();
    $isWeb = Auth::guard('web')->check();

    if ($isAdmin) {
        $userId = Auth::guard('admin')->id();
        $userType = \App\Models\Admin::class;
        $userTypeShort = 'admin';
    } elseif ($isWeb) {
        $userId = Auth::guard('web')->id();
        $userType = \App\Models\User::class;
        $userTypeShort = 'user';
    } else {
        $userId = $user->id;
        $userType = get_class($user);
        $userTypeShort = strtolower(class_basename($user));
    }

    return ConversationParticipant::where('conversation_id', $conversationId)
        ->where('participant_id', $userId)
        ->whereIn('participant_type', [$userType, $userTypeShort])
        ->exists();
});
