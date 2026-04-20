<x-guest-layout>
    <div class="mb-8 text-center lg:text-left">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Reset Password</h1>
        <p class="mt-3 text-sm text-slate-500 font-medium">Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4 rounded-lg bg-emerald-50 p-4 text-emerald-800" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email')" class="ml-1" />
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required autofocus placeholder="Enter your email" />
            <x-input-error :messages="$errors->get('email')" class="mt-1 ml-1" />
        </div>

        <div class="pt-4">
            <button class="modern-btn group">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3"></span>
                {{ __('Email Password Reset Link') }}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>
        
        <p class="text-center text-sm text-slate-500 mt-6 font-medium">
            Remembered your password? 
            <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:text-indigo-500 hover:underline transition-all">Back to sign in</a>
        </p>
    </form>
</x-guest-layout>
