<?php

namespace Modules\Messaging\Helpers;

use Illuminate\Support\Facades\Auth;

class AuthParticipant
{
    protected static $guards = ['admin', 'web']; // web = user

    public static function guard()
    {
        foreach (self::$guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $guard;
            }
        }

        return null;
    }

    public static function id()
    {
        $guard = self::guard();

        return $guard ? Auth::guard($guard)->id() : null;
    }

    public static function type()
    {
        $guard = self::guard();

        // dd($guard);

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
