<?php

namespace Modules\Messaging\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Messaging\Helpers\AuthParticipant;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\ConversationParticipant;

class ChatController extends Controller
{
    protected function resolveType($type)
    {
        return match ($type) {
            'admin' => \App\Models\Admin::class,
            'user' => \App\Models\User::class,
            default => $type,
        };
    }

    public function index($userId, $type)
    {
        $senderId = AuthParticipant::id();
        $senderType = AuthParticipant::type();

        if (!$senderId || !$senderType) {       
            abort(401, 'Unauthorized');
        }

        $senderTypeShort = strtolower(class_basename($senderType));
        
        // Regular users can only chat with admin
        if ($senderTypeShort === 'user' && strtolower($type) !== 'admin') {
            abort(403, 'Users can only message the admin');
        }
        
        // Admin can only chat with users
        if ($senderTypeShort === 'admin' && strtolower($type) !== 'user') {
            abort(403, 'Admin can only message users');
        }

        // Get current user name
        $currentUserName = 'User';
        $currentUser = $senderType::find($senderId);
        if ($currentUser) {
            $currentUserName = $currentUser->name ?? ($currentUser->email ?? 'User #' . $senderId);
        }

        // Resolve type to full class name
        $receiverType = $this->resolveType($type);

        // conversation find or create - match both class name and short name
        $conversation = Conversation::where('type', 'private')
            ->whereHas('participants', function ($q) use ($senderId, $senderType) {
                $q->where('participant_id', $senderId)
                    ->whereIn('participant_type', [$senderType, strtolower(class_basename($senderType))]);
            })
            ->whereHas('participants', function ($q) use ($userId, $receiverType) {
                $q->where('participant_id', $userId)
                    ->whereIn('participant_type', [$receiverType, strtolower(class_basename($receiverType))]);
            })
            ->first();

        // create if not exists
        if (!$conversation) {
            $conversation = Conversation::create(['type' => 'private']);

            ConversationParticipant::insert([
                [
                    'conversation_id' => $conversation->id,
                    'participant_id' => $senderId,
                    'participant_type' => $senderType,
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'conversation_id' => $conversation->id,
                    'participant_id' => $userId,
                    'participant_type' => $receiverType,
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }

        return view('messaging::chat.index', compact('conversation', 'currentUserName'));
    }

    public function show($conversationId)
    {
        $senderId = AuthParticipant::id();
        $senderType = AuthParticipant::type();

        if (!$senderId || !$senderType) {
            abort(401, 'Unauthorized');
        }

        $senderTypeShort = strtolower(class_basename($senderType));

        $conversation = Conversation::where('id', $conversationId)
            ->whereHas('participants', function ($q) use ($senderId, $senderType) {
                $q->where('participant_id', $senderId)
                    ->whereIn('participant_type', [$senderType, strtolower(class_basename($senderType))]);
            })
            ->with('participants')
            ->first();

        if (!$conversation) {
            abort(403, 'You are not a participant of this conversation');
        }

        // Regular users can only view conversations with admin
        if ($senderTypeShort === 'user') {
            $hasAdmin = $conversation->participants()->where('participant_type', 'App\Models\Admin')->exists();
            if (!$hasAdmin) {
                abort(403, 'You can only view conversations with the admin');
            }
        }

        // Get current user name
        $currentUserName = 'User';
        $currentUser = $senderType::find($senderId);
        if ($currentUser) {
            $currentUserName = $currentUser->name ?? ($currentUser->email ?? 'User #' . $senderId);
        }

        $conversation = Conversation::where('id', $conversationId)
            ->whereHas('participants', function ($q) use ($senderId, $senderType) {
                $q->where('participant_id', $senderId)
                    ->whereIn('participant_type', [$senderType, strtolower(class_basename($senderType))]);
            })
            ->with('participants')
            ->first();

        if (!$conversation) {
            abort(403, 'You are not a participant of this conversation');
        }

        return view('messaging::chat.index', compact('conversation', 'currentUserName'));
    }
}
