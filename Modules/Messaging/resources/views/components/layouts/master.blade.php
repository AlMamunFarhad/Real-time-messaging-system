<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Messaging Module - {{ config('app.name', 'Laravel') }}</title>

    <meta name="description" content="{{ $description ?? '' }}">
    <meta name="keywords" content="{{ $keywords ?? '' }}">
    <meta name="author" content="{{ $author ?? '' }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Vite CSS --}}
    {{-- {{ module_vite('build-messaging', 'resources/assets/sass/app.scss') }} --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    @php
        $notificationAuth = null;

        if (Auth::guard('admin')->check()) {
            $notificationAuth = [
                'type' => 'admin',
                'id' => (int) Auth::guard('admin')->id(),
            ];
        } elseif (Auth::guard('web')->check()) {
            $notificationAuth = [
                'type' => 'user',
                'id' => (int) Auth::guard('web')->id(),
            ];
        }
    @endphp

    {{ $slot }}

    @if ($notificationAuth)
        <div id="message-toast-root" class="pointer-events-none fixed right-4 top-4 z-[80] flex w-full max-w-sm flex-col gap-3 sm:right-6 sm:top-6"></div>
        <script>
            (() => {
                const auth = @json($notificationAuth);
                if (!auth || typeof window === 'undefined') return;

                const root = document.getElementById('message-toast-root');
                if (!root) return;

                const activeToastIds = new Set();

                const escapeHtml = (value) => String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');

                const buildPreview = (message) => {
                    const body = String(message?.body || '').trim();
                    if (body) return body.length > 90 ? `${body.slice(0, 90)}...` : body;

                    const fileName = String(message?.file_name || '').trim();
                    if (fileName) return `Attachment: ${fileName}`;

                    return message?.type === 'file' ? 'Sent an attachment' : 'New message received';
                };

                const showToast = (message) => {
                    if (!message || !message.id || activeToastIds.has(String(message.id))) return;

                    activeToastIds.add(String(message.id));

                    const toast = document.createElement('button');
                    toast.type = 'button';
                    toast.className = 'pointer-events-auto translate-y-0 opacity-0 transition-all duration-300 text-left';
                    toast.innerHTML = `
                        <div class="overflow-hidden rounded-[24px] border border-white/70 bg-white/95 shadow-[0_24px_60px_rgba(15,23,42,0.16)] ring-1 ring-orange-100/80 backdrop-blur-xl">
                            <div class="bg-gradient-to-r from-rose-500 via-orange-400 to-amber-400 px-4 py-1.5"></div>
                            <div class="flex items-start gap-3 px-4 py-4">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-orange-400 text-white shadow-lg shadow-orange-200/70">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-[12px] font-black uppercase tracking-[0.2em] text-orange-500">New Message</p>
                                        <span class="rounded-full bg-orange-50 px-2 py-1 text-[10px] font-bold text-orange-500">Live</span>
                                    </div>
                                    <p class="mt-1 truncate text-[15px] font-bold tracking-tight text-stone-800">${escapeHtml(message.sender_name || 'Someone')}</p>
                                    <p class="mt-1 text-[13px] leading-5 text-stone-500">${escapeHtml(buildPreview(message))}</p>
                                </div>
                            </div>
                        </div>
                    `;

                    const removeToast = () => {
                        toast.classList.remove('opacity-100', 'translate-y-0');
                        toast.classList.add('opacity-0', 'translate-y-[-8px]');
                        setTimeout(() => {
                            activeToastIds.delete(String(message.id));
                            toast.remove();
                        }, 220);
                    };

                    toast.addEventListener('click', () => {
                        window.dispatchEvent(new CustomEvent('messaging:notification-click', {
                            detail: message
                        }));
                        removeToast();
                    });

                    root.appendChild(toast);

                    requestAnimationFrame(() => {
                        toast.classList.remove('opacity-0');
                        toast.classList.add('opacity-100');
                    });

                    setTimeout(removeToast, 4500);
                };

                const connectNotificationChannel = () => {
                    if (typeof window.Echo === 'undefined') {
                        setTimeout(connectNotificationChannel, 1200);
                        return;
                    }

                    window.Echo.private(`user.${auth.type}.${auth.id}`)
                        .listen('.message.sent', (message) => {
                            showToast(message);
                        });
                };

                connectNotificationChannel();
            })();
        </script>
    @endif

    {{-- Vite JS --}}
    {{-- {{ module_vite('build-messaging', 'resources/assets/js/app.js') }} --}}
    @stack('scripts')
</body>

</html>
