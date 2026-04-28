<?php

namespace Modules\Messaging\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Messaging\Helpers\AuthParticipant;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Services\GeminiService;
use Modules\Messaging\Services\ConversationService;

class SummaryController extends Controller
{
    public function __construct(
        protected GeminiService $geminiService,
        protected ConversationService $conversationService
    ) {}

    public function generate(Request $request, $conversationId)
    {
        abort_unless(messaging_feature('ai_summary'), 403, 'Messaging feature disabled.');

        $participantId = AuthParticipant::id();
        $participantType = AuthParticipant::type();

        if (!$participantId || !$participantType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $conversation = $this->conversationService->getConversationForParticipant(
            $conversationId,
            $participantId,
            $participantType
        );

        if (!$conversation) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        $messages = $conversation->messages()
            ->with(['sender'])
            ->oldest()
            ->get()
            ->map(function ($message) {
                // Formatting sender name for the AI
                $senderName = 'Unknown';
                if ($message->sender) {
                    $senderName = $message->sender->name ?? $message->sender->email ?? 'User';
                }
                $message->sender_name = $senderName;
                return $message;
            });

        if ($messages->isEmpty()) {
            return response()->json(['error' => 'No messages to summarize'], 400);
        }

        // Generate summary using AI
        $summary = $this->geminiService->generateSummary($messages, $conversation->isGroup());

        return response()->json([
            'summary' => $summary,
            'title' => $conversation->isGroup() ? $conversation->name : 'Direct Chat Summary',
            'generated_at' => now()->format('F j, Y, g:i a'),
            'type' => $conversation->isGroup() ? 'Group Chat' : 'One-to-One Chat',
            'conversation_name' => $conversation->isGroup() ? $conversation->name : 'Private Chat'
        ]);
    }
}
