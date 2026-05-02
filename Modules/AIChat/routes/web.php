<?php

use Illuminate\Support\Facades\Route;
use Modules\AIChat\Http\Controllers\AIChatController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('aichats', AIChatController::class)->names('aichat');
});
