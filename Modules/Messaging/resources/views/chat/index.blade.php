<x-messaging::layouts.master>

    {{-- ================== PHP LOGIC FIRST ================== --}}
    @php
        $participantId = \Modules\Messaging\Helpers\AuthParticipant::id();
        $participantType = \Modules\Messaging\Helpers\AuthParticipant::type();
        $participantTypeShort = strtolower(class_basename($participantType));

        $currentUserName = $currentUserName ?? 'User';

        $participants = $conversation->participants ?? collect();

        // Debug - remove in production
        // dd($participants->toArray(), $participantId, $participantType);

        $otherParticipant = null;
        foreach ($participants as $p) {
            $pTypeShort = strtolower(class_basename($p->participant_type ?? ''));
            // Find the participant who is NOT the current logged in user
            if ($p->participant_id != $participantId || $pTypeShort != $participantTypeShort) {
                $otherParticipant = $p;
                break;
            }
        }

        $otherParticipantId = $otherParticipant ? $otherParticipant->participant_id : 0;

        $otherTypeShort = '';
        if ($otherParticipant) {
            $otherTypeShort = strtolower(class_basename($otherParticipant->participant_type));
        }

        // Get other participant's actual name
$otherUserName = 'User';
if ($otherParticipant && $otherParticipant->participant_type) {
    $otherUser = $otherParticipant->participant_type::find($otherParticipant->participant_id);
    if ($otherUser) {
        $otherUserName =
            $otherUser->name ?? ($otherUser->email ?? 'User #' . $otherParticipant->participant_id);
    }
}

