@props(['auth'])

@if ($auth && messaging_feature('notifications'))
    <div id="global-message-toast-root"
        class="pointer-events-none fixed right-4 top-4 z-[90] flex w-full max-w-sm flex-col gap-3 sm:right-6 sm:top-6">
    </div>
    <script>
        (() => {
            const auth = @json($auth);
            if (!auth || typeof window === 'undefined') return;

            window.__dashboardGlobalMessageNotifications = true;

            const root = document.getElementById('global-message-toast-root');
            if (!root) return;

            const activeToastIds = new Set();
            const seenMessageIds = new Set();
            const storageKey = `message_notification_cursor_${auth.type}_${auth.id}`;
            let lastSeenAt = window.localStorage.getItem(storageKey);

            const escapeHtml = (value) => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const buildPreview = (message) => {
                const body = String(message?.body || '').trim();
                if (body) return body.length > 110 ? `${body.slice(0, 110)}...` : body;

                const fileName = String(message?.file_name || '').trim();
                if (fileName) {
                    if (/\.(webm|mp3|wav|ogg|m4a|aac)$/i.test(fileName)) return 'Voice Message';
                    if (/\.(jpg|jpeg|png|gif|webp|bmp|svg)$/i.test(fileName)) return 'Photo';

                    return 'Attachment';
                }

                return message?.type === 'file' ? 'Attachment' : 'New message received';
            };

            const removeToast = (toast, messageId) => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-[-8px]');
                setTimeout(() => {
                    activeToastIds.delete(String(messageId));
                    toast.remove();
                }, 220);
            };

            const showToast = (message, unreadCount = null) => {
                const senderType = String(message?.sender_type || '').split('\\').pop().toLowerCase();
                const isOwnMessage = String(message?.sender_id) === String(auth.id) && senderType === auth.type;
                if (!message?.id || isOwnMessage || activeToastIds.has(String(message.id))) return;

                activeToastIds.add(String(message.id));

                const toast = document.createElement('button');
                toast.type = 'button';
                toast.className =
                    'pointer-events-auto opacity-0 translate-y-0 transition-all duration-300 text-left';
                toast.innerHTML = `
                    <div class="group relative overflow-hidden rounded-2xl bg-white/80 backdrop-blur-xl border border-white/60 shadow-[0_10px_40px_rgba(0,0,0,0.08)] hover:shadow-[0_20px_60px_rgba(0,0,0,0.12)] transition-all duration-300">

    <!-- Top Gradient Accent -->
    <div class="absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-rose-500 via-orange-400 to-amber-400"></div>

    <!-- Glow Effect -->
    <div class="absolute -top-10 -right-10 h-32 w-32 bg-orange-400/20 rounded-full blur-3xl opacity-70 group-hover:opacity-100 transition"></div>

    <div class="flex items-start gap-4 px-5 py-4 relative z-10">

        <!-- Icon -->
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-orange-400 text-white shadow-md shadow-orange-200/60">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-orange-500">
                    New Message
                </p>

                <span class="flex items-center gap-1 text-[10px] font-semibold text-orange-500 bg-orange-50 px-2 py-1 rounded-full">
                    <span class="inline-flex min-w-[18px] items-center justify-center rounded-full bg-white px-1.5 py-0.5 text-[10px] font-bold text-orange-500 shadow-sm">
                        ${escapeHtml(unreadCount && unreadCount > 0 ? unreadCount : 1)}
                    </span>
                </span>
            </div>

            <!-- Sender -->
            <p class="mt-1 text-[15px] font-semibold text-gray-900 truncate">
                ${escapeHtml(message.sender_name || 'Someone')}
            </p>

            <!-- Message Preview -->
            <p class="mt-1 text-[13px] text-gray-500 leading-relaxed line-clamp-2">
                ${escapeHtml(buildPreview(message))}
            </p>

        </div>
    </div>
</div>
                `;

                toast.addEventListener('click', () => {
                    window.location.href = auth.messagesUrl;
                });

                root.appendChild(toast);

                requestAnimationFrame(() => {
                    toast.classList.remove('opacity-0');
                    toast.classList.add('opacity-100');
                });

                setTimeout(() => removeToast(toast, message.id), 5000);
            };

            const pollFeed = async (isBoot = false) => {
                if (!auth.feedUrl) return;

                try {
                    if (typeof window.axios === 'undefined') {
                        window.axios = {
                            get: async (targetUrl) => {
                                const res = await fetch(targetUrl, {
                                    credentials: 'same-origin'
                                });
                                const contentType = res.headers.get('content-type') || '';
                                const data = contentType.includes('application/json') ? await res
                                .json() : await res.text();
                                if (!res.ok) {
                                    const err = new Error('Request failed');
                                    err.response = {
                                        status: res.status,
                                        data
                                    };
                                    throw err;
                                }
                                return {
                                    status: res.status,
                                    data
                                };
                            }
                        };
                    }

                    const url = new URL(auth.feedUrl, window.location.origin);
                    if (lastSeenAt) {
                        url.searchParams.set('since', lastSeenAt);
                    }

                    const response = await window.axios.get(url.toString());
                    const messages = response.data?.messages || [];
                    const unreadCount = Number(response.data?.unread_count || 0);
                    const newMessages = messages.filter((message) => !seenMessageIds.has(String(message.id)));

                    newMessages.forEach((message) => {
                        seenMessageIds.add(String(message.id));
                    });

                    if (!isBoot && newMessages.length) {
                        const latestMessage = newMessages[newMessages.length - 1];
                        showToast(latestMessage, unreadCount);
                    }

                    if (messages.length) {
                        lastSeenAt = messages[messages.length - 1].created_at || response.data?.server_time ||
                            lastSeenAt;
                    } else if (!lastSeenAt) {
                        lastSeenAt = response.data?.server_time || new Date().toISOString();
                    }

                    if (lastSeenAt) {
                        window.localStorage.setItem(storageKey, lastSeenAt);
                    }
                } catch (error) {
                    console.error('Notification feed error:', error);
                }
            };

            const connectEcho = (retryCount = 0) => {
                if (typeof window.Echo === 'undefined') {
                    if (retryCount < 10) setTimeout(() => connectEcho(retryCount + 1), 1000);
                    return;
                }

                const channelName = `user.${auth.type}.${auth.id}`;
                console.log('Global notification listener connecting to:', channelName);

                window.Echo.private(channelName)
                    .listen('.message.sent', (message) => {
                        console.log('Global notification received:', message);
                        if (!message) return;
                        
                        // Small delay to let DB catch up if needed
                        setTimeout(() => {
                            showToast(message);
                            if (typeof window.dispatchMessageCounterSync === 'function') {
                                window.dispatchMessageCounterSync('received', {
                                    conversationId: message.conversation_id
                                });
                            }
                        }, 100);
                    });
            };

            connectEcho();

            pollFeed(true);
            setInterval(() => {
                // If Echo is connected, skip polling to reduce server load
                const isEchoConnected = window.Echo && window.Echo.connector &&
                    window.Echo.connector.pusher && window.Echo.connector.pusher.connection.state ===
                    'connected';

                if (isEchoConnected) return;

                pollFeed(false);
            }, 30000); // Increased from 4s to 30s fallback
        })();
    </script>
@endif
