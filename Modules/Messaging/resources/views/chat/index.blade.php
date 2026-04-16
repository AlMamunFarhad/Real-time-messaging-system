<x-messaging::layouts.master>

    @php
        $participantId = \Modules\Messaging\Helpers\AuthParticipant::id();
        $participantType = \Modules\Messaging\Helpers\AuthParticipant::type();
        $participantTypeShort = strtolower(class_basename($participantType));

        $participants = $conversation->participants ?? collect();

        $otherParticipantId = $otherParticipant ? $otherParticipant->participant_id : 0;
        $otherTypeShort = '';
        if ($otherParticipant) {
            $resolvedOtherType = match ($otherParticipant->participant_type) {
                'admin' => \App\Models\Admin::class,
                'user' => \App\Models\User::class,
                default => $otherParticipant->participant_type,
            };

            $otherTypeShort = strtolower(class_basename($resolvedOtherType));
        }
    @endphp

    <div style="width: 100%; max-width: 900px; margin: 1.5rem auto; background: #ffffff; border-radius: 24px; border: 1px solid rgba(15, 23, 42, 0.08); overflow: hidden; box-shadow: 0 20px 40px -12px rgba(15, 23, 42, 0.12);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; padding: 1.25rem 1.5rem;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="height: 48px; width: 48px; border-radius: 14px; background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);">
                        <svg xmlns="http://www.w3.org/2000/svg" style="height: 1.5rem; width: 1.5rem; color: white;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <div>
                        <h3 style="font-weight: 700; font-size: 1.25rem; margin: 0;">{{ $otherUserName }}</h3>
                        <p style="font-size: 0.8rem; color: #94a3b8; margin: 0;">Conversation #{{ $conversation->id }}</p>
                    </div>
                </div>
                <div id="online-status" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; background: rgba(255,255,255,0.1); padding: 0.4rem 0.8rem; border-radius: 20px;">
                    <span id="online-dot" style="width: 8px; height: 8px; border-radius: 50%; background: #64748b; box-shadow: 0 0 8px rgba(100, 116, 139, 0.5);"></span>
                    <span id="online-text" style="color: #cbd5e1;">Offline</span>
                </div>
            </div>
        </div>

        <!-- Chat Body -->
        <div id="chat-box" style="height: 65vh; min-height: 450px; max-height: 65vh; overflow-y: auto; padding: 1.5rem; background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);">
            <div style="text-align: center; color: #64748b; padding: 2rem; font-size: 14px;">
                <svg xmlns="http://www.w3.org/2000/svg" style="width: 48px; height: 48px; margin-bottom: 12px; opacity: 0.4;" fill="none" viewBox="0 0 24 24" stroke="#94a3b8">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <p>Start your conversation</p>
            </div>
        </div>

        <!-- Input -->
        <div style="border-top: 1px solid #e2e8f0; background: white; padding: 1rem;">
            <!-- File Preview -->
            <div id="file-preview" style="display: none; margin-bottom: 0.5rem; padding: 0.5rem; background: #f3f4f6; border-radius: 8px;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span id="file-name" style="font-size: 0.875rem; color: #374151; display: flex; align-items: center; gap: 8px;"></span>
                    <button type="button" onclick="removeFile()" style="background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 1.25rem;">&times;</button>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <input type="file" id="file-input" style="display: none;" onchange="handleFileSelect(event)">
                <button type="button" onclick="document.getElementById('file-input').click()" style="background: #f3f4f6; color: #6b7280; padding: 0.75rem; border-radius: 50%; border: none; cursor: pointer;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="height: 1.25rem; width: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                </button>
                <input type="text" id="message-input" style="flex: 1; background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 9999px; padding: 0.75rem 1rem; color: #374151; font-size: 0.9375rem; outline: none;" placeholder="Type your message..." autocomplete="off">
                <button type="button" onclick="sendMessage()" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; padding: 0.75rem 1rem; border-radius: 9999px; border: none; cursor: pointer; transition: background 0.2s; white-space: nowrap;">
                    <span style="display: flex; align-items: center; gap: 0.25rem;">
                        Send
                        <svg xmlns="http://www.w3.org/2000/svg" style="height: 1rem; width: 1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </span>
                </button>
            </div>
        </div>

    </div>

    <script>
        window.conversationId = {{ $conversation->id }};
        window.otherParticipantId = {{ $otherParticipantId }};
        window.otherParticipantType = '{{ $otherTypeShort }}';
        window.userId = {{ $participantId ?? 0 }};
        window.userType = '{{ $participantType }}';
        window.userTypeShort = '{{ $participantTypeShort }}';
    </script>

    <script src="https://cdn.jsdelivr.net/npm/axios@1.6.7/dist/axios.min.js"></script>
    <script>
        window.axios = axios;
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        window.axios.defaults.withCredentials = true;
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
        }
    </script>

    <style>
        #chat-box {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        }
        .message-row {
            display: flex;
            width: 100%;
            margin-bottom: 20px;
            animation: fadeInUp 0.3s ease-out;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .message-row.my-message {
            justify-content: flex-end;
        }
        .message-row.their-message {
            justify-content: flex-start;
        }
        .message-container {
            max-width: 70%;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .message-row.my-message .message-container {
            align-items: flex-end;
        }
        .message-row.their-message .message-container {
            align-items: flex-start;
        }
        .sender-name {
            font-size: 12px;
            color: #f59e0b;
            font-weight: 600;
            margin-bottom: 6px;
            margin-left: 12px;
        }
        .message-bubble {
            padding: 14px 18px;
            border-radius: 20px;
            word-wrap: break-word;
            font-size: 14px;
            line-height: 1.5;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
        }
        /* User's message - Dark slate theme */
        .my-message .message-bubble {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            border-bottom-right-radius: 6px;
            position: relative;
        }
        .my-message .message-bubble::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: -8px;
            width: 0;
            height: 0;
            border: 8px solid transparent;
            border-top: 0;
            border-bottom: 12px solid #1e293b;
            border-left: 0;
            border-right: 0;
            transform: rotate(-45deg);
        }
        /* Admin/Other message - Clean white style */
        .their-message .message-bubble {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            color: #1e293b;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-bottom-left-radius: 6px;
            position: relative;
        }
        .their-message .message-bubble::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: -8px;
            width: 0;
            height: 0;
            border: 8px solid transparent;
            border-top: 0;
            border-bottom: 12px solid #f1f5f9;
            border-right: 0;
            transform: rotate(45deg);
        }
        .message-time {
            font-size: 11px;
            color: #64748b;
            margin-top: 6px;
            padding: 0 4px;
        }
        .my-message .message-time {
            color: #64748b;
        }
    </style>

    <script>
        window.loadedMessageIds = new Set();
        window.lastMessageId = 0;
        window.lastMarkedReadAt = 0;
        window.lastLoadTime = 0;
        window.loadDebounceMs = 2000;

        function isImage(url) {
            if (!url) return false;
            const ext = url.split('.').pop().toLowerCase();
            return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
        }

        function syncMessageCounter(reason = 'refresh') {
            try {
                const payload = JSON.stringify({
                    reason,
                    conversationId: window.conversationId,
                    at: Date.now(),
                });

                localStorage.setItem('message-counter-sync', payload);
                window.dispatchEvent(new CustomEvent('message-counter-sync', {
                    detail: JSON.parse(payload)
                }));
            } catch (error) {
                console.error('Message counter sync error:', error);
            }
        }

        async function markConversationAsRead(force = false) {
            const now = Date.now();

            if (!force && now - window.lastMarkedReadAt < 1500) {
                return;
            }

            try {
                const response = await axios.post('/mark-read', {
                    conversation_id: window.conversationId
                });

                window.lastMarkedReadAt = now;
                console.log('Marked conversation as read:', response.data);
                syncMessageCounter('read');
            } catch (error) {
                console.error('Mark read error:', error);
            }
        }

        window.appendMessage = function(message) {
            let chatBox = document.getElementById('chat-box');
            if (!chatBox) return;

            let messageTypeShort = message.sender_type ? message.sender_type.split('\\').pop().toLowerCase() : '';
            let isMe = (message.sender_id == window.userId && messageTypeShort === window.userTypeShort);

            let time = message.created_at ? new Date(message.created_at).toLocaleTimeString() : new Date().toLocaleTimeString();
            let senderName = message.sender_name || (isMe ? 'You' : 'Unknown');

            let row = document.createElement('div');
            row.className = isMe ? 'message-row my-message' : 'message-row their-message';

            let container = document.createElement('div');
            container.className = 'message-container';

            let fileUrl = message.file_url || message.fileUrl || '';
            let hasImage = fileUrl && (fileUrl.endsWith('.jpg') || fileUrl.endsWith('.jpeg') || fileUrl.endsWith('.png') || fileUrl.endsWith('.gif') || fileUrl.endsWith('.webp'));

            if (isMe) {
                let content = message.body || '';
                if (hasImage) {
                    content += `<img src="${fileUrl}" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                    <a href="${fileUrl}" download style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 10px; margin-top: 6px; background: rgba(255,255,255,0.3); border-radius: 6px; text-decoration: none; font-size: 11px; color: white;">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download
                    </a>`;
                } else if (fileUrl) {
                    content += `<a href="${fileUrl}" target="_blank" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: rgba(255,255,255,0.2); border-radius: 8px; text-decoration: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        <span style="color: white; font-size: 12px;">Download File</span>
                    </a>`;
                }
                container.innerHTML = `
                    <div class="message-bubble">${content}</div>
                    <div class="message-time">${time} ✓</div>`;
            } else {
                let content = message.body || '';
                if (hasImage) {
                    content += `<img src="${fileUrl}" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 1px solid #e5e7eb;">
                    <a href="${fileUrl}" download style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 10px; margin-top: 6px; background: #e5e7eb; border-radius: 6px; text-decoration: none; font-size: 11px; color: #374151;">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download
                    </a>`;
                } else if (fileUrl) {
                    content += `<a href="${fileUrl}" target="_blank" style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: #e5e7eb; border-radius: 8px; text-decoration: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 20px; height: 20px; color: #6b7280;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        <span style="color: #374151; font-size: 12px;">Download File</span>
                    </a>`;
                }
                container.innerHTML = `
                    <div class="sender-name">${senderName}</div>
                    <div class="message-bubble">${content}</div>
                    <div class="message-time">${time}</div>`;
            }

            row.appendChild(container);
            chatBox.appendChild(row);
            chatBox.scrollTop = chatBox.scrollHeight;
        };

        window.isLoadingMessages = false;

        async function loadMessages() {
            if (window.isLoadingMessages) return;
            const now = Date.now();
            if (now - window.lastLoadTime < window.loadDebounceMs) return;
            window.lastLoadTime = now;
            window.isLoadingMessages = true;
            try {
                const response = await axios.get('/messages/' + window.conversationId + '?t=' + now);
                console.log('Load messages response:', response.data);
                const messages = response.data.messages || [];
                messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                
                const isFirstLoad = window.loadedMessageIds.size === 0;
                messages.forEach(msg => {
                    if (!window.loadedMessageIds.has(msg.id)) {
                        window.loadedMessageIds.add(msg.id);
                        window.appendMessage(msg);
                    }
                });
                if (window.loadedMessageIds.size > 0) {
                    window.lastMessageId = Math.max(...Array.from(window.loadedMessageIds));
                }
                await markConversationAsRead(true);
                console.log('Loaded messages. Total loaded:', window.loadedMessageIds.size, 'Last ID:', window.lastMessageId);
            } catch (error) {
                console.error('Load error:', error);
            } finally {
                window.isLoadingMessages = false;
            }
        }

        window.sendMessage = async function() {
            let input = document.getElementById('message-input');
            let message = input.value.trim();
            let fileInput = document.getElementById('file-input');
            let file = fileInput.files[0];
            
            if (!message && !file) return;
            
            input.disabled = true;
            let formData = new FormData();
            if (message) formData.append('message', message);
            if (file) formData.append('file', file);
            formData.append('conversation_id', window.conversationId);
            
            console.log('Sending message:', { message, hasFile: !!file, conversationId: window.conversationId });
            
            try {
                const response = await axios.post('/send-message', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                console.log('Send response:', response.data);
                input.value = '';
                fileInput.value = '';
                document.getElementById('file-preview').style.display = 'none';
                if (response.data && response.data.id) {
                    window.loadedMessageIds.add(response.data.id);
                    window.lastMessageId = Math.max(window.lastMessageId, response.data.id);
                    window.appendMessage(response.data);
                    syncMessageCounter('sent');
                }
            } catch (error) {
                console.error('Send error:', error);
                alert('Failed to send message');
            } finally {
                input.disabled = false;
                input.focus();
            }
        };

        function isImageFile(file) {
            if (!file) return false;
            const ext = file.name.split('.').pop().toLowerCase();
            return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
        }

        window.handleFileSelect = function(event) {
            let file = event.target.files[0];
            if (file) {
                const preview = document.getElementById('file-preview');
                const nameEl = document.getElementById('file-name');
                nameEl.innerHTML = '';
                
                if (isImageFile(file)) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.style.maxWidth = '100px';
                    img.style.maxHeight = '100px';
                    img.style.borderRadius = '8px';
                    nameEl.appendChild(img);
                } else {
                    nameEl.textContent = file.name;
                }
                preview.style.display = 'block';
            }
        };

        window.removeFile = function() {
            document.getElementById('file-input').value = '';
            document.getElementById('file-preview').style.display = 'none';
        };

        loadMessages();

        // Poll every 3 seconds with debounce
        let pollInterval;
        const startPolling = () => {
            pollInterval = setInterval(async () => {
                if (window.isLoadingMessages) return;
                const now = Date.now();
                if (now - window.lastLoadTime < window.loadDebounceMs) return;
                window.lastLoadTime = now;
                window.isLoadingMessages = true;
                try {
                const response = await axios.get('/messages/' + window.conversationId + '?t=' + Date.now());
                console.log('Poll response:', response.data);
                const messages = response.data.messages || [];
                if (messages.length > 0) {
                    messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                    const newMessageId = messages[messages.length - 1].id;
                    console.log('Last message ID:', newMessageId, 'Prev last:', window.lastMessageId);
                    if (newMessageId > window.lastMessageId) {
                        messages.forEach(msg => {
                            if (!window.loadedMessageIds.has(msg.id)) {
                                window.loadedMessageIds.add(msg.id);
                                let senderTypeShort = msg.sender_type ? msg.sender_type.split('\\').pop().toLowerCase() : '';
                                // Check if sender is SAME person (both id AND type must match)
                                let isSameUser = (msg.sender_id == window.userId && senderTypeShort === window.userTypeShort);
                                console.log('Message', msg.id, 'from:', senderTypeShort, 'to:', window.userTypeShort, 'sameUser:', isSameUser);
                                if (!isSameUser) {
                                    window.appendMessage(msg);
                                }
                            }
                        });
                        window.lastMessageId = newMessageId;
                        await markConversationAsRead();
                    }
                }
            } catch (e) {
                console.error('Poll error:', e);
            } finally {
                window.isLoadingMessages = false;
            }
        }, 3000);
        };
        startPolling();

        const input = document.getElementById('message-input');
        if (input) {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    window.sendMessage();
                }
            });
        }

        const otherParticipantId = window.otherParticipantId;
        const otherParticipantType = window.otherParticipantType;

        async function checkOnlineStatus() {
            if (!otherParticipantId || !otherParticipantType) return;
            try {
                const response = await axios.get(`/online-status/${otherParticipantId}/${otherParticipantType}`);
                const isOnline = response.data.online;
                const dot = document.getElementById('online-dot');
                const text = document.getElementById('online-text');
                if (dot && text) {
                    if (isOnline) {
                        dot.style.background = '#10b981';
                        dot.style.boxShadow = '0 0 10px #10b981';
                        text.textContent = 'Online';
                    } else {
                        dot.style.background = '#64748b';
                        dot.style.boxShadow = '0 0 8px rgba(100, 116, 139, 0.5)';
                        text.textContent = 'Offline';
                    }
                }
            } catch (error) {}
        }

        async function sendHeartbeat() {
            try {
                await axios.post('/online-heartbeat');
            } catch (error) {}
        }

        checkOnlineStatus();
        setInterval(checkOnlineStatus, 5000);
        setInterval(sendHeartbeat, 8000);
        window.addEventListener('focus', () => markConversationAsRead(true));
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                markConversationAsRead(true);
            }
        });
    </script>

</x-messaging::layouts.master>
