<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Modules\Messaging\Http\Controllers\ChatController;
use Modules\Messaging\Http\Controllers\ParticipantDirectoryController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/debug-login', function () {
    return (new \App\Http\Controllers\Auth\AuthenticatedSessionController())->create();
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// Admin Routes
Route::prefix('admin')->group(function () {

    Route::get('/login', [AuthController::class, 'loginForm']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        Route::get('/messages', [ChatController::class, 'dashboard'])
            ->name('admin.messages');
        Route::get('/messages/conversation', [ChatController::class, 'directConversation'])
            ->name('admin.messages.conversation');
        Route::get('/users/list', [ParticipantDirectoryController::class, 'adminUsers'])
            ->name('admin.users.list');

        Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/user/messages/conversation', [ChatController::class, 'userConversation'])
        ->name('user.messages.conversation');
    Route::get('/messages', [ChatController::class, 'dashboard'])
        ->name('user.messages');
});



require __DIR__ . '/auth.php';
