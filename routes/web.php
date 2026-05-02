<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Modules\Messaging\Http\Controllers\ChatController;
use Modules\Messaging\Http\Middleware\EnsureMessagingFeatureEnabled;
use Modules\Messaging\Http\Controllers\ParticipantDirectoryController;

Route::get('/', function () {
    return view('welcome');
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
        Route::get('/fix-db', function () {
            try {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('conversation_participants', 'left_at')) {
                    \Illuminate\Support\Facades\Schema::table('conversation_participants', function ($table) {
                        $table->timestamp('left_at')->nullable();
                    });
                    return "Column 'left_at' added successfully.";
                }
                return "Column 'left_at' already exists.";
            } catch (\Exception $e) {
                return "Error: " . $e->getMessage();
            }
        });

        Route::get('/test-ai-message', function () {
            try {
                $conversation = \Modules\Messaging\Models\Conversation::first();
                if (!$conversation) return "No conversation found.";
                
                $message = \Modules\Messaging\Models\Message::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => 1,
                    'sender_type' => \App\Models\User::class,
                    'body' => 'Hello AI! What is Laravel?',
                    'type' => 'text'
                ]);
                
                event(new \Modules\Messaging\Events\MessageSent($message));
                return "Message sent! ID: " . $message->id;
            } catch (\Exception $e) {
                return "Error: " . $e->getMessage();
            }
        });

        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        Route::get('/messages', [ChatController::class, 'dashboard'])
            ->middleware(EnsureMessagingFeatureEnabled::class . ':enabled')
            ->name('admin.messages');
        Route::get('/messages/conversation', [ChatController::class, 'directConversation'])
            ->middleware(EnsureMessagingFeatureEnabled::class . ':enabled')
            ->name('admin.messages.conversation');
        Route::get('/users/list', [ParticipantDirectoryController::class, 'adminUsers'])
            ->middleware(EnsureMessagingFeatureEnabled::class . ':enabled')
            ->name('admin.users.list');

        Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/user/messages/conversation', [ChatController::class, 'userConversation'])
        ->middleware(EnsureMessagingFeatureEnabled::class . ':enabled')
        ->name('user.messages.conversation');
    Route::get('/messages', [ChatController::class, 'dashboard'])
        ->middleware(EnsureMessagingFeatureEnabled::class . ':enabled')
        ->name('user.messages');
});



Route::get('/test-ai', function () {
    $event = new \Modules\Messaging\Events\MessageSent(
        \Modules\Messaging\Models\Message::create([
            'conversation_id' => 1,
            'sender_id' => 1,
            'sender_type' => \App\Models\User::class,
            'body' => 'Hello, tell me a short joke.',
            'type' => 'text',
        ])
    );
    event($event);

    return response()->json([
        'status' => 'Event dispatched. Check laravel.log for details.'
    ]);
});

Route::get('/test-gemini', function () {
    $apiKey = env('GEMINI_API_KEY');
    $response = \Illuminate\Support\Facades\Http::get("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");
    return $response->json();
});

require __DIR__ . '/auth.php';
