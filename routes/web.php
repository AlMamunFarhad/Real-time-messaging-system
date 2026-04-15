<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Modules\Messaging\Models\Conversation;
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
            $admin = auth('admin')->user();
            $query = \App\Models\User::query()
                ->where('id', '!=', optional($admin)->id)
                ->orderBy('name');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            $users = $query->get(['id', 'name', 'email']);

            $unreadByUser = [];

            if ($admin && $users->isNotEmpty()) {
                $adminType = \App\Models\Admin::class;
                $adminTypeShort = strtolower(class_basename($adminType));
                $userType = \App\Models\User::class;
                $userTypeShort = strtolower(class_basename($userType));
                $userIds = $users->pluck('id')->all();

                $conversations = Conversation::query()
                    ->where('type', 'private')
                    ->whereHas('participants', function ($q) use ($admin, $adminType, $adminTypeShort) {
                        $q->where('participant_id', $admin->id)
                            ->whereIn('participant_type', [$adminType, $adminTypeShort]);
                    })
                    ->whereHas('participants', function ($q) use ($userIds, $userType, $userTypeShort) {
                        $q->whereIn('participant_id', $userIds)
                            ->whereIn('participant_type', [$userType, $userTypeShort]);
                    })
                    ->with('participants')
                    ->get();

                foreach ($conversations as $conversation) {
                    $adminParticipant = $conversation->participants->first(function ($participant) use ($admin, $adminType, $adminTypeShort) {
                        return (int) $participant->participant_id === (int) $admin->id
                            && in_array($participant->participant_type, [$adminType, $adminTypeShort], true);
                    });

                    $userParticipant = $conversation->participants->first(function ($participant) use ($userIds, $userType, $userTypeShort) {
                        return in_array((int) $participant->participant_id, $userIds, true)
                            && in_array($participant->participant_type, [$userType, $userTypeShort], true);
                    });

                    if (!$adminParticipant || !$userParticipant) {
                        continue;
                    }

                    $unreadByUser[$userParticipant->participant_id] = $conversation->messages()
                        ->when($adminParticipant->last_read_at, fn($messageQuery) => $messageQuery->where('created_at', '>', $adminParticipant->last_read_at))
                        ->where(function ($messageQuery) use ($admin, $adminType, $adminTypeShort) {
                            $messageQuery->where('sender_id', '!=', $admin->id)
                                ->orWhereNotIn('sender_type', [$adminType, $adminTypeShort]);
                        })
                        ->count();
                }
            }

            $users->transform(function ($user) use ($unreadByUser) {
                $user->unseen_count = (int) ($unreadByUser[$user->id] ?? 0);
                $user->is_online = \Illuminate\Support\Facades\Cache::has('online_user_' . $user->id);

                return $user;
            });

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
