<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Gemini\Laravel\Facades\Gemini;

$models = ['gemini-flash-latest', 'gemini-2.0-flash', 'gemini-pro-latest', 'gemini-1.5-flash'];

foreach ($models as $model) {
    echo "Testing $model... ";
    try {
        $result = Gemini::generativeModel($model)->generateContent('Hello');
        echo "SUCCESS: " . substr($result->text(), 0, 50) . "...\n";
        break; // Stop if we find one that works
    } catch (\Exception $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}
