<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Modules\Messaging\Http\Controllers\MessageController;
use Modules\Messaging\Helpers\AuthParticipant;
use Modules\Messaging\Http\Controllers\ChatController;

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

        Route::get('/messages', [ChatController::class, 'adminDashboard'])
            ->name('admin.messages');
        Route::get('/messages/conversation', [ChatController::class, 'adminConversation'])
            ->name('admin.messages.conversation');

        Route::get('/users/list', function () {
            $search = trim((string) request()->get('q', ''));
            $query = \App\Models\User::query()
                ->where('id', '!=', optional(auth('admin')->user())->id)
                ->orderBy('name');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            $users = $query->get(['id', 'name', 'email']);

            return response()->json([
                'users' => $users,
                'total' => $users->count(),
            ]);
        })->name('admin.users.list');

        Route::get('/test-auth', function () {
            return [
                'guard' => AuthParticipant::guard(),
                'id'    => AuthParticipant::id(),
                'type'  => AuthParticipant::type(),
                'name'  => AuthParticipant::name(),
                'check' => AuthParticipant::check(),
            ];
        });

        Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/user/messages/conversation', [ChatController::class, 'userConversation'])
        ->name('user.messages.conversation');
});



require __DIR__ . '/auth.php';

Route::post('/send-message', [MessageController::class, 'send']);