// Debug to see what's happening
        // dd($otherParticipantId, $otherTypeShort, $otherParticipant ? $otherParticipant->toArray() : null);

    @endphp

    <div
        style="width: 100%; max-width: 800px; margin: 1rem auto; background: white; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border-radius: 1rem; border: 1px solid #e5e7eb; overflow: hidden;">

        <!-- Header -->
        <div
            style="background: linear-gradient(135deg, #1d4ed8 0%, #4338ca 100%); color: white; padding: 1rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div
                    style="height: 2.5rem; width: 2.5rem; border-radius: 9999px; background: rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="height: 1.5rem; width: 1.5rem; color: white;"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <div>
                    <h3 style="font-weight: 600; font-size: 1.125rem;">{{ $otherUserName }}
                    </h3>
                    <p style="font-size: 0.75rem; color: #bfdbfe;">Conversation #{{ $conversation->id }}</p>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem;" id="online-status">
                <span id="online-dot" class="online-indicator"></span>
                <span id="online-text">Offline</span>
            </div>
        </div>

        <!-- Chat Body -->
        <div id="chat-box"
            style="height: 60vh; min-height: 400px; overflow-y: auto; padding: 1rem; background: linear-gradient(180deg, #f9fafb 0%, #f3f4f6 100%); display: flex; flex-direction: column;">
            <!-- Messages appear here -->
        </div>

        <!-- Input -->
        <div style="border-top: 1px solid #e5e7eb; background: white; padding: 1rem;">
            <div
                style="display: flex; align-items: center; gap: 0.75rem; background: #f3f4f6; border-radius: 9999px; padding: 0.5rem 1rem; border: 1px solid #e5e7eb;">
                <input type="text" id="message-input"
                    style="flex: 1; background: transparent; border: none; outline: none; color: #374151; font-size: 0.9375rem; padding: 0.25rem;"
                    placeholder="Type your message..." autocomplete="off">

                <button type="button" onclick="sendMessage()"
                    style="background: #2563eb; color: white; padding: 0.5rem 0.75rem; border-radius: 9999px; border: none; cursor: pointer; transition: background 0.2s; display: flex; align-items: center; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        style="height: 1.25rem; width: 1.25rem; transform: rotate(90deg);" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </div>
        </div>

    </div>

    {{-- ================== JS VARIABLES ================== --}}
    <script>
        // All variables on window so Vite modules (chat.js) can access them
        window.conversationId = {{ $conversation->id }};
        window.otherParticipantId = {{ $otherParticipantId }};
        window.otherParticipantType = '{{ $otherTypeShort }}';

        window.userId = {{ $participantId ?? 0 }};
        window.userType = '{{ $participantType }}';
        window.uniqueUserId = '{{ $participantTypeShort }}_{{ $participantId }}';
        window.currentUserName = '{{ $currentUserName }}';

        console.log('Chat initialized - conversationId:', window.conversationId, 'userId:', window.userId);
    </script>

    {{-- Load axios via CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/axios@1.6.7/dist/axios.min.js"></script>
    <script>
        // Set up axios defaults
        window.axios = axios;
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        window.axios.defaults.withCredentials = true;
        
        // Set CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
            console.log('CSRF token found');
        } else {
            console.log('WARNING: CSRF token not found!');
        }
    </script>

    {{-- Basic Chat Application (Inline - No Dependencies) --}}
    <script>
        console.log('Starting chat application...');
        
        // Variables
        window.loadedMessageIds = new Set();
        window.lastMessageId = 0;
        
        // Function to append message to chat
        window.appendMessage = function(message, append = true, isNew = false) {
            let chatBox = document.getElementById('chat-box');
            if (!chatBox) {
                console.error('Chat box not found!');
                return;
            }
            
            // Compare both sender_id AND sender_type to determine if it's my message
            let senderTypeShort = message.sender_type ? message.sender_type.split('\\').pop().toLowerCase() : '';
            let myTypeShort = window.userType ? window.userType.split('\\').pop().toLowerCase() : '';
            
            // My message if sender_id matches AND (sender_type matches OR types are same)
            let isMe = (message.sender_id == window.userId && (message.sender_type === window.userType || senderTypeShort === myTypeShort));
            
            let time = message.created_at ? new Date(message.created_at).toLocaleTimeString() : new Date().toLocaleTimeString();
            let senderName = message.sender_name || (isMe ? (window.currentUserName || 'You') : 'Unknown');
            
            console.log('Appending message:', isMe ? 'MY message' : 'THEIR message', 'sender:', senderName, 'sender_id:', message.sender_id, 'userId:', window.userId);
            
            let div = document.createElement('div');
            div.style.display = 'flex';
            div.style.width = '100%';
            div.style.marginBottom = '0.75rem';
            div.style.justifyContent = isMe ? 'flex-end' : 'flex-start';
            div.dataset.messageId = message.id;
            
            if (isMe) {
                div.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: flex-end; max-width: 75%;">
                        <div style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; padding: 0.75rem 1rem; border-radius: 1rem 1rem 0.25rem 1rem;">
                            <span style="font-size: 0.9375rem; color: white; word-wrap: break-word;">${message.body}</span>
                        </div>
                        <div style="font-size: 0.6875rem; color: #9ca3af; margin-top: 0.25rem;">${time}</div>
                    </div>`;
            } else {
                div.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: flex-start; max-width: 75%;">
                        <span style="font-size: 0.6875rem; color: #6b7280; margin-bottom: 0.25rem; font-weight: 500;">${senderName}</span>
                        <div style="background: white; color: #1f2937; padding: 0.75rem 1rem; border-radius: 1rem 1rem 1rem 0.25rem; border: 1px solid #e5e7eb;">
                            <span style="font-size: 0.9375rem; color: #374151; word-wrap: break-word;">${message.body}</span>
                        </div>
                        <div style="font-size: 0.6875rem; color: #9ca3af; margin-top: 0.25rem;">${time}</div>
                    </div>`;
            }
            
            chatBox.appendChild(div);
            chatBox.scrollTop = chatBox.scrollHeight;
        };
        
        // Load messages from API
        async function loadMessages() {
            console.log('Loading messages for conversation:', window.conversationId);
            try {
                const response = await axios.get('/messages/' + window.conversationId);
                console.log('Messages API response:', response.data);
                
                const messages = response.data.messages || [];
                console.log('Got', messages.length, 'messages');
                
                messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                
                messages.forEach(msg => {
                    if (!window.loadedMessageIds.has(msg.id)) {
                        window.loadedMessageIds.add(msg.id);
                        window.appendMessage(msg, true);
                    }
                });
                
                if (window.loadedMessageIds.size > 0) {
                    window.lastMessageId = Math.max(...Array.from(window.loadedMessageIds));
                    console.log('Last message ID set to:', window.lastMessageId);
                }
                
                console.log('Loaded', window.loadedMessageIds.size, 'messages total');
            } catch (error) {
                console.error('Load messages error:', error);
                if (error.response) {
                    console.error('Status:', error.response.status, 'Data:', error.response.data);
                }
            }
        }
        
        // Send message function
        window.sendMessage = async function() {
            let input = document.getElementById('message-input');
            let message = input.value.trim();
            
            if (!message) return;
            
            console.log('Sending message:', message);
            input.disabled = true;
            
            try {
                const response = await axios.post('/send-message', {
                    message: message,
                    conversation_id: window.conversationId
                });
                
                console.log('Send response:', response.data);
                input.value = '';
                
                if (response.data && response.data.id) {
                    window.loadedMessageIds.add(response.data.id);
                    window.lastMessageId = Math.max(window.lastMessageId, response.data.id);
                    window.appendMessage(response.data, true, true);
                    console.log('Message displayed, ID:', response.data.id);
                }
            } catch (error) {
                alert('Failed to send message');
                console.error('Send error:', error);
            } finally {
                input.disabled = false;
                input.focus();
            }
        };
        
        // Initialize
        console.log('Initializing chat...');
        console.log('User ID:', window.userId);
        console.log('Conversation ID:', window.conversationId);
        
        // Load existing messages
        loadMessages();
        
        // Poll for new messages every 2 seconds (faster for real-time feel)
        setInterval(async () => {
            try {
                const response = await axios.get('/messages/' + window.conversationId);
                const messages = response.data.messages || [];
                
                if (messages.length > 0) {
                    messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                    const latestMessage = messages[messages.length - 1];
                    const newMessageId = latestMessage.id;
                    
                    console.log('Polling - latest ID:', newMessageId, 'last ID:', window.lastMessageId);
                    
                    if (newMessageId > window.lastMessageId) {
                        console.log('>>> NEW MESSAGE FOUND! <<<');
                        
                        messages.forEach(msg => {
                            if (!window.loadedMessageIds.has(msg.id)) {
                                window.loadedMessageIds.add(msg.id);
                                console.log('Adding new message:', msg.id, msg.body.substring(0, 20));
                                
                                // Check if it's NOT my message using sender_type comparison
                                let senderTypeShort = msg.sender_type ? msg.sender_type.split('\\').pop().toLowerCase() : '';
                                let myTypeShort = window.userType ? window.userType.split('\\').pop().toLowerCase() : '';
                                let isMyMessage = (msg.sender_id == window.userId && (msg.sender_type === window.userType || senderTypeShort === myTypeShort));
                                
                                if (!isMyMessage) {
                                    window.appendMessage(msg, true, true);
                                }
                            }
                        });
                        
                        window.lastMessageId = newMessageId;
                    }
                }
            } catch (error) {
                console.log('Polling error:', error.message);
            }
        }, 2000);
        
        // Enter key handler
        const input = document.getElementById('message-input');
        if (input) {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    window.sendMessage();
                }
            });
        }
        
        console.log('Chat initialized successfully');
    </script>

    {{-- ================== INLINE STYLE (SAFE) ================== --}}
    <style>
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {

            0%,
            60%,
            100% {
                transform: translateY(0);
            }

            30% {
                transform: translateY(-4px);
            }
        }

        .animate-slide-in {
            animation: slideIn 0.3s ease-out;
        }

        .online-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #9ca3af;
            /* Default grey for offline */
            margin-right: 0.5rem;
        }
    </style>

    {{-- ================== INLINE SCRIPT (SAFE) ================== --}}
    {{-- <script>
        console.log('Chat Ready');
    </script> --}}

</x-messaging::layouts.master>
