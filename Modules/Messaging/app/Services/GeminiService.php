<?php

namespace Modules\Messaging\Services;

use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Collection;

class GeminiService
{
    /**
     * Generate a summary of chat messages.
     *
     * @param Collection $messages
     * @param bool $isGroup
     * @return string
     */
    public function generateSummary(Collection $messages, bool $isGroup = false): string
    {
        if ($messages->isEmpty()) {
            return "No messages to summarize.";
        }

        $formattedMessages = $messages->map(function ($msg) {
            $sender = $msg->sender_name ?? 'Unknown';
            $body = $msg->body ?? ($msg->type === 'file' ? '[File Attachment]' : '');
            return "$sender: $body";
        })->implode("\n");

        $prompt = "Please provide a highly structured and professional summary of the following " . ($isGroup ? "group " : "direct ") . "chat conversation.\n" .
                  "Use the following format:\n" .
                  "1. **Overview**: A 1-2 sentence summary of the conversation's purpose.\n" .
                  "2. **Key Highlights**: Use bullet points for the main topics discussed.\n" .
                  "3. **Decisions & Action Items**: List any concrete conclusions or tasks mentioned.\n" .
                  "Keep it concise, professional, and in English.\n" .
                  "### Chat History:\n" . $formattedMessages;

        try {
            $result = Gemini::generativeModel('gemini-flash-latest')->generateContent($prompt);
            return $result->text();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            \Illuminate\Support\Facades\Log::error('Gemini Summary Error: ' . $errorMessage);
            
            if (str_contains(strtolower($errorMessage), 'quota') || str_contains(strtolower($errorMessage), 'limit')) {
                return "Failed to generate summary: AI API Quota exceeded. Please try again in a few minutes or check your Gemini API plan.";
            }
            
            return "Failed to generate summary: " . $errorMessage;
        }
    }
}
