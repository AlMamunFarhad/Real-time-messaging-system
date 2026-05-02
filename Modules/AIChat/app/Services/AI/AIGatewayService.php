<?php

namespace Modules\AIChat\app\Services\AI;

use Illuminate\Support\Str;
use Modules\AIChat\app\Services\AI\Providers\GroqProvider;
use Modules\AIChat\app\Services\AI\Providers\GeminiProvider;

class AIGatewayService
{
    protected $provider;

    public function __construct()
    {
        $this->provider = $this->resolveProvider();
    }

    /**
     * Resolve the AI provider based on config.
     */
    protected function resolveProvider()
    {
        $default = config('aichat.default_provider', 'groq');
        $config = config("aichat.providers.{$default}");

        switch ($default) {
            case 'groq':
                return new GroqProvider($config);
            case 'gemini':
                return new GeminiProvider($config);
            default:
                throw new \Exception("AI Provider [{$default}] not supported.");
        }
    }


    /**
     * Should the AI respond to this message?
     */
    public function shouldRespond(string $message): bool
    {
        if (!config('aichat.enabled')) return false;

        $message = Str::lower(trim($message));

        // Skip if too short
        if (Str::length($message) < config('aichat.rules.min_length')) return false;

        // Skip greetings/simple keywords
        $ignore = config('aichat.rules.ignore_keywords', []);
        if (in_array($message, $ignore)) return false;

        return true;
    }

    /**
     * Get AI response.
     */
    public function getAIResponse(string $prompt, array $context = []): ?string
    {
        try {
            return $this->provider->getResponse($prompt, $context);
        } catch (\Exception $e) {
            \Log::error("AI Gateway Error: " . $e->getMessage());
            return "AI is temporarily unavailable.";
        }
    }
}
