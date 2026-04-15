<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            Cache::put('online_admin_' . Auth::guard('admin')->id(), true, now()->addSeconds(10));
            return redirect('/admin/dashboard');
        }

        return back()->with('error', 'Invalid credentials');
    }

    public function logout()
    {
        $adminId = Auth::guard('admin')->id();
        Auth::guard('admin')->logout();
        if ($adminId) {
            Cache::forget('online_admin_' . $adminId);
        }
        return redirect('/admin/login');
    }
}
