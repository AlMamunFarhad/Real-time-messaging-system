<?php

namespace Modules\Messaging\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\Message;
use Modules\Messaging\Helpers\AuthParticipant;

class MessagingController extends Controller
{
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

        $conversations = Conversation::whereHas('participants', function ($q) use ($userId, $userType, $userTypeShort) {
            $q->where('participant_id', $userId)
                ->whereIn('participant_type', [$userType, $userTypeShort]);
        })
            ->with(['participants'])
            ->with(['messages' => function ($q) {
                $q->orderBy('created_at', 'desc')->limit(1);
            }])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        $unreadCount = 0;
        foreach ($conversations as $conversation) {
            $unread = $conversation->messages()
                ->whereNull('read_at')
                ->where('sender_id', '!=', $userId)
                ->where('sender_type', '!=', $userType)
                ->count();
            $unreadCount += $unread;
            $conversation->unread_count = $unread;

            $conversation->last_message = $conversation->messages->first();
        }

        return response()->json([
            'conversations' => $conversations,
            'unread_count' => $unreadCount
        ]);
    }
}
