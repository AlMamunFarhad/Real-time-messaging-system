<?php

namespace Modules\Messaging\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Messaging\Helpers\AuthParticipant;

class TrackOnlineStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (AuthParticipant::check()) {
            $id = AuthParticipant::id();
            $type = AuthParticipant::type();
            
            if ($id && $type) {
                $typeShort = strtolower(class_basename($type));
                $key = "online_{$typeShort}_{$id}";
                
                // Set online status for 60 seconds
                Cache::put($key, true, now()->addSeconds(60));
            }
        }

        return $next($request);
    }
}
