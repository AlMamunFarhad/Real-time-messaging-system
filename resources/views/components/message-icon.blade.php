@php
    $userId = $currentUserId ?? AuthParticipant::id();
    $userType = $currentUserType ?? AuthParticipant::type();
    $userTypeShort = $userType ? strtolower(class_basename($userType)) : null;
    $unread = $unreadCount ?? 0;
    $isAdminDashboard = $isAdminDashboard ?? ($userTypeShort === 'admin');
    $currentUserName = 'User';

    if ($userType && $userId) {
        $user = $userType::find($userId);
        if ($user) {
            $currentUserName = $user->name ?? ($user->email ?? 'User #' . $userId);
        }
    }
@endphp

@if ($isAdminDashboard)
    <a href="{{ route('admin.messages') }}" class="relative inline-flex p-2 text-gray-600 transition-colors duration-200 hover:text-blue-600">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        @if ($unread > 0)
            <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">
                {{ $unread > 9 ? '9+' : $unread }}
            </span>
        @endif
    </a>
@elseif (\App\Models\Admin::first())
    <div
        x-data="userMessagePanel({
            unreadCount: {{ $unread }},
            userId: {{ $userId ?? 0 }},
            userTypeShort: @js($userTypeShort),
            conversationUrl: @js(route('user.messages.conversation'))
        })"
        x-init="init()"
        class="relative"
    >
        <button @click="openChat()" class="relative p-2 text-gray-600 transition-colors duration-200 hover:text-blue-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <template x-if="unreadCount > 0">
                <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white" x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
            </template>
        </button>

        <div x-show="open" x-transition.opacity class="fixed inset-0 z-[70] bg-slate-950/45 backdrop-blur-sm" style="display:none;">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div @click.outside="closeChat()" class="flex h-[85vh] w-full max-w-5xl flex-col overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_24px_80px_-28px_rgba(15,23,42,0.45)]">
                    <div class="border-b border-slate-200 bg-white px-6 py-5">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-300 text-slate-900">
                                    <span class="text-base font-semibold" x-text="otherParticipantName ? otherParticipantName.charAt(0).toUpperCase() : 'A'"></span>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-slate-900" x-text="otherParticipantName"></h3>
                                    <p class="text-xs text-slate-500">Direct chat</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-500">
                                    <span id="user-online-dot" class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                                    <span id="user-online-text">Offline</span>
                                </div>
                                <button type="button" @click="closeChat()" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-50">Close</button>
                            </div>
                        </div>
                    </div>

                    <div id="user-chat-box" class="flex-1 overflow-y-auto bg-[radial-gradient(circle_at_top,#f8fafc_0%,#ffffff_42%,#f8fafc_100%)] px-6 py-6">
                        <template x-if="loadingChat">
                            <div class="space-y-3">
                                <div class="h-14 w-2/3 animate-pulse rounded-3xl bg-white"></div>
                                <div class="ml-auto h-14 w-1/2 animate-pulse rounded-3xl bg-slate-200"></div>
                            </div>
                        </template>
                        <div id="user-chat-empty-note" x-show="!loadingChat" class="rounded-3xl border border-dashed border-slate-300 bg-white/80 px-6 py-10 text-center text-slate-500">
                            <p class="text-sm font-medium">Chat load ??? ?????</p>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 bg-white px-6 py-4">
                        <div id="user-file-preview" style="display:none;" class="mb-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <span id="user-file-name" class="text-sm text-slate-600"></span>
                                <button type="button" @click="removeFile()" class="text-xl leading-none text-slate-400 transition hover:text-slate-700">&times;</button>
                            </div>
                        </div>
                        <div class="flex items-end gap-3">
                            <input type="file" id="user-file-input" class="hidden" @change="handleFileSelect($event)">
                            <button type="button" @click="document.getElementById('user-file-input').click()" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-slate-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                            </button>
                            <input type="text" id="user-message-input" @keydown.enter.prevent="sendMessage()" class="h-12 flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none focus:border-slate-400 focus:ring-4 focus:ring-slate-200" placeholder="Write a message...">
                            <button type="button" @click="sendMessage()" class="inline-flex h-12 shrink-0 items-center gap-2 rounded-2xl bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800">Send</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .message-row { display:flex; width:100%; margin-bottom:18px; }
        .message-row.my-message { justify-content:flex-end; }
        .message-row.their-message { justify-content:flex-start; }
        .message-container { max-width:min(78%,680px); display:flex; flex-direction:column; }
        .message-row.my-message .message-container { align-items:flex-end; }
        .message-bubble { border-radius:22px; padding:14px 16px; font-size:14px; line-height:1.6; word-break:break-word; box-shadow:0 18px 35px -26px rgba(15,23,42,.45); }
        .my-message .message-bubble { background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%); color:#fff; border-bottom-right-radius:8px; }
        .their-message .message-bubble { background:#fff; color:#0f172a; border:1px solid rgba(148,163,184,.28); border-bottom-left-radius:8px; }
        .sender-name { margin-bottom:6px; padding-left:8px; font-size:12px; font-weight:600; color:#475569; }
        .message-time { margin-top:6px; padding:0 6px; font-size:11px; color:#64748b; }
    </style>

    <script>
        function buildUserAttachmentHtml(fileUrl, fileName, isMe) {
            if (!fileUrl) return '';

            const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            const ext = fileUrl.split('.').pop().toLowerCase().split('?')[0];
            const isImage = imageExts.includes(ext);
            const safeName = fileName || 'Download file';

            if (isImage) {
                return `
                    <div style="margin-top:10px;">
                        <img src="${fileUrl}" alt="${safeName}" style="max-width:220px; max-height:220px; border-radius:16px; border:1px solid rgba(148,163,184,.25); display:block;">
                        <a href="${fileUrl}" download="${safeName}" style="display:inline-flex;align-items:center;gap:8px;margin-top:10px;padding:10px 12px;border-radius:14px;background:${isMe ? 'rgba(255,255,255,0.14)' : '#f8fafc'};color:${isMe ? '#fff' : '#0f172a'};text-decoration:none;font-size:12px;">Download image</a>
                    </div>
                `;
            }

            return `
                <a href="${fileUrl}" download="${safeName}" style="display:inline-flex;align-items:center;gap:8px;margin-top:10px;padding:10px 12px;border-radius:14px;background:${isMe ? 'rgba(255,255,255,0.14)' : '#f8fafc'};color:${isMe ? '#fff' : '#0f172a'};text-decoration:none;font-size:12px;">Download file</a>
            `;
        }

        function userMessagePanel(config) {
            return {
                open: false,
                unreadCount: config.unreadCount || 0,
                loadingChat: false,
                activeConversationId: null,
                otherParticipantId: null,
                otherParticipantType: 'admin',
                otherParticipantName: 'Admin',
                lastMessageId: 0,
                loadedMessageIds: new Set(),
                lastMarkedReadAt: 0,
                pollTimer: null,
                init() {
                    this.refreshConversations();
                    window.addEventListener('message-counter-sync', () => this.refreshConversations());
                    window.addEventListener('storage', (event) => {
                        if (event.key === 'message-counter-sync') this.refreshConversations();
                    });
                },
                async refreshConversations() {
                    try {
                        const response = await fetch('{{ route('messages.conversations') }}?t=' + Date.now(), { credentials: 'include' });
                        if (response.ok) {
                            const data = await response.json();
                            this.unreadCount = data.unread_count || 0;
                        }
                    } catch (error) {
                        console.error(error);
                    }
                },
                async openChat() {
                    this.open = true;
                    this.loadingChat = true;
                    try {
                        const response = await axios.get(config.conversationUrl);
                        this.activeConversationId = response.data.conversation.id;
                        this.otherParticipantId = response.data.other_participant.id;
                        this.otherParticipantType = response.data.other_participant.type || 'admin';
                        this.otherParticipantName = response.data.other_participant.name || 'Admin';
                        await this.loadMessages();
                        this.startPolling();
                        this.checkOnlineStatus();
                    } catch (error) {
                        console.error('Open chat error:', error);
                    } finally {
                        this.loadingChat = false;
                    }
                },
                closeChat() {
                    this.open = false;
                    if (this.pollTimer) {
                        clearInterval(this.pollTimer);
                        this.pollTimer = null;
                    }
                },
                async markConversationAsRead(force = false) {
                    const now = Date.now();
                    if (!force && now - this.lastMarkedReadAt < 1500) return;
                    try {
                        await axios.post('/mark-read', { conversation_id: this.activeConversationId });
                        this.lastMarkedReadAt = now;
                        this.refreshConversations();
                    } catch (error) {
                        console.error(error);
                    }
                },
                renderMessage(message) {
                    const chatBox = document.getElementById('user-chat-box');
                    if (!chatBox) return;
                    const note = document.getElementById('user-chat-empty-note');
                    if (note) note.remove();
                    const senderTypeShort = message.sender_type ? message.sender_type.split('\\').pop().toLowerCase() : '';
                    const isMe = (message.sender_id == config.userId && senderTypeShort === config.userTypeShort);
                    const row = document.createElement('div');
                    row.className = isMe ? 'message-row my-message' : 'message-row their-message';
                    const container = document.createElement('div');
                    container.className = 'message-container';
                    let content = message.body || '';
                    const fileUrl = message.file_url || '';
                    const fileName = message.file_name || 'Attachment';
                    content += buildUserAttachmentHtml(fileUrl, fileName, isMe);
                    const time = message.created_at ? new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
                    container.innerHTML = isMe
                        ? `<div class="message-bubble">${content}</div><div class="message-time">${time}</div>`
                        : `<div class="sender-name">${message.sender_name || this.otherParticipantName}</div><div class="message-bubble">${content}</div><div class="message-time">${time}</div>`;
                    row.appendChild(container);
                    chatBox.appendChild(row);
                    chatBox.scrollTop = chatBox.scrollHeight;
                },
                async loadMessages() {
                    if (!this.activeConversationId) return;
                    try {
                        const response = await axios.get('/messages/' + this.activeConversationId + '?t=' + Date.now());
                        const messages = response.data.messages || [];
                        const chatBox = document.getElementById('user-chat-box');
                        if (chatBox) chatBox.innerHTML = '';
                        this.loadedMessageIds = new Set();
                        messages.forEach((msg) => {
                            this.loadedMessageIds.add(msg.id);
                            this.renderMessage(msg);
                        });
                        this.lastMessageId = messages.length ? messages[messages.length - 1].id : 0;
                        await this.markConversationAsRead(true);
                    } catch (error) {
                        console.error(error);
                    }
                },
                async sendMessage() {
                    if (!this.activeConversationId) return;
                    const input = document.getElementById('user-message-input');
                    const fileInput = document.getElementById('user-file-input');
                    const file = fileInput.files[0];
                    const message = input.value.trim();
                    if (!message && !file) return;
                    input.disabled = true;
                    const formData = new FormData();
                    if (message) formData.append('message', message);
                    if (file) formData.append('file', file);
                    formData.append('conversation_id', this.activeConversationId);
                    try {
                        const response = await axios.post('/send-message', formData, {
                            headers: { 'Content-Type': 'multipart/form-data' }
                        });
                        input.value = '';
                        fileInput.value = '';
                        document.getElementById('user-file-preview').style.display = 'none';
                        if (response.data && response.data.id) {
                            this.loadedMessageIds.add(response.data.id);
                            this.lastMessageId = Math.max(this.lastMessageId, response.data.id);
                            this.renderMessage(response.data);
                        }
                    } catch (error) {
                        console.error(error);
                    } finally {
                        input.disabled = false;
                        input.focus();
                    }
                },
                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const nameEl = document.getElementById('user-file-name');
                    nameEl.innerHTML = '';
                    if (file.type.startsWith('image/')) {
                        const img = document.createElement('img');
                        img.src = URL.createObjectURL(file);
                        img.style.maxWidth = '96px';
                        img.style.maxHeight = '96px';
                        img.style.borderRadius = '14px';
                        nameEl.appendChild(img);
                    } else {
                        nameEl.textContent = file.name;
                    }
                    document.getElementById('user-file-preview').style.display = 'block';
                },
                removeFile() {
                    document.getElementById('user-file-input').value = '';
                    document.getElementById('user-file-preview').style.display = 'none';
                },
                startPolling() {
                    if (this.pollTimer) clearInterval(this.pollTimer);
                    this.pollTimer = setInterval(async () => {
                        if (!this.activeConversationId || !this.open) return;
                        try {
                            const response = await axios.get('/messages/' + this.activeConversationId + '?t=' + Date.now());
                            const messages = response.data.messages || [];
                            if (!messages.length) return;
                            const newestId = messages[messages.length - 1].id;
                            if (newestId > this.lastMessageId) {
                                messages.forEach((msg) => {
                                    if (!this.loadedMessageIds.has(msg.id)) {
                                        this.loadedMessageIds.add(msg.id);
                                        this.renderMessage(msg);
                                    }
                                });
                                this.lastMessageId = newestId;
                                await this.markConversationAsRead();
                            }
                        } catch (error) {
                            console.error(error);
                        }
                    }, 2000);
                },
                async checkOnlineStatus() {
                    if (!this.otherParticipantId || !this.otherParticipantType) return;
                    try {
                        const response = await axios.get('/online-status/' + this.otherParticipantId + '/' + this.otherParticipantType);
                        const dot = document.getElementById('user-online-dot');
                        const text = document.getElementById('user-online-text');
                        if (dot && text) {
                            dot.style.background = response.data.online ? '#10b981' : '#94a3b8';
                            text.textContent = response.data.online ? 'Online' : 'Offline';
                        }
                    } catch (error) {
                        console.error(error);
                    }
                }
            };
        }
    </script>
@endif
