<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body { font-family: 'Inter', sans-serif; }
            /* Modern Overrides */
            input[type="email"], input[type="password"], input[type="text"] {
                border-radius: 16px !important;
                border: 2px solid #f1f5f9 !important;
                background-color: #f8fafc !important;
                padding: 0.875rem 1.25rem !important;
                transition: all 0.2s !important;
                box-shadow: none !important;
                color: #0f172a !important;
            }
            input[type="email"]:focus, input[type="password"]:focus, input[type="text"]:focus {
                border-color: #6366f1 !important;
                background-color: #ffffff !important;
                box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
            }
            label {
                font-weight: 600 !important;
                color: #475569 !important;
                font-size: 0.875rem !important;
            }
            .modern-btn {
                border-radius: 16px !important;
                padding: 0.875rem 1.5rem !important;
                font-weight: 700 !important;
                font-size: 1rem !important;
                background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%) !important;
                border: none !important;
                color: white !important;
                transition: all 0.3s ease !important;
                box-shadow: 0 4px 14px 0 rgba(79, 70, 229, 0.39) !important;
                display: flex;
                justify-content: center;
                align-items: center;
                width: 100%;
                cursor: pointer;
            }
            .modern-btn:hover {
                transform: translateY(-2px) !important;
                box-shadow: 0 6px 20px rgba(79, 70, 229, 0.23) !important;
                background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%) !important;
            }
            
            /* Custom Checkbox */
            input[type="checkbox"] {
                width: 1.25rem;
                height: 1.25rem;
                border-radius: 6px !important;
                border: 2px solid #cbd5e1 !important;
                color: #4f46e5 !important;
            }
            input[type="checkbox"]:focus {
                box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1) !important;
                border-color: #4f46e5 !important;
            }
        </style>
    </head>
    <body class="antialiased text-slate-900 bg-white selection:bg-indigo-500 selection:text-white">
        
        <div class="flex min-h-dvh bg-white">
            
            <!-- Left Side: Animated Brand Area (Hidden on small screens) -->
            <div class="relative hidden w-0 flex-1 lg:block">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,_var(--tw-gradient-stops))] from-indigo-900 via-slate-900 to-black overflow-hidden">
                    
                    <!-- Abstract glowing orbs -->
                    <div class="absolute -top-[20%] -left-[10%] w-[70%] h-[70%] rounded-full bg-indigo-600/30 blur-[120px] mix-blend-screen animate-[pulse_8s_ease-in-out_infinite]"></div>
                    <div class="absolute bottom-[0%] right-[0%] w-[60%] h-[60%] rounded-full bg-blue-500/20 blur-[100px] mix-blend-screen animate-[pulse_10s_ease-in-out_infinite]"></div>
                    <div class="absolute top-[40%] right-[20%] w-[30%] h-[30%] rounded-full bg-purple-500/20 blur-[80px] mix-blend-screen animate-[pulse_6s_ease-in-out_infinite]"></div>
                    
                    <!-- Content Overlay -->
                    <div class="relative z-10 flex h-full flex-col justify-between p-16 xl:p-24">
                        <div>
                            <a href="/" class="flex items-center gap-3 w-fit hover:opacity-80 transition-opacity">
                                <div class="flex h-12 w-12 items-center justify-center rounded-[18px] bg-white text-indigo-600 shadow-[0_0_40px_rgba(255,255,255,0.2)]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                                    </svg>
                                </div>
                                <span class="text-3xl font-bold tracking-tight text-white">ChitChat</span>
                            </a>
                        </div>

                        <div class="mt-auto max-w-xl pb-12">
                            <h2 class="text-5xl font-extrabold tracking-tight text-white sm:text-6xl xl:text-7xl leading-[1.1]">
                                Modern.<br>Seamless.<br>Real-time.
                            </h2>
                            <p class="mt-8 text-lg leading-8 text-slate-300 font-medium">
                                Step into the future of team communication. Experience lightning-fast messaging, robust file sharing, and interactive groups in one beautifully designed workspace.
                            </p>
                        </div>
                        
                        <div class="flex gap-3">
                            <div class="h-2 w-16 rounded-full bg-white shadow-[0_0_15px_rgba(255,255,255,0.5)]"></div>
                            <div class="h-2 w-2 rounded-full bg-white/20"></div>
                            <div class="h-2 w-2 rounded-full bg-white/20"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Form Area -->
            <div class="flex flex-1 flex-col justify-center px-6 py-12 sm:px-12 lg:flex-none lg:px-20 xl:px-32">
                <div class="mx-auto w-full max-w-md lg:w-[420px]">
                    <!-- Mobile Logo -->
                    <div class="mb-10 lg:hidden flex justify-center">
                        <a href="/" class="flex items-center gap-3">
                            <div class="flex h-14 w-14 items-center justify-center rounded-[20px] bg-indigo-600 text-white shadow-xl shadow-indigo-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                            </div>
                        </a>
                    </div>
                    
                    {{ $slot }}

                </div>
            </div>
            
        </div>
        
    </body>
</html>
