@php
    $userId = $currentUserId ?? AuthParticipant::id();
    $userType = $currentUserType ?? AuthParticipant::type();
    $userTypeShort = $userType ? strtolower(class_basename($userType)) : null;
    $unread = $unreadCount ?? 0;
    $isAdminDashboard = $isAdminDashboard ?? $userTypeShort === 'admin';
    $currentUserName = 'User';

    if ($userType && $userId) {
        $user = $userType::find($userId);
        if ($user) {
            $currentUserName = $user->name ?? ($user->email ?? 'User #' . $userId);
        }
    }
@endphp

@if ($isAdminDashboard)
    <div x-data="window.messageCounterBadge({
        unreadCount: {{ $unread }},
        conversationsUrl: @js(route('messages.conversations'))
    })" x-init="init()" class="relative">
        <a href="{{ route('admin.messages') }}"
            class="group relative inline-flex items-center gap-2 rounded-xl bg-orange-50 px-3 py-2 text-sm font-bold text-orange-600 transition-all hover:bg-orange-100">
            <span>Messages</span>
            <template x-if="unreadCount > 0">
                <span
                    class="flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-sm"
                    x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
            </template>
        </a>
    </div>

@elseif (\App\Models\Admin::first())
    <div x-data="window.messageCounterBadge({
        unreadCount: {{ $unread }},
        conversationsUrl: @js(route('messages.conversations'))
    })" x-init="init()" class="relative">
        <a href="{{ route('user.messages') }}"
            class="group relative inline-flex items-center gap-2 rounded-xl bg-orange-50 px-3 py-2 text-sm font-bold text-orange-600 transition-all hover:bg-orange-100">
            <span>Messages</span>
            <template x-if="unreadCount > 0">
                <span
                    class="flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-sm"
                    x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
            </template>
        </a>
    </div>
@endif

@if ($isAdminDashboard || \App\Models\Admin::first())
    <style>
        .message-row {
            display: flex;
            width: 100%;
            margin-bottom: 18px;
        }

        .message-row.my-message {
            justify-content: flex-end;
        }

        .message-row.their-message {
            justify-content: flex-start;
        }

        .message-container {
            max-width: min(78%, 680px);
            display: flex;
            flex-direction: column;
        }

        .message-row.my-message .message-container {
            align-items: flex-end;
        }

        .message-bubble {
            border-radius: 22px;
            padding: 14px 16px;
            font-size: 14px;
            line-height: 1.6;
            word-break: break-word;
            box-shadow: 0 18px 35px -26px rgba(15, 23, 42, .45);
        }

        .my-message .message-bubble {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            border-bottom-right-radius: 8px;
        }

        .their-message .message-bubble {
            background: #fff;
            color: #0f172a;
            border: 1px solid rgba(148, 163, 184, .28);
            border-bottom-left-radius: 8px;
        }

        .sender-name {
            margin-bottom: 6px;
            padding-left: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
        }

        .message-time {
            margin-top: 6px;
            padding: 0 6px;
            font-size: 11px;
            color: #64748b;
        }
    </style>

    <script>
        window.dispatchMessageCounterSync = window.dispatchMessageCounterSync || function(reason = 'refresh', extra = {}) {
            try {
                const payload = JSON.stringify({
                    reason,
                    at: Date.now(),
                    ...extra,
                });

                localStorage.setItem('message-counter-sync', payload);
                window.dispatchEvent(new CustomEvent('message-counter-sync', {
                    detail: JSON.parse(payload)
                }));
            } catch (error) {
                console.error('Message counter sync error:', error);
            }
        };

        window.messageCounterBadge = window.messageCounterBadge || function(config) {
            return {
                unreadCount: config.unreadCount || 0,
                refreshTimer: null,
                init() {
                    // Start refresh loop immediately
                    this.startRefreshLoop();
                    
                    // Delay initial refresh by 1s to allow dashboard markRead to settle
                    setTimeout(() => this.refreshConversations(), 1000);
                    
                    window.addEventListener('message-counter-sync', () => this.refreshConversations());
                    window.addEventListener('focus', () => this.refreshConversations());
                    document.addEventListener('visibilitychange', () => {
                        if (document.visibilityState === 'visible') this.refreshConversations();
                    });
                    window.addEventListener('storage', (event) => {
                        if (event.key === 'message-counter-sync') this.refreshConversations();
                    });
                },
                startRefreshLoop() {
                    if (this.refreshTimer) clearInterval(this.refreshTimer);
                    this.refreshTimer = setInterval(() => this.refreshConversations(), 2000);
                },
                async refreshConversations() {
                    try {
                        const response = await fetch(config.conversationsUrl + '?t=' + Date.now(), {
                            credentials: 'include',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });
                        if (!response.ok) return;
                        const data = await response.json();
                        this.unreadCount = data.unread_count || 0;
                    } catch (error) {
                        console.error('Message counter error:', error);
                    }
                }
            };
        };
    </script>
@endif
