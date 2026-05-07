<?php

namespace Modules\AIChat\Services\AI\Providers;

use Illuminate\Support\Facades\Http;
use Modules\AIChat\Services\AI\Contracts\AIProviderInterface;

class DeepseekProvider implements AIProviderInterface
{
    protected $apiKey;
    protected $model;
    protected $endpoint;

    public function __construct(array $config)
    {
        $this->apiKey = $config['key'];
        $this->model = $config['model'];
        $this->endpoint = $config['endpoint'];
    }

    public function getResponse(string $prompt, array $context = []): ?string
    {
        if (empty($this->apiKey)) {
            \Log::error("Deepseek AI Error: API Key is missing.");
            return null;
        }

        $messages = $this->buildMessages($prompt, $context);

        $response = Http::withToken($this->apiKey)
            ->post($this->endpoint, [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => config('aichat.settings.max_tokens', 500),
                'temperature' => config('aichat.settings.temperature', 0.7),
            ]);

        if ($response->failed()) {
            \Log::error("Deepseek AI Error: " . $response->body());
            return null;
        }

        return $response->json('choices.0.message.content');
    }

    protected function buildMessages(string $prompt, array $context): array
    {
        $messages = [
            ['role' => 'system', 'content' => config('aichat.settings.system_prompt')]
        ];

        foreach ($context as $msg) {
            $role = (!empty($msg['is_ai'])) ? 'assistant' : 'user';
            $messages[] = ['role' => $role, 'content' => $msg['body']];
        }

        $messages[] = ['role' => 'user', 'content' => $prompt];

        return $messages;
    }
}
