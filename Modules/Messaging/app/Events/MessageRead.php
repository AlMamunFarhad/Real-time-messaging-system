<?php

namespace Modules\Messaging\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $conversationId,
        public $receiverId,
        public string $receiverType
    ) {}

    public function broadcastOn(): array
    {
        // Broadcast to the receiver's private channel
        $type = strtolower(class_basename($this->receiverType));
        return [
            new PrivateChannel("messaging.{$type}.{$this->receiverId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.read';
    }
}
