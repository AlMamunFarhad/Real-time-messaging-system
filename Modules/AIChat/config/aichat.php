<?php

return [
    'enabled' => true,
    
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    | Options: groq, huggingface, together, openai
    */
    'default_provider' => 'gemini',

    'providers' => [
        'groq' => [
            'key' => env('GROQ_API_KEY', ''),
            'model' => env('GROQ_MODEL', 'mixtral-8x7b-32768'),
            'endpoint' => 'https://api.groq.com/openai/v1/chat/completions',
        ],
        'gemini' => [
            'key' => env('GEMINI_API_KEY', ''),
            'model' => env('GEMINI_MODEL', 'gemini-flash-latest'),
            'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/',
        ],
        'huggingface' => [
            'key' => env('HUGGINGFACE_API_KEY', ''),
            'model' => env('HUGGINGFACE_MODEL', 'mistralai/Mistral-7B-Instruct-v0.2'),
            'endpoint' => 'https://api-inference.huggingface.co/models/',
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | AI Behavior Settings
    |--------------------------------------------------------------------------
    */
    'settings' => [
        'max_tokens' => 500,
        'temperature' => 0.7,
        'context_limit' => 10, // Last N messages
        'ai_name' => 'AI Assistant',
        'system_prompt' => 'You are a helpful chat assistant inside a messaging app. Keep replies concise and professional. Do not use Markdown formatting (like bold, italics, asterisks, or hashes). Provide your answers in clean, plain text.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Decision Engine Rules
    |--------------------------------------------------------------------------
    */
    'rules' => [
        'min_length' => 2,
        'ignore_keywords' => [
            'hi', 'hello', 'hey', 'bye', 'thanks', 'thank you', 'ok', 'okay', 'yes', 'no'
        ],
        'auto_respond' => true,
    ],
];
