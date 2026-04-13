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
use Modules\Messaging\Models\ConversationParticipant;
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
        $channels = [
            new PrivateChannel('conversation.' . $this->message->conversation_id),
        ];

        $senderTypeShort = strtolower(class_basename($this->message->sender_type));

        $participants = ConversationParticipant::where('conversation_id', $this->message->conversation_id)
            ->get(['participant_id', 'participant_type']);

        foreach ($participants as $participant) {
            $participantTypeShort = strtolower(class_basename($participant->participant_type ?? ''));

            $isSender = (int) $participant->participant_id === (int) $this->message->sender_id
                && ($participant->participant_type === $this->message->sender_type || $participantTypeShort === $senderTypeShort);

            if ($isSender) {
                continue;
            }

            $resolvedTypeShort = match ($participant->participant_type) {
                'App\\Models\\Admin', 'admin' => 'admin',
                'App\\Models\\User', 'user' => 'user',
                default => $participantTypeShort,
            };

            $channels[] = new PrivateChannel('user.' . $resolvedTypeShort . '.' . $participant->participant_id);
        }

        Log::info('MessageSent broadcasting on channels', [
            'conversation_id' => $this->message->conversation_id,
            'channels' => array_map(fn ($channel) => $channel->name, $channels),
        ]);

        return $channels;
    }

    // event name
    public function broadcastAs()
    {
        return 'message.sent';
    }
    
    public function broadcastWith()
    {
        $sender = $this->message->sender;

        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'sender_type' => $this->message->sender_type,
            'sender_name' => $sender?->name ?? ($sender?->email ?? 'Unknown'),
            'body' => $this->message->body,
            'created_at' => $this->message->created_at->toISOString(),
        ];
    }
}
