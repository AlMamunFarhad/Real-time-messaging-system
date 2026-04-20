<x-guest-layout>
    <div class="mb-10 text-center lg:text-left">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Welcome back</h1>
        <p class="mt-3 text-sm text-slate-500 font-medium">Please enter your details to sign in.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6 rounded-lg bg-emerald-50 p-4 text-emerald-800" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email')" class="ml-1" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required
                autofocus autocomplete="username" placeholder="Enter your email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 ml-1" />
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <x-input-label for="password" :value="__('Password')" class="ml-1" />
            <x-text-input id="password" class="block w-full" type="password" name="password" required
                autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 ml-1" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between pt-2">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <input id="remember_me" type="checkbox" name="remember" class="transition-shadow duration-200">
                <span class="ms-3 text-sm font-medium text-slate-600 group-hover:text-slate-900 transition-colors">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 transition-colors"
                    href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <div class="pt-4">
            <button class="modern-btn group">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3"></span>
                {{ __('Sign In') }}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>
        
        <p class="text-center text-sm text-slate-500 mt-8 font-medium">
            Don't have an account? 
            <a href="{{ route('register') }}" class="font-bold text-indigo-600 hover:text-indigo-500 hover:underline transition-all">Sign up for free</a>
        </p>
    </form>
</x-guest-layout>
