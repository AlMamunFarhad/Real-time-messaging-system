<?php

namespace Modules\Messaging\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Messaging\Helpers\AuthParticipant;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\ConversationParticipant;
use Modules\Messaging\Models\Message;
use Modules\Messaging\Events\MessageSent;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('messaging::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('messaging::create');
    }

    public function send(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:1000',
                'conversation_id' => 'required|integer'
            ]);

            $senderId = AuthParticipant::id();
            $senderType = AuthParticipant::type();

            \Illuminate\Support\Facades\Log::info('Auth check', [
                'guard' => AuthParticipant::guard(),
                'id' => $senderId,
                'type' => $senderType
            ]);

            if (!$senderId || !$senderType) {
                return response()->json(['error' => 'Unauthorized: No authenticated user', 'debug' => ['guard' => AuthParticipant::guard()]], 401);
            }

            $conversationId = $request->conversation_id;
            $senderTypeShort = strtolower(class_basename($senderType));

            \Illuminate\Support\Facades\Log::info('Sending message', [
                'sender_id' => $senderId,
                'sender_type' => $senderType,
                'conversation_id' => $conversationId
            ]);

            $conversation = Conversation::where('id', $conversationId)
                ->whereHas('participants', function ($q) use ($senderId, $senderType, $senderTypeShort) {
                    $q->where('participant_id', $senderId)
                        ->whereIn('participant_type', [$senderType, $senderTypeShort]);
                })
                ->first();

            if (!$conversation) {
                return response()->json(['error' => 'Conversation not found or you are not a participant'], 404);
            }

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $senderId,
                'sender_type' => $senderType,
                'body' => $request->message,
            ]);

            $conversation->touch();

            // Add sender name to message for display
            $senderModel = AuthParticipant::model();
            $senderName = $senderModel ? $senderModel->name : 'Unknown';

            // Dispatch broadcast event synchronously to ensure it works
            try {
                \Illuminate\Support\Facades\Log::info('Attempting to broadcast message', [
                    'message_id' => $message->id,
                    'conversation_id' => $message->conversation_id
                ]);
                
                event(new MessageSent($message));
                
                \Illuminate\Support\Facades\Log::info('Event dispatched successfully');
            } catch (\Exception $broadcastException) {
                \Illuminate\Support\Facades\Log::error('Broadcast failed: ' . $broadcastException->getMessage(), [
                    'exception' => $broadcastException
                ]);
            }

            // Return message with sender_name included
            return response()->json([
                'id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'sender_id' => $message->sender_id,
                'sender_type' => $message->sender_type,
                'sender_name' => $senderName,
                'body' => $message->body,
                'created_at' => $message->created_at,
                'read_at' => $message->read_at,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Send message error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function markRead(Request $request)
    {
        $conversationId = $request->conversation_id;

        if (!$conversationId) {
            return response()->json(['error' => 'Conversation ID required'], 400);
        }

        $userId = AuthParticipant::id();
        $userType = AuthParticipant::type();

        if (!$userId || !$userType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $updated = Message::where('conversation_id', $conversationId)
            ->whereNull('read_at')
            ->where('sender_id', '!=', $userId)
            ->where('sender_type', '!=', $userType)
            ->update(['read_at' => now()]);

        return response()->json(['marked_read' => $updated]);
    }

    // public function send(Request $request)
    // {
    //     $request->validate([
    //         'receiver_id' => 'required',
    //         'receiver_type' => 'required',
    //         'message' => 'required'
    //     ]);

    //     $senderId = AuthParticipant::id();
    //     $senderType = AuthParticipant::type();

    //     // Step 1: conversation find or create
    //     $conversation = Conversation::whereHas('participants', function ($q) use ($senderId, $senderType) {
    //         $q->where('participant_id', $senderId)
    //             ->where('participant_type', $senderType);
    //     })
    //         ->whereHas('participants', function ($q) use ($request) {
    //             $q->where('participant_id', $request->receiver_id)
    //                 ->where('participant_type', $request->receiver_type);
    //         })
    //         ->first();

    //     // if conversation not found then create new conversation and add participants
    //     if (!$conversation) {
    //         $conversation = Conversation::create();

    //         // sender add
    //         ConversationParticipant::create([
    //             'conversation_id' => $conversation->id,
    //             'participant_id' => $senderId,
    //             'participant_type' => $senderType,
    //             'joined_at' => now(),
    //         ]);

    //         // receiver add
    //         ConversationParticipant::create([
    //             'conversation_id' => $conversation->id,
    //             'participant_id' => $request->receiver_id,
    //             'participant_type' => $request->receiver_type,
    //             'joined_at' => now(),
    //         ]);
    //     }

    //     // Step 2: message insert
    //     $message = Message::create([
    //         'conversation_id' => $conversation->id,
    //         'sender_id' => $senderId,
    //         'sender_type' => $senderType,
    //         'body' => $request->message,
    //     ]);
    //     event(new MessageSent($message));

    //     return response()->json([
    //         'message' => 'Message sent',
    //         'data' => $message
    //     ]);
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $user = auth()->user();
        // $message = Message::create([
        //     'conversation_id' => $request->conversation_id,
        //     'sender_id' => $user->id,
        //     'sender_type' => get_class($user),
        //     'body' => $request->message,
        // ]);

        // // real-time trigger
        // event(new MessageSent($message));

        // return response()->json($message);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('messaging::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('messaging::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
