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
        $request->validate([
            'receiver_id' => 'required',
            'receiver_type' => 'required',
            'message' => 'required'
        ]);

        $senderId = AuthParticipant::id();
        $senderType = AuthParticipant::type();

        // Step 1: conversation find or create
        $conversation = Conversation::whereHas('participants', function ($q) use ($senderId, $senderType) {
            $q->where('participant_id', $senderId)
                ->where('participant_type', $senderType);
        })
            ->whereHas('participants', function ($q) use ($request) {
                $q->where('participant_id', $request->receiver_id)
                    ->where('participant_type', $request->receiver_type);
            })
            ->first();

        // if conversation not found then create new conversation and add participants
        if (!$conversation) {
            $conversation = Conversation::create();

            // sender add
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'participant_id' => $senderId,
                'participant_type' => $senderType,
                'joined_at' => now(),
            ]);

            // receiver add
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'participant_id' => $request->receiver_id,
                'participant_type' => $request->receiver_type,
                'joined_at' => now(),
            ]);
        }

        // Step 2: message insert
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'sender_type' => $senderType,
            'body' => $request->message,
        ]);
        event(new MessageSent($message));

        return response()->json([
            'message' => 'Message sent',
            'data' => $message
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

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
