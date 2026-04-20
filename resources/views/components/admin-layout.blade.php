@props(['header' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="antialiased bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-r border-gray-200 w-64 min-h-screen">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-800">Admin Panel</h2>
                    <!-- Message Icon with Counter -->
                    @auth('admin')
                        <x-message-icon />
                    @endauth
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="px-6 space-y-2">
                <a href="{{ route('admin.dashboard') }}"
                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700' : '' }}">
                    Dashboard
                </a>
                <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    Users
                </a>
                <a href="{{ route('admin.messages') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg {{ request()->routeIs('admin.messages') ? 'bg-blue-50 text-blue-700' : '' }}">
                    Open Chat
                </a>
                <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    Settings
                </a>
            </div>

        </nav>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-6 py-3 flex justify-between items-center min-h-[64px]">
                    <div class="flex-1">
                        {{ $header }}
                    </div>
                    <div class="flex items-center gap-4">
                        <!-- Redesigned Premium Logout Button -->
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" 
                                class="group flex items-center gap-2.5 rounded-full bg-slate-900 px-5 py-2.5 text-xs font-bold text-white shadow-lg transition-all duration-300 hover:bg-rose-600 hover:shadow-rose-200 hover:-translate-y-0.5 active:scale-95">
                                <span class="tracking-wider uppercase">Logout</span>
                                <div class="flex h-5 w-5 items-center justify-center rounded-full bg-white/20 transition-transform group-hover:rotate-12">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </div>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>

</html>
