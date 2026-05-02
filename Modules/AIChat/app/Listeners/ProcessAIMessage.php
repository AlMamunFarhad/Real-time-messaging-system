<?php

namespace Modules\AIChat\app\Listeners;

use Modules\Messaging\Events\MessageSent;
use Modules\AIChat\app\Services\AI\AIGatewayService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Modules\Messaging\Models\Message;
use Modules\Messaging\Events\MessageSent as MessageSentEvent;

class ProcessAIMessage
{
    // use InteractsWithQueue;


    protected $aiGateway;

    public function __construct(AIGatewayService $aiGateway)
    {
        $this->aiGateway = $aiGateway;
    }

    public function handle(MessageSent $event)
    {
        $message = $event->message;
        \Log::info("ProcessAIMessage listener triggered for message: " . $message->id);

        // Don't respond to AI messages (avoid loop)
        $aiUser = $this->getAIUser();
        if ($message->sender_type === \App\Models\User::class && $message->sender_id === $aiUser->id) {
            \Log::info("Skipping AI message.");
            return;
        }

        // Check decision engine
        if (!$this->aiGateway->shouldRespond($message->body)) {
            \Log::info("Decision engine decided NOT to respond.");
            return;
        }

        \Log::info("Decision engine decided TO respond. Getting AI response...");

        // Get context
        $context = Message::where('conversation_id', $message->conversation_id)
            ->latest()
            ->take(config('aichat.settings.context_limit', 10))
            ->get()
            ->reverse()
            ->toArray();

        // Get AI Response
        $response = $this->aiGateway->getAIResponse($message->body, $context);

        if ($response) {
            $this->saveAndBroadcastAIResponse($message->conversation_id, $response);
        }
    }

    protected function getAIUser()
    {
        return \App\Models\User::firstOrCreate(
            ['email' => 'ai@assistant.local'],
            [
                'name' => config('aichat.settings.ai_name', 'AI Assistant'),
                'password' => bcrypt(\Illuminate\Support\Str::random(16)),
            ]
        );
    }

    protected function saveAndBroadcastAIResponse($conversationId, $text)
    {
        $aiUser = $this->getAIUser();

        $aiMessage = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $aiUser->id,
            'sender_type' => \App\Models\User::class,
            'body' => $text,
            'type' => 'text',
        ]);

        // Broadcast to realtime channel
        broadcast(new MessageSentEvent($aiMessage));
    }
}
