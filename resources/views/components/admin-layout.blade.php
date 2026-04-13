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
                <a href="{{ route('chat.index', ['userId' => 1, 'type' => 'admin']) }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
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
            @if ($header)
                <header class="bg-white shadow-sm border-b border-gray-200">
                    <div class="px-6 py-4">
                        {{ $header }}
                    </div>
                </header>
            @endif

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
