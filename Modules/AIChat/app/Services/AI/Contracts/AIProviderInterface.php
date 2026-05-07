<?php

namespace Modules\AIChat\Services\AI\Contracts;

interface AIProviderInterface
{
    /**
     * Send message to AI provider and get response.
     *
     * @param string $prompt
     * @param array $context
     * @return string|null
     */
    public function getResponse(string $prompt, array $context = []): ?string;
}
