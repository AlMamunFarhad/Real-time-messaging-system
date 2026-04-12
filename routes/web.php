<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Modules\Messaging\Http\Controllers\MessageController;
use Modules\Messaging\Helpers\AuthParticipant;

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

        Route::get('/users/list', function () {
            $page = request()->get('page', 1);
            $adminId = auth()->guard('admin')->id();
            $users = \App\Models\User::where('id', '!=', $adminId)->orderBy('id', 'desc')->paginate(10, ['*'], 'page', $page);
            return response()->json([
                'users' => $users->items(),
                'currentPage' => $users->currentPage(),
                'lastPage' => $users->lastPage(),
                'total' => $users->total(),
            ]);
        })->name('admin.users.list');

        Route::get('/test-auth', function () {
            return [
                'guard' => \Modules\Messaging\Helpers\AuthParticipant::guard(),
                'id'    => \Modules\Messaging\Helpers\AuthParticipant::id(),
                'type'  => \Modules\Messaging\Helpers\AuthParticipant::type(),
                'name'  => \Modules\Messaging\Helpers\AuthParticipant::name(),
                'check' => \Modules\Messaging\Helpers\AuthParticipant::check(),
            ];
        });

        Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
    });
});

// Route::get('/test-auth', function () {
//     dd(AuthParticipant::id());
// });


require __DIR__ . '/auth.php';

Route::post('/send-message', [MessageController::class, 'send']);

// Route::get('/test-auth', function () {
//     return [
//         'guard' => \Modules\Messaging\Helpers\AuthParticipant::guard(),
//         'id'    => \Modules\Messaging\Helpers\AuthParticipant::id(),
//         'type'  => \Modules\Messaging\Helpers\AuthParticipant::type(),
//         'name'  => \Modules\Messaging\Helpers\AuthParticipant::name(),
//         'check' => \Modules\Messaging\Helpers\AuthParticipant::check(),
//     ];
// })->middleware('auth');

// Route::get('/test-auth', function () {
//     $webCheck = Auth::guard('web')->check();
//     $adminCheck = Auth::guard('admin')->check();

//     $result = [
//         'web_check' => $webCheck,
//         'admin_check' => $adminCheck,
//     ];

//     if ($webCheck) {
//         $result['web_id'] = Auth::guard('web')->id();
//         $result['web_user'] = Auth::guard('web')->user()?->name;
//     }

//     if ($adminCheck) {
//         $result['admin_id'] = Auth::guard('admin')->id();
//         $result['admin_user'] = Auth::guard('admin')->user()?->name;
//     }

//     $result['default_guard'] = AuthParticipant::guard();
//     $result['default_id'] = AuthParticipant::id();

//     return $result;
// })->middleware('auth');
