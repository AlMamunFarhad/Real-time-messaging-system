<?php

namespace Modules\Messaging\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthParticipant
{
    protected static $guards = ['web', 'admin'];

    public static function guard(?string $specific = null)
    {
        if ($specific) {
            return Auth::guard($specific)->check() ? $specific : null;
        }

        $sessionKey = config('session.variable_name', 'login_') . 'web_' . sha1('App\\Auth\\Guard\\Web');
        $adminKey = config('session.variable_name', 'login_') . 'admin_' . sha1('App\\Auth\\Guard\\Admin');

        if (Session::has($adminKey)) {
            return 'admin';
        }

        if (Session::has($sessionKey)) {
            return 'web';
        }

        foreach (self::$guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $guard;
            }
        }

        return null;
    }

    public static function id(?string $guard = null)
    {
        $g = $guard ?? self::guard();
        return $g ? Auth::guard($g)->id() : null;
    }

    public static function type(?string $guard = null)
    {
        $g = $guard ?? self::guard();

        return match ($g) {
            'admin' => \App\Models\Admin::class,
            'web'   => \App\Models\User::class,
            default => null,
        };
    }

    public static function model(?string $guard = null)
    {
        $g = $guard ?? self::guard();
        return $g ? Auth::guard($g)->user() : null;
    }

    public static function name(?string $guard = null)
    {
        $user = self::model($guard);
        return $user?->name ?? 'Unknown';
    }

    public static function check(?string $guard = null)
    {
        return self::guard($guard) !== null;
    }

    public static function isAdmin(): bool
    {
        return self::guard('admin') !== null;
    }

    public static function isUser(): bool
    {
        return self::guard('web') !== null;
    }

    public static function userId(): ?int
    {
        return self::id('web');
    }

    public static function adminId(): ?int
    {
        return self::id('admin');
    }
}