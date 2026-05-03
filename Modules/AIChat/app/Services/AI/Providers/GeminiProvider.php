<?php

namespace Modules\AIChat\app\Services\AI\Providers;

use Illuminate\Support\Facades\Http;
use Modules\AIChat\app\Services\AI\Contracts\AIProviderInterface;

class GeminiProvider implements AIProviderInterface
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
        $contents = $this->buildContents($prompt, $context);

        $url = $this->endpoint . $this->model . ':generateContent?key=' . $this->apiKey;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => config('aichat.settings.temperature', 0.7),
                'maxOutputTokens' => config('aichat.settings.max_tokens', 500),
            ],
            'system_instruction' => [
                'parts' => [
                    ['text' => config('aichat.settings.system_prompt')]
                ]
            ]
        ]);

        if ($response->failed()) {
            \Log::error("Gemini AI Error: " . $response->body());
            return null;
        }

        return $response->json('candidates.0.content.parts.0.text');
    }

    protected function buildContents(string $prompt, array $context): array
    {
        $contents = [];

        foreach ($context as $msg) {
            $role = (!empty($msg['is_ai'])) ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $msg['body']]]
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $prompt]]
        ];

        return $contents;
    }
}
