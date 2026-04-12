<?php

namespace Modules\Messaging\Helpers;

use Illuminate\Support\Facades\Auth;

class AuthParticipant
{
    protected static $guards = ['admin', 'web']; // web = user

    public static function guard()
    {
        // Check admin first (priority)
        if (Auth::guard('admin')->check()) {
            return 'admin';
        }
        if (Auth::guard('web')->check()) {
            return 'web';
        }

        return null;
    }

    public static function id()
    {
        $guard = self::guard();
        if (!$guard) return null;

        // If admin guard, use admin guard's id
        if ($guard === 'admin') {
            return Auth::guard('admin')->id();
        }
        
        return Auth::guard($guard)->id();
    }

    public static function type()
    {
        $guard = self::guard();

        return match ($guard) {
            'admin' => \App\Models\Admin::class,
            'web'   => \App\Models\User::class,
            default => null,
        };
    }

    public static function model()
    {
        $guard = self::guard();

        return $guard ? Auth::guard($guard)->user() : null;
    }

    public static function name()
    {
        $user = self::model();

        return $user->name ?? 'Unknown';
    }

    public static function check()
    {
        return self::guard() !== null;
    }
}
