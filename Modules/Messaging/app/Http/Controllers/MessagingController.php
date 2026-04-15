<?php

namespace Modules\Messaging\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;
use Modules\Messaging\Helpers\AuthParticipant;

class MessagingController extends Controller
{
    protected function resolveParticipantType(?string $type): ?string
    {
        return match ($type) {
            'admin' => \App\Models\Admin::class,
            'user' => \App\Models\User::class,
            default => $type,
        };
    }

    protected function getParticipantDisplayName($participant): string
    {
        $participantType = $this->resolveParticipantType($participant->participant_type ?? null);

        if (!$participantType || !class_exists($participantType)) {
            return 'User #' . $participant->participant_id;
        }

        $model = $participantType::find($participant->participant_id);

        return $model?->name ?? ($model?->email ?? 'User #' . $participant->participant_id);
    }

    protected function getUnreadCountForConversation(Conversation $conversation, int $userId, string $userType, string $userTypeShort): int
    {
        // Use the same matching logic as the MessageIcon component
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
                    ->whereIn('participant_type', [$userType, $userTypeShort]);
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
                    ->whereIn('participant_type', [$userType, $userTypeShort]);
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

        $query = Conversation::whereHas('participants', function ($q) use ($userId, $userType, $userTypeShort) {
            $q->where('participant_id', $userId)
                ->whereIn('participant_type', [$userType, $userTypeShort]);
        });

        // Regular users can only see conversations with admin
        if ($userTypeShort === 'user') {
            $query->whereHas('participants', function ($q) {
                $adminType = \App\Models\Admin::class;
                $adminTypeShort = strtolower(class_basename($adminType));
                $q->whereIn('participant_type', [$adminType, $adminTypeShort]);
            });
        }

        $allConversations = $query
            ->with(['participants'])
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
                $participant->participant_name = $this->getParticipantDisplayName($participant);

                $participantType = $this->resolveParticipantType($participant->participant_type ?? null);
                $participantTypeShort = strtolower(class_basename($participantType ?? ''));
                $isCurrentUser = (int) $participant->participant_id === (int) $userId
                    && ($participantType === $userType || $participantTypeShort === $userTypeShort);

                if (!$isCurrentUser && !$otherParticipant) {
                    $otherParticipant = $participant;
                }
            }

            $conversation->other_participant_id = $otherParticipant?->participant_id;
            $conversation->other_participant_type = $otherParticipant
                ? strtolower(class_basename($this->resolveParticipantType($otherParticipant->participant_type ?? null) ?? ''))
                : null;
            $conversation->other_participant_name = $otherParticipant
                ? $otherParticipant->participant_name
                : 'User';
        }

        $conversations = $allConversations->values();

        return response()->json([
            'conversations' => $conversations,
            'unread_count' => $unreadCount
        ]);
    }
}
