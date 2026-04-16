<x-admin-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Admin Messages</h2>
        </div>
    </x-slot>

    @php
        $participantId = \Modules\Messaging\Helpers\AuthParticipant::id();
        $participantType = \Modules\Messaging\Helpers\AuthParticipant::type();
        $participantTypeShort = strtolower(class_basename($participantType));
        $initialConversationId = (int) ($conversation?->id ?? 0);
        $initialOtherParticipantId = (int) ($otherParticipant?->participant_id ?? 0);
        $initialOtherParticipantType = $otherParticipant
            ? strtolower(
                class_basename(
                    match ($otherParticipant->participant_type) {
                        'admin' => \App\Models\Admin::class,
                        'user' => \App\Models\User::class,
                        default => $otherParticipant->participant_type,
                    },
                ),
            )
            : '';
        $initialOtherUserName = $otherUserName ?? '';
        $initialActiveUserId = (int) ($activeUserId ?? 0);
    @endphp

    <div x-data="adminMessagesApp({
        userId: {{ $participantId ?? 0 }},
        userTypeShort: '{{ $participantTypeShort }}',
        initialConversationId: {{ $initialConversationId }},
        initialOtherParticipantId: {{ $initialOtherParticipantId }},
        initialOtherParticipantType: '{{ $initialOtherParticipantType }}',
        initialOtherUserName: @js($initialOtherUserName),
        initialActiveUserId: {{ $initialActiveUserId }},
        showChat: false
    })" x-init="init()" class="-m-6 mx-auto max-w-7xl overflow-hidden">
        <div
            class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_24px_80px_-28px_rgba(15,23,42,0.35)]">
            <div class="grid min-h-[76vh] lg:grid-cols-[340px_minmax(0,1fr)]">
                <aside class="border-b border-slate-200 bg-slate-50 lg:border-b-0 lg:border-r">
                    <div class="border-b border-slate-200 bg-white px-5 py-5">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-3 3-3-3z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-slate-900">Users</h3>
                                <p class="text-sm text-slate-500">Search and select a user</p>
                            </div>
                        </div>
                        <div class="relative mt-4">
                            <input x-model="search" @input="debouncedLoadUsers()" type="text"
                                placeholder="Search by name or email"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 pr-10 text-sm text-slate-700 outline-none focus:border-slate-400 focus:ring-4 focus:ring-slate-200">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="pointer-events-none absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z" />
                            </svg>
                        </div>
                    </div>

                    <div class="max-h-[calc(76vh-110px)] overflow-y-auto px-3 py-3">
                        <template x-if="loadingUsers">
                            <div class="space-y-2">
                                <div class="h-16 animate-pulse rounded-2xl bg-white"></div>
                                <div class="h-16 animate-pulse rounded-2xl bg-white"></div>
                                <div class="h-16 animate-pulse rounded-2xl bg-white"></div>
                            </div>
                        </template>

                        <template x-if="!loadingUsers && users.length === 0">
                            <div
                                class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-10 text-center">
                                <p class="text-sm font-medium text-slate-600">No user found</p>
                            </div>
                        </template>

                        <div class="space-y-2" x-show="!loadingUsers && users.length">
                            <template x-for="user in users" :key="user.id">
                                <button type="button" @click="selectUser(user)"
                                    class="block w-full rounded-2xl border px-4 py-3 text-left transition"
                                    :class="Number(activeUserId) === Number(user.id) ?
                                        'border-slate-900 bg-slate-900 text-white' :
                                        'border-transparent bg-white text-slate-800 hover:border-slate-200 hover:bg-slate-100'">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl text-sm font-semibold"
                                            :class="Number(activeUserId) === Number(user.id) ? 'bg-white/15 text-white' :
                                                'bg-slate-100 text-slate-700'"
                                            x-text="initialFor(user.name)"></div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex h-2.5 w-2.5 shrink-0 rounded-full"
                                                    :class="user.is_online ?
                                                        'bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,0.16)]' :
                                                        'bg-slate-300'"></span>
                                                <div class="truncate text-sm font-semibold" x-text="user.name"></div>
                                            </div>
                                            <div class="truncate text-xs"
                                                :class="Number(activeUserId) === Number(user.id) ? 'text-slate-300' :
                                                    'text-slate-500'"
                                                x-text="user.is_online ? user.email + ' • Active now' : user.email">
                                            </div>
                                        </div>
                                        <template x-if="Number(user.unseen_count || 0) > 0">
                                            <span
                                                class="inline-flex h-6 min-w-[1.5rem] items-center justify-center rounded-full px-2 text-xs font-bold"
                                                :class="Number(activeUserId) === Number(user.id) ? 'bg-white text-slate-900' :
                                                    'bg-rose-500 text-white'"
                                                x-text="Number(user.unseen_count) > 99 ? '99+' : user.unseen_count"></span>
                                        </template>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </aside>

                <section
                    class="hidden min-h-[76vh] flex-col bg-[radial-gradient(circle_at_top,#f8fafc_0%,#ffffff_42%,#f8fafc_100%)] lg:flex">
                    <template x-if="activeConversationId">
                        <div class="flex h-full flex-col">
                            <div class="border-b border-slate-200 bg-white px-6 py-5">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-300 text-slate-900">
                                            <span class="text-base font-semibold"
                                                x-text="initialFor(activeUserName)"></span>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-semibold text-slate-900"
                                                x-text="activeUserName || 'User'"></h3>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div id="online-status"
                                            class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-500">
                                            <span id="online-dot" class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                                            <span id="online-text">Offline</span>
                                        </div>
                                        <a href="{{ route('admin.dashboard') }}"
                                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 p-2 text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div id="chat-box" class="flex-1 max-h-[calc(80vh-180px)] overflow-y-auto px-6 py-6"></div>

                            <div class="border-t border-slate-200 bg-white px-6 py-6">
                                <div id="file-preview"
                                    class="mb-3 hidden rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <span id="file-name"
                                            class="flex items-center gap-2 text-sm text-slate-600"></span>
                                        <button type="button" @click="removeFile()"
                                            class="text-xl leading-none text-slate-400 transition hover:text-slate-700">&times;</button>
                                    </div>
                                </div>
                                <div class="flex items-end gap-3">
                                    <input type="file" id="file-input" class="hidden"
                                        @change="handleFileSelect($event)">
                                    <button type="button" @click="openFilePicker()"
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-slate-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                    </button>
                                    <input type="text" id="message-input" x-model="draftMessage"
                                        @keydown.enter.prevent="sendMessage()"
                                        class="h-12 flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none focus:border-slate-400 focus:ring-4 focus:ring-slate-200"
                                        placeholder="Write a message...">
                                    <button type="button" @click="sendMessage()"
                                        class="inline-flex h-12 shrink-0 items-center gap-2 rounded-2xl bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800">Send</button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="!activeConversationId">
                        <div class="flex flex-1 items-center justify-center px-6 py-10">
                            <div class="max-w-md text-center">
                                <div
                                    class="mx-auto flex h-20 w-20 items-center justify-center rounded-[28px] bg-slate-100 text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-3 3-3-3z" />
                                    </svg>
                                </div>
                                <h3 class="mt-6 text-xl font-semibold text-slate-900">Message box blank</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-500">Select a user from the left sidebar to
                                    open chat here.</p>
                            </div>
                        </div>
                    </template>
                </section>
            </div>

            <!-- Modal for medium and smaller screens -->
            <div x-cloak x-show="showChat" x-transition.opacity class="fixed inset-x-0 bottom-0 top-0 z-50 lg:hidden">
                <div class="fixed inset-x-0 bottom-0 top-0 mt-16 flex h-[calc(100dvh-4rem)] w-full flex-col overflow-hidden bg-white shadow-[0_24px_80px_-28px_rgba(15,23,42,0.35)]"
                    @click.stop>
                    <template x-if="activeConversationId">
                        <div class="flex h-full flex-col">
                            <div
                                class="border-b border-slate-200 bg-white px-6 py-5 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-300 text-slate-900">
                                        <span class="text-base font-semibold"
                                            x-text="initialFor(activeUserName)"></span>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-semibold text-slate-900"
                                            x-text="activeUserName || 'User'"></h3>
                                    </div>
                                </div>
                                <button type="button" @click="showChat = false"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                                    aria-label="Close chat">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div id="chat-box-modal" class="flex-1 overflow-y-auto px-6 py-6"></div>

                            <div class="border-t border-slate-200 bg-white px-6 py-2">
                                <div id="file-preview-modal"
                                    class="mb-3 hidden rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <span id="file-name-modal"
                                            class="flex items-center gap-2 text-sm text-slate-600"></span>
                                        <button type="button" @click="removeFile()"
                                            class="text-xl leading-none text-slate-400 transition hover:text-slate-700">&times;</button>
                                    </div>
                                </div>
                                <div class="flex items-end gap-3">
                                    <input type="file" id="file-input-modal" class="hidden"
                                        @change="handleFileSelect($event)">
                                    <button type="button" @click="openFilePicker()"
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-slate-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                    </button>
                                    <input type="text" id="message-input-modal" x-model="draftMessage"
                                        @keydown.enter.prevent="sendMessage()"
                                        class="h-12 flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none focus:border-slate-400 focus:ring-4 focus:ring-slate-200"
                                        placeholder="Write a message...">
                                    <button type="button" @click="sendMessage()"
                                        class="inline-flex h-12 shrink-0 items-center gap-2 rounded-2xl bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800">Send</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }

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

    <script src="https://cdn.jsdelivr.net/npm/axios@1.6.7/dist/axios.min.js"></script>
    <script>
        window.axios = axios;
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        window.axios.defaults.withCredentials = true;
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');

        function buildAttachmentHtml(fileUrl, fileName, isMe) {
            if (!fileUrl) return '';

            const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            const ext = fileUrl.split('.').pop().toLowerCase().split('?')[0];
            const isImage = imageExts.includes(ext);
            const safeName = fileName || 'Download file';

            if (isImage) {
                return `
                    <div style="margin-top:10px;">
                        <img src="${fileUrl}" alt="${safeName}" style="max-width:220px; max-height:220px; border-radius:16px; border:1px solid rgba(148,163,184,.25); display:block;">
                        <a href="${fileUrl}" download="${safeName}" style="display:inline-flex;align-items:center;gap:8px;margin-top:10px;padding:10px 12px;border-radius:14px;background:${isMe ? 'rgba(255,255,255,0.14)' : '#f8fafc'};color:${isMe ? '#fff' : '#0f172a'};text-decoration:none;font-size:12px;">Download</a>
                    </div>
                `;
            }

            return `
                <a href="${fileUrl}" download="${safeName}" style="display:inline-flex;align-items:center;gap:8px;margin-top:10px;padding:10px 12px;border-radius:14px;background:${isMe ? 'rgba(255,255,255,0.14)' : '#f8fafc'};color:${isMe ? '#fff' : '#0f172a'};text-decoration:none;font-size:12px;">Download file</a>
            `;
        }

        function adminMessagesApp(config) {
            return {
                users: [],
                search: '',
                loadingUsers: true,
                activeUserId: config.initialActiveUserId || 0,
                activeConversationId: config.initialConversationId || 0,
                activeUserName: config.initialOtherUserName || '',
                otherParticipantId: config.initialOtherParticipantId || 0,
                otherParticipantType: config.initialOtherParticipantType || '',
                draftMessage: '',
                showChat: Boolean(config.showChat),
                searchTimer: null,
                pollTimer: null,
                usersRefreshTimer: null,
                onlineStatusTimer: null,
                heartbeatTimer: null,
                lastMessageId: 0,
                loadedMessageIds: new Set(),
                lastMarkedReadAt: 0,
                init() {
                    this.loadUsers();
                    this.sendHeartbeat();
                    this.startHeartbeatLoop();
                    this.startUsersRefreshLoop();
                    window.addEventListener('message-counter-sync', () => this.loadUsers(false));
                    window.addEventListener('focus', () => this.loadUsers(false));
                    window.addEventListener('storage', (event) => {
                        if (event.key === 'message-counter-sync') this.loadUsers(false);
                    });
                    document.addEventListener('visibilitychange', () => {
                        if (document.visibilityState === 'visible') this.loadUsers(false);
                    });
                    if (this.activeConversationId) {
                        this.loadMessages();
                        this.startPolling();
                        this.checkOnlineStatus();
                        this.startOnlineStatusLoop();
                        this.showChat = true;
                    }
                },
                initialFor(name) {
                    return name ? name.charAt(0).toUpperCase() : '?';
                },
                debouncedLoadUsers() {
                    clearTimeout(this.searchTimer);
                    this.searchTimer = setTimeout(() => this.loadUsers(), 250);
                },
                startUsersRefreshLoop() {
                    if (this.usersRefreshTimer) clearInterval(this.usersRefreshTimer);
                    this.usersRefreshTimer = setInterval(() => this.loadUsers(false), 2000);
                },
                syncCounterState(reason = 'refresh') {
                    try {
                        const payload = JSON.stringify({
                            reason,
                            conversationId: this.activeConversationId || null,
                            userId: this.activeUserId || null,
                            at: Date.now(),
                        });
                        localStorage.setItem('message-counter-sync', payload);
                        window.dispatchEvent(new CustomEvent('message-counter-sync', {
                            detail: JSON.parse(payload)
                        }));
                    } catch (error) {
                        console.error('Counter sync error:', error);
                    }
                },
                async loadUsers(showLoader = true) {
                    if (showLoader) this.loadingUsers = true;
                    try {
                        const response = await fetch(`/admin/users/list?q=${encodeURIComponent(this.search.trim())}`, {
                            credentials: 'include',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });
                        const data = await response.json();
                        this.users = data.users || [];
                    } catch (error) {
                        console.error(error);
                        this.users = [];
                    } finally {
                        if (showLoader) this.loadingUsers = false;
                    }
                },
                async selectUser(user) {
                    this.showChat = true;
                    this.activeUserId = user.id;
                    this.activeUserName = user.name;
                    try {
                        const response = await axios.get(`{{ route('admin.messages.conversation') }}?user=${user.id}`);
                        this.activeConversationId = response.data.conversation.id;
                        this.otherParticipantId = response.data.other_participant.id || 0;
                        this.otherParticipantType = response.data.other_participant.type || '';
                        this.activeUserName = response.data.other_participant.name || user.name;
                        history.replaceState({}, '', `{{ route('admin.messages') }}?user=${user.id}`);
                        await this.loadMessages();
                        await this.loadUsers(false);
                        this.startPolling();
                        this.checkOnlineStatus();
                        this.startOnlineStatusLoop();
                    } catch (error) {
                        console.error('Conversation load error:', error);
                        this.showChat = false;
                    }
                },
                renderMessage(message) {
                    const isLargeScreen = window.innerWidth >= 1024; // lg breakpoint
                    const chatBox = isLargeScreen ? document.getElementById('chat-box') : document.getElementById(
                        'chat-box-modal');
                    if (!chatBox) return;
                    const typeShort = message.sender_type ? message.sender_type.split('\\').pop().toLowerCase() : '';
                    const isMe = message.sender_id == config.userId && typeShort === config.userTypeShort;
                    const row = document.createElement('div');
                    row.className = isMe ? 'message-row my-message' : 'message-row their-message';
                    const container = document.createElement('div');
                    container.className = 'message-container';
                    let content = message.body || '';
                    const fileUrl = message.file_url || '';
                    const fileName = message.file_name || 'Attachment';
                    content += buildAttachmentHtml(fileUrl, fileName, isMe);
                    const time = message.created_at ? new Date(message.created_at).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : '';
                    container.innerHTML = isMe ?
                        `<div class="message-bubble">${content}</div><div class="message-time">${time}</div>` :
                        `<div class="sender-name">${message.sender_name || this.activeUserName}</div><div class="message-bubble">${content}</div><div class="message-time">${time}</div>`;
                    row.appendChild(container);
                    chatBox.appendChild(row);
                    chatBox.scrollTop = chatBox.scrollHeight;
                },
                async loadMessages() {
                    if (!this.activeConversationId) return;
                    try {
                        const response = await axios.get(`/messages/${this.activeConversationId}?t=${Date.now()}`);
                        const messages = response.data.messages || [];
                        const chatBox = document.getElementById('chat-box');
                        const chatBoxModal = document.getElementById('chat-box-modal');
                        if (chatBox) chatBox.innerHTML = '';
                        if (chatBoxModal) chatBoxModal.innerHTML = '';
                        this.loadedMessageIds = new Set();
                        messages.forEach((message) => {
                            this.loadedMessageIds.add(message.id);
                            this.renderMessage(message);
                        });
                        this.lastMessageId = messages.length ? messages[messages.length - 1].id : 0;
                        await this.markConversationAsRead(true);
                        await this.loadUsers(false);
                    } catch (error) {
                        console.error('Messages load error:', error);
                    }
                },
                async sendMessage() {
                    if (!this.activeConversationId) return;
                    const isLargeScreen = window.innerWidth >= 1024;
                    const fileInput = isLargeScreen ? document.getElementById('file-input') : document.getElementById(
                        'file-input-modal');
                    const file = fileInput ? fileInput.files[0] : null;
                    const message = this.draftMessage.trim();
                    if (!message && !file) return;
                    const formData = new FormData();
                    if (message) formData.append('message', message);
                    if (file) formData.append('file', file);
                    formData.append('conversation_id', this.activeConversationId);
                    try {
                        const response = await axios.post('/send-message', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        });
                        this.draftMessage = '';
                        if (fileInput) fileInput.value = '';
                        const previewEl = isLargeScreen ? document.getElementById('file-preview') : document
                            .getElementById('file-preview-modal');
                        if (previewEl) previewEl.style.display = 'none';
                        this.loadedMessageIds.add(response.data.id);
                        this.lastMessageId = Math.max(this.lastMessageId, response.data.id);
                        this.renderMessage(response.data);
                        await this.loadUsers(false);
                        this.syncCounterState('sent');
                    } catch (error) {
                        console.error('Send error:', error);
                    }
                },
                openFilePicker() {
                    const isLargeScreen = window.innerWidth >= 1024;
                    const fileInput = isLargeScreen ? document.getElementById('file-input') : document.getElementById(
                        'file-input-modal');
                    if (fileInput) fileInput.click();
                },
                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const isLargeScreen = window.innerWidth >= 1024;
                    const nameEl = isLargeScreen ? document.getElementById('file-name') : document.getElementById(
                        'file-name-modal');
                    const previewEl = isLargeScreen ? document.getElementById('file-preview') : document.getElementById(
                        'file-preview-modal');
                    if (nameEl) nameEl.innerHTML = '';
                    if (file.type.startsWith('image/')) {
                        const img = document.createElement('img');
                        img.src = URL.createObjectURL(file);
                        img.style.maxWidth = '96px';
                        img.style.maxHeight = '96px';
                        img.style.borderRadius = '14px';
                        if (nameEl) nameEl.appendChild(img);
                    } else {
                        if (nameEl) nameEl.textContent = file.name;
                    }
                    if (previewEl) previewEl.style.display = 'block';
                },
                removeFile() {
                    const isLargeScreen = window.innerWidth >= 1024;
                    const fileInput = isLargeScreen ? document.getElementById('file-input') : document.getElementById(
                        'file-input-modal');
                    const previewEl = isLargeScreen ? document.getElementById('file-preview') : document.getElementById(
                        'file-preview-modal');
                    if (fileInput) fileInput.value = '';
                    if (previewEl) previewEl.style.display = 'none';
                },
                async markConversationAsRead(force = false) {
                    const now = Date.now();
                    if (!force && now - this.lastMarkedReadAt < 1500) return;
                    try {
                        await axios.post('/mark-read', {
                            conversation_id: this.activeConversationId
                        });
                        this.lastMarkedReadAt = now;
                        await this.loadUsers(false);
                        this.syncCounterState('read');
                    } catch (error) {
                        console.error('Mark read error:', error);
                    }
                },
                startPolling() {
                    if (this.pollTimer) clearInterval(this.pollTimer);
                    this.pollTimer = setInterval(async () => {
                        if (!this.activeConversationId) return;
                        try {
                            const response = await axios.get(
                                `/messages/${this.activeConversationId}?t=${Date.now()}`);
                            const messages = response.data.messages || [];
                            if (!messages.length) return;
                            const newestId = messages[messages.length - 1].id;
                            if (newestId > this.lastMessageId) {
                                messages.forEach((message) => {
                                    if (!this.loadedMessageIds.has(message.id)) {
                                        this.loadedMessageIds.add(message.id);
                                        this.renderMessage(message);
                                    }
                                });
                                this.lastMessageId = newestId;
                                await this.markConversationAsRead();
                                await this.loadUsers(false);
                            }
                        } catch (error) {
                            console.error('Poll error:', error);
                        }
                    }, 2000);
                },
                startHeartbeatLoop() {
                    if (this.heartbeatTimer) clearInterval(this.heartbeatTimer);
                    this.heartbeatTimer = setInterval(() => this.sendHeartbeat(), 8000);
                },
                startOnlineStatusLoop() {
                    if (this.onlineStatusTimer) clearInterval(this.onlineStatusTimer);
                    this.onlineStatusTimer = setInterval(() => this.checkOnlineStatus(), 5000);
                },
                async sendHeartbeat() {
                    try {
                        await axios.post('/online-heartbeat');
                    } catch (error) {
                        console.error('Heartbeat error:', error);
                    }
                },
                async checkOnlineStatus() {
                    if (!this.otherParticipantId || !this.otherParticipantType) return;
                    try {
                        const response = await axios.get(
                            `/online-status/${this.otherParticipantId}/${this.otherParticipantType}`);
                        const dot = document.getElementById('online-dot');
                        const text = document.getElementById('online-text');
                        if (dot && text) {
                            dot.style.background = response.data.online ? '#10b981' : '#94a3b8';
                            text.textContent = response.data.online ? 'Online' : 'Offline';
                        }
                    } catch (error) {
                        console.error('Online status error:', error);
                    }
                }
            }
        }
    </script>
</x-admin-layout>
