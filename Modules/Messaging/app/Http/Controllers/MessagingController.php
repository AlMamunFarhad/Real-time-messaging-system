<?php

namespace Modules\Messaging\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;
use Modules\Messaging\Helpers\AuthParticipant;
use Modules\Messaging\Services\ConversationService;

class MessagingController extends Controller
{
    public function __construct(
        protected ConversationService $conversationService
    ) {}

    protected function getUnreadCountForConversation(Conversation $conversation, int $userId, string $userType, string $userTypeShort): int
    {
        $participant = $conversation->participants->first(function ($participant) use ($userId, $userType, $userTypeShort) {
            return (int) $participant->participant_id === (int) $userId
                && in_array($participant->participant_type, [$userType, $userTypeShort], true);
        });

        if (!$participant) {
            return 0;
        }

        $lastReadAt = $participant->last_read_at;

        return $conversation->messages()
            ->when($lastReadAt, fn ($query) => $query->where('created_at', '>', $lastReadAt))
            ->where(function ($query) use ($userId, $userType, $userTypeShort) {
                $query->where('sender_id', '!=', $userId)
                    ->orWhereNotIn('sender_type', [$userType, $userTypeShort]);
            })
            ->count();
    }

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
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get()
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
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get()
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
            ->with(['messages' => function ($q) {
                $q->orderBy('created_at', 'desc')->limit(1);
            }])
            ->orderBy('updated_at', 'desc')
            ->get();

        $unreadCount = 0;
        foreach ($allConversations as $conversation) {
            $unread = $this->getUnreadCountForConversation($conversation, $userId, $userType, $userTypeShort);
            $unreadCount += $unread;
            $conversation->unread_count = $unread;

            $conversation->last_message = $conversation->messages->first();

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
            $conversation->members_count = $conversation->participants->count();
            $conversation->can_manage = $conversation->is_group
                ? $this->conversationService->canManageGroup($conversation, $userId, $userType)
                : false;
        }

        $conversations = $allConversations->values();

        return response()->json([
            'conversations' => $conversations,
            'unread_count' => $unreadCount
        ]);
    }
}
