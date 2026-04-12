<?php

namespace Modules\Messaging\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Messaging\Helpers\AuthParticipant;
use Illuminate\Support\Facades\Cache;

class OnlineStatusController extends Controller
{
    public function heartbeat()
    {
        $id = AuthParticipant::id();
        $type = AuthParticipant::type(); // full class e.g. App\Models\User

        if (!$id || !$type) {
            return response()->json(['status' => 'error'], 401);
        }

        // Use short type name to match what check() uses from URL params
        $typeShort = strtolower(class_basename($type)); // "user" or "admin"
        $key = "online_{$typeShort}_{$id}";
        Cache::put($key, true, now()->addSeconds(10));

        return response()->json(['status' => 'ok']);
    }

    public function check($userId, $type)
    {
        $key = "online_{$type}_{$userId}";
        $isOnline = Cache::has($key);

        return response()->json([
            'online' => $isOnline,
            'key' => $key,
            'checkedUserId' => $userId,
            'typeShort' => $type
        ]);
    }
}
