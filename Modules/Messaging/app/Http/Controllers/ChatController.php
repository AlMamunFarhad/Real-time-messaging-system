<?php

namespace Modules\Messaging\Http\Controllers;

use App\Models\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
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

    protected function getParticipantDisplayName($participant): string
    {
        $participantType = $this->resolveType($participant->participant_type ?? null);

        if (!$participantType || !class_exists($participantType)) {
            return 'User #' . $participant->participant_id;
        }

        $model = $participantType::find($participant->participant_id);

        return $model?->name ?? ($model?->email ?? 'User #' . $participant->participant_id);
    }

    protected function findOtherParticipant($conversation, int $senderId, string $senderType)
    {
        $senderTypeShort = strtolower(class_basename($senderType));

        return $conversation->participants->first(function ($participant) use ($senderId, $senderType, $senderTypeShort) {
            $participantType = $this->resolveType($participant->participant_type ?? null);
            $participantTypeShort = strtolower(class_basename($participantType ?? ''));
            $sameId = (int) $participant->participant_id === $senderId;
            $sameType = $participantType === $senderType || $participantTypeShort === $senderTypeShort;

            return !($sameId && $sameType);
        });
    }

    protected function findOrCreatePrivateConversation(int $senderId, string $senderType, int $userId, string $receiverType): Conversation
    {
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
                ],
            ]);
        }

        return $conversation->load('participants');
    }

    public function adminDashboard(Request $request)
    {
        $senderId = AuthParticipant::id();
        $senderType = AuthParticipant::type();

        if (!$senderId || !$senderType) {
            abort(401, 'Unauthorized');
        }

        if (strtolower(class_basename($senderType)) !== 'admin') {
            abort(403, 'Only admins can access this page');
        }

        $conversation = null;
        $otherParticipant = null;
        $otherUserName = null;
        $selectedUserId = $request->integer('user');
        $selectedConversationId = $request->integer('conversation');

        if ($selectedUserId) {
            $selectedUser = User::findOrFail($selectedUserId);
            $conversation = $this->findOrCreatePrivateConversation(
                $senderId,
                $senderType,
                $selectedUser->id,
                User::class
            );
        } elseif ($selectedConversationId) {
            $conversation = Conversation::where('id', $selectedConversationId)
                ->whereHas('participants', function ($q) use ($senderId, $senderType) {
                    $q->where('participant_id', $senderId)
                        ->whereIn('participant_type', [$senderType, strtolower(class_basename($senderType))]);
                })
                ->with('participants')
                ->firstOrFail();
        }

        if ($conversation) {
            $otherParticipant = $this->findOtherParticipant($conversation, $senderId, $senderType);
            $otherUserName = $otherParticipant
                ? $this->getParticipantDisplayName($otherParticipant)
                : 'User';
        }

        $activeUserId = $otherParticipant?->participant_id ?? $selectedUserId;

        return view('messaging::chat.admin', compact(
            'conversation',
            'otherParticipant',
            'otherUserName',
            'activeUserId'
        ));
    }

    public function adminConversation(Request $request)
    {
        $senderId = AuthParticipant::id();
        $senderType = AuthParticipant::type();

        if (!$senderId || !$senderType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (strtolower(class_basename($senderType)) !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $selectedUserId = $request->integer('user');
        $selectedConversationId = $request->integer('conversation');
        $conversation = null;

        if ($selectedUserId) {
            $selectedUser = User::findOrFail($selectedUserId);
            $conversation = $this->findOrCreatePrivateConversation(
                $senderId,
                $senderType,
                $selectedUser->id,
                User::class
            );
        } elseif ($selectedConversationId) {
            $conversation = Conversation::where('id', $selectedConversationId)
                ->whereHas('participants', function ($q) use ($senderId, $senderType) {
                    $q->where('participant_id', $senderId)
                        ->whereIn('participant_type', [$senderType, strtolower(class_basename($senderType))]);
                })
                ->with('participants')
                ->firstOrFail();
        } else {
            return response()->json(['error' => 'User or conversation is required'], 422);
        }

        $otherParticipant = $this->findOtherParticipant($conversation, $senderId, $senderType);
        $otherUserName = $otherParticipant
            ? $this->getParticipantDisplayName($otherParticipant)
            : 'User';

        $resolvedOtherType = $this->resolveType($otherParticipant?->participant_type);

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
            ],
            'other_participant' => [
                'id' => $otherParticipant?->participant_id,
                'type' => $resolvedOtherType ? strtolower(class_basename($resolvedOtherType)) : null,
                'name' => $otherUserName,
            ],
        ]);
    }

    public function userConversation()
    {
        $senderId = AuthParticipant::id();
        $senderType = AuthParticipant::type();

        if (!$senderId || !$senderType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (strtolower(class_basename($senderType)) !== 'user') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $admin = Admin::query()->orderBy('id')->first();

        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
        }

        $conversation = $this->findOrCreatePrivateConversation(
            $senderId,
            $senderType,
            $admin->id,
            Admin::class
        );

        $otherParticipant = $this->findOtherParticipant($conversation, $senderId, $senderType);
        $otherUserName = $otherParticipant
            ? $this->getParticipantDisplayName($otherParticipant)
            : ($admin->name ?? 'Admin');

        $resolvedOtherType = $this->resolveType($otherParticipant?->participant_type);

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
            ],
            'other_participant' => [
                'id' => $otherParticipant?->participant_id ?? $admin->id,
                'type' => $resolvedOtherType ? strtolower(class_basename($resolvedOtherType)) : 'admin',
                'name' => $otherUserName,
            ],
        ]);
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

        // Resolve type to full class name
        $receiverType = $this->resolveType($type);

        // conversation find or create - match both class name and short name
        $conversation = $this->findOrCreatePrivateConversation($senderId, $senderType, $userId, $receiverType);

        $otherParticipant = $this->findOtherParticipant($conversation, $senderId, $senderType);
        $otherUserName = $otherParticipant
            ? $this->getParticipantDisplayName($otherParticipant)
            : 'User #' . $userId;

        return view('messaging::chat.index', compact('conversation', 'otherParticipant', 'otherUserName'));
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
            $adminType = \App\Models\Admin::class;
            $adminTypeShort = strtolower(class_basename($adminType));
            $hasAdmin = $conversation->participants()->whereIn('participant_type', [$adminType, $adminTypeShort])->exists();
            if (!$hasAdmin) {
                abort(403, 'You can only view conversations with the admin');
            }
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

        $otherParticipant = $this->findOtherParticipant($conversation, $senderId, $senderType);
        $otherUserName = $otherParticipant
            ? $this->getParticipantDisplayName($otherParticipant)
            : 'User';

        return view('messaging::chat.index', compact('conversation', 'otherParticipant', 'otherUserName'));
    }
}
