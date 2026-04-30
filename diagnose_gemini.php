<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Gemini\Laravel\Facades\Gemini;

echo "--- Gemini API Diagnostic ---\n";
echo "API Key: " . (config('gemini.api_key') ? "Set (ends in ..." . substr(config('gemini.api_key'), -4) . ")" : "NOT SET") . "\n";

try {
    echo "Fetching models...\n";
    $models = Gemini::models()->list();
    foreach ($models->models as $model) {
        if (str_contains($model->name, 'gemini')) {
            echo "- Name: " . $model->name . "\n";
            echo "  Methods: " . implode(', ', $model->supportedGenerationMethods) . "\n";
        }
    }
} catch (\Exception $e) {
    echo "Error listing models: " . $e->getMessage() . "\n";
}

try {
    echo "\nTesting basic generation (gemini-2.0-flash)...\n";
    $result = Gemini::generativeModel('gemini-2.0-flash')->generateContent('Hello');
    echo "Result: " . $result->text() . "\n";
} catch (\Exception $e) {
    echo "Error with gemini-2.0-flash: " . $e->getMessage() . "\n";
}

try {
    echo "\nTesting basic generation (gemini-pro-latest)...\n";
    $result = Gemini::generativeModel('gemini-pro-latest')->generateContent('Hello');
    echo "Result: " . $result->text() . "\n";
} catch (\Exception $e) {
    echo "Error with gemini-pro-latest: " . $e->getMessage() . "\n";
}
