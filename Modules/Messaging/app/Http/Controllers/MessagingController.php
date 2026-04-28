<?php

namespace Modules\Messaging\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;
use Modules\Messaging\Models\ConversationParticipant;
use Modules\Messaging\Helpers\AuthParticipant;
use Modules\Messaging\Services\ConversationService;

class MessagingController extends Controller
{
    public function __construct(
        protected ConversationService $conversationService
    ) {}



    public function index()
    {
        return view('messaging::index');
    }

    public function create()
    {
        return view('messaging::create');
    }

    public function store(Request $request) {}

    public function show($id)
    {
        return view('messaging::show');
    }

    public function edit($id)
    {
        return view('messaging::edit');
    }

    public function update(Request $request, $id) {}

    public function destroy($id) {}

    public function messages($conversationId)
    {
        $userId = AuthParticipant::id();
        $userType = AuthParticipant::type();

        if (!$userId || !$userType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userTypeShort = strtolower(class_basename($userType));

        $conversation = Conversation::where('id', $conversationId)
            ->whereHas('participants', function ($q) use ($userId, $userType, $userTypeShort) {
                $q->where('participant_id', $userId)
                    ->whereIn('participant_type', [$userType, $userTypeShort])
                    ->whereNull('left_at');
            })
            ->first();

        if (!$conversation) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = Message::where('conversation_id', $conversationId)
            ->latest('created_at')
            ->limit(50)
            ->get()
            ->sortBy('created_at')
            ->values()
            ->map(function ($msg) {
                $sender = $msg->sender;
                $msg->sender_name = $sender ? ($sender->name ?? 'Unknown') : 'Unknown';
                if ($msg->file_path) {
                    $msg->file_url = asset($msg->file_path);
                }
                $conversation = $msg->conversation()->first(['id', 'name', 'is_group']);
                $msg->conversation_meta = [
                    'id' => $conversation?->id,
                    'name' => $conversation?->name,
                    'is_group' => (bool) ($conversation?->is_group),
                ];
                return $msg;
            });

        return response()->json(['messages' => $messages]);
    }

    public function messagesWeb($conversationId)
    {
        $userId = AuthParticipant::id();
        $userType = AuthParticipant::type();

        if (!$userId || !$userType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userTypeShort = strtolower(class_basename($userType));

        $conversation = Conversation::where('id', $conversationId)
            ->whereHas('participants', function ($q) use ($userId, $userType, $userTypeShort) {
                $q->where('participant_id', $userId)
                    ->whereIn('participant_type', [$userType, $userTypeShort])
                    ->whereNull('left_at');
            })
            ->first();

        if (!$conversation) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = Message::where('conversation_id', $conversationId)
            ->latest('created_at')
            ->limit(50)
            ->get()
            ->sortBy('created_at')
            ->values()
            ->map(function ($msg) {
                $sender = $msg->sender;
                $msg->sender_name = $sender ? ($sender->name ?? 'Unknown') : 'Unknown';
                if ($msg->file_path) {
                    $msg->file_url = asset($msg->file_path);
                }
                $conversation = $msg->conversation()->first(['id', 'name', 'is_group']);
                $msg->conversation_meta = [
                    'id' => $conversation?->id,
                    'name' => $conversation?->name,
                    'is_group' => (bool) ($conversation?->is_group),
                ];
                return $msg;
            });

        return response()->json(['messages' => $messages]);
    }

    public function getConversations(Request $request)
    {
        $userId = AuthParticipant::id();
        $userType = AuthParticipant::type();

        if (!$userId || !$userType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userTypeShort = strtolower(class_basename($userType));

        $query = $this->conversationService->conversationQueryForParticipant($userId, $userType);

        if ($userTypeShort === 'user') {
            $query->where(function ($conversationQuery) {
                $adminType = \App\Models\Admin::class;
                $adminTypeShort = strtolower(class_basename($adminType));
                $conversationQuery->where('is_group', true)
                    ->orWhereHas('participants', function ($q) use ($adminType, $adminTypeShort) {
                        $q->whereIn('participant_type', [$adminType, $adminTypeShort])
                            ->whereNull('left_at');                                                                                         
                    });
            });
        }

        $allConversations = $query
            ->with(['participants' => function ($q) {
                $q->whereNull('left_at');
            }])
            ->with('lastMessage')
            ->orderBy('updated_at', 'desc')
            ->get();

        $unreadCount = 0;
        foreach ($allConversations as $conversation) {
            $unread = $this->conversationService->getUnreadCountForConversation($conversation, $userId, $userType);
            $unreadCount += $unread;
            $conversation->unread_count = $unread;

            $conversation->last_message = $conversation->lastMessage;

            $otherParticipant = null;

            foreach ($conversation->participants as $participant) {
                $participant->participant_name = $this->conversationService->getParticipantDisplayName(
                    (int) $participant->participant_id,
                    $participant->participant_type
                );

                $participantType = $this->conversationService->resolveParticipantType($participant->participant_type ?? null);
                $participantTypeShort = strtolower(class_basename($participantType ?? ''));
                $isCurrentUser = (int) $participant->participant_id === (int) $userId
                    && ($participantType === $userType || $participantTypeShort === $userTypeShort);

                if (!$isCurrentUser && !$otherParticipant) {
                    $otherParticipant = $participant;
                }
            }

            $currentUserParticipant = $conversation->participants->first(function ($p) use ($userId, $userType, $userTypeShort) {
                $typeFull = 'App\\Models\\' . ucfirst($userTypeShort);
                return (int) $p->participant_id === (int) $userId
                    && in_array($p->participant_type, [$userType, $userTypeShort, $typeFull]);
            });
            $conversation->is_pinned = (bool) ($currentUserParticipant?->is_pinned ?? false);

            $conversation->other_participant_id = $otherParticipant?->participant_id;
            $conversation->other_participant_type = $otherParticipant
                ? strtolower(class_basename($this->conversationService->resolveParticipantType($otherParticipant->participant_type ?? null) ?? ''))
                : null;
            $conversation->other_participant_name = $conversation->is_group
                ? ($conversation->name ?: 'Untitled Group')
                : ($otherParticipant ? $otherParticipant->participant_name : 'User');
            $conversation->title = $conversation->is_group
                ? ($conversation->name ?: 'Untitled Group')
                : $conversation->other_participant_name;
            $conversation->is_online = $conversation->is_group ? false : ($otherParticipant ? \Illuminate\Support\Facades\Cache::has("online_{$conversation->other_participant_type}_{$conversation->other_participant_id}") : false);
            $conversation->members_count = $conversation->participants->count();
            $conversation->can_manage = $conversation->is_group
                ? $this->conversationService->canManageGroup($conversation, $userId, $userType)
                : false;
            $conversation->preview_text = $this->conversationService->buildMessagePreview(
                $conversation->last_message?->body,
                $conversation->last_message?->file_path,
                $conversation->is_group ? 'Start a discussion' : 'No message yet'
            );
        }

        $conversations = $allConversations->sort(function ($a, $b) {
            if ($a->is_pinned === $b->is_pinned) {
                return $b->updated_at <=> $a->updated_at;
            }
            return $b->is_pinned <=> $a->is_pinned;
        })->values();

        return response()->json([
            'conversations' => $conversations,
            'unread_count' => $unreadCount
        ]);
    }

    public function notificationsFeed(Request $request)
    {
        $userId = AuthParticipant::id();
        $userType = AuthParticipant::type();

        if (!$userId || !$userType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userTypeShort = strtolower(class_basename($userType));
        $since = $request->query('since');

        $conversationIds = ConversationParticipant::query()
            ->where('participant_id', $userId)
            ->whereIn('participant_type', [$userType, $userTypeShort])
            ->whereNull('left_at')
            ->pluck('conversation_id');

        $messages = Message::query()
            ->with('conversation', 'sender')
            ->whereIn('conversation_id', $conversationIds)
            ->when($since, fn ($query) => $query->where('created_at', '>', $since))
            ->where(function ($query) use ($userId, $userType, $userTypeShort) {
                $query->where('sender_id', '!=', $userId)
                    ->orWhereNotIn('sender_type', [$userType, $userTypeShort]);
            })
            ->orderBy('created_at', 'asc')
            ->limit(20)
            ->get()
            ->map(function (Message $message) {
                $sender = $message->sender;
                $conversation = $message->conversation;

                return [
                    'id' => $message->id,
                    'conversation_id' => $message->conversation_id,
                    'conversation_title' => $conversation?->is_group
                        ? ($conversation?->name ?: 'Group Chat')
                        : ($sender?->name ?? $sender?->email ?? 'Direct Chat'),
                    'sender_id' => $message->sender_id,
                    'sender_type' => $message->sender_type,
                    'sender_name' => $sender?->name ?? ($sender?->email ?? 'Unknown'),
                    'body' => $message->body,
                    'type' => $message->type,
                    'file_name' => $message->file_path ? basename($message->file_path) : null,
                    'created_at' => optional($message->created_at)->toISOString(),
                ];
            });

        return response()->json([
            'messages' => $messages,
            'unread_count' => $this->conversationService->getUnreadCountForParticipant($userId, $userType),
            'server_time' => now()->toISOString(),
        ]);
    }
}
