<?php

namespace Modules\Messaging\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessagesRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversationId;
    public $readerId;
    public $readerType;
    public $readAt;

    public function __construct($conversationId, $readerId, $readerType, $readAt)
    {
        $this->conversationId = $conversationId;
        $this->readerId = $readerId;
        $this->readerType = $readerType;
        $this->readAt = $readAt;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('conversation.' . $this->conversationId);
    }

    public function broadcastAs()
    {
        return 'messages.read';
    }

    public function broadcastWith()
    {
        return [
            'conversation_id' => $this->conversationId,
            'reader_id' => $this->readerId,
            'reader_type' => $this->readerType,
            'read_at' => $this->readAt,
        ];
    }
}
