<?php

use Illuminate\Support\Facades\Route;
use Modules\AIChat\Http\Controllers\AIChatController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('aichats', AIChatController::class)->names('aichat');
});
