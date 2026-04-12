<?php

namespace Modules\Messaging\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Messaging\Models\Message;
use Illuminate\Support\Facades\Log;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
        Log::info('MessageSent event created', [
            'message_id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id
        ]);
    }
    
    // broadcast on private channel
    public function broadcastOn()
    {
        $channel = 'conversation.' . $this->message->conversation_id;
        Log::info('MessageSent broadcasting on channel: ' . $channel);
        
        return new PrivateChannel($channel);
    }

    // event name
    public function broadcastAs()
    {
        return 'message.sent';
    }
    
    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'sender_type' => $this->message->sender_type,
            'body' => $this->message->body,
            'created_at' => $this->message->created_at->toISOString(),
        ];
    }
}
