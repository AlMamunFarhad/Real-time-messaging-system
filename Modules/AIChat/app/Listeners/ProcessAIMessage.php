<?php

namespace Modules\AIChat\Listeners;

use Modules\Messaging\Events\MessageSent;
use Modules\AIChat\Services\AI\AIGatewayService;
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
        // Check if ai_chat_bot feature is enabled in messaging config
        if (!function_exists('messaging_feature') || !messaging_feature('ai_chat_bot', true)) {
            \Log::info("AI Chat Bot is disabled via config/features.php. Skipping AI response.");
            return;
        }

        $message = $event->message;
        \Log::info("ProcessAIMessage listener triggered for message: " . $message->id);

        // Don't respond to AI messages (avoid loop)
        $aiUser = $this->getAIUser();
        if ($message->sender_type === \App\Models\User::class && $message->sender_id === $aiUser->id) {
            \Log::info("Skipping AI message.");
            return;
        }

        // HUMAN HANDOVER LOGIC
        // Check if any admin is currently online
        $isAnyAdminOnline = false;
        foreach (\App\Models\Admin::all() as $admin) {
            if (\Illuminate\Support\Facades\Cache::has('online_admin_' . $admin->id)) {
                $isAnyAdminOnline = true;
                break;
            }
        }

        // If an Admin is online AND has ever sent a message in this conversation, disable the AI bot.
        // If all admins log out, the AI ignores previous admin messages and resumes responding.
        if ($isAnyAdminOnline) {
            $hasAdminReplied = \Modules\Messaging\Models\Message::where('conversation_id', $message->conversation_id)
                ->where('sender_type', \App\Models\Admin::class)
                ->exists();

            if ($hasAdminReplied) {
                \Log::info("Human agent (Admin) is online and has joined conversation {$message->conversation_id}. AI is disabled.");
                return;
            }
        } else {
            \Log::info("All Admins are offline. AI will handle conversation {$message->conversation_id}.");
        }

        // Check decision engine
        if (!$this->aiGateway->shouldRespond($message->body)) {
            \Log::info("Decision engine decided NOT to respond.");
            return;
        }

        \Log::info("Decision engine decided TO respond. Getting AI response...");

        // Get context (Exclude current message since it's passed as prompt)
        $aiUser = $this->getAIUser();
        $context = Message::where('conversation_id', $message->conversation_id)
            ->where('id', '!=', $message->id)
            ->latest()
            ->take(config('aichat.settings.context_limit', 10))
            ->get()
            ->reverse()
            ->map(function ($msg) use ($aiUser) {
                return [
                    'is_ai' => ($msg->sender_type === \App\Models\User::class && $msg->sender_id === $aiUser->id),
                    'body' => $msg->body,
                ];
            })
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
