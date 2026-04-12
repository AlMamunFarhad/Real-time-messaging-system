window.appendMessage = function (message, append = true, isNew = false) {
    let chatBox = document.getElementById('chat-box');
    if (!chatBox) return;

    let isMe = message.sender_id == window.userId;

    let time = message.created_at ? new Date(message.created_at).toLocaleTimeString() : new Date().toLocaleTimeString();
    let senderName = message.sender_name || (isMe ? 'You' : 'Unknown');

    let div = document.createElement('div');
    div.style.display = 'flex';
    div.style.width = '100%';
    div.style.marginBottom = '0.75rem';
    div.style.justifyContent = isMe ? 'flex-end' : 'flex-start';
    div.dataset.messageId = message.id;

    if (isMe) {
        div.innerHTML = `
            <div style="display: flex; flex-direction: column; align-items: flex-end; max-width: 75%; ${isNew ? 'animation: slideIn 0.3s ease-out;' : ''}">
                <div style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; padding: 0.75rem 1rem; border-radius: 1rem 1rem 0.25rem 1rem; box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3); max-width: 100%;">
                    <span style="font-size: 0.9375rem; line-height: 1.5; color: white; word-wrap: break-word;">${message.body}</span>
                </div>
                <div style="font-size: 0.6875rem; color: #9ca3af; margin-top: 0.25rem; display: flex; align-items: center; gap: 0.25rem;">
                    ${time}
                    ${message.read_at ? '<span style="color: #3b82f6;">✓✓</span>' : '<span style="color: #d1d5db;">✓</span>'}
                </div>
            </div>
        `;
    } else {
        div.innerHTML = `
            <div style="display: flex; flex-direction: column; align-items: flex-start; max-width: 75%; ${isNew ? 'animation: slideIn 0.3s ease-out;' : ''}">
                <span style="font-size: 0.6875rem; color: #6b7280; margin-bottom: 0.25rem; font-weight: 500;">${senderName}</span>
                <div style="background: white; color: #1f2937; padding: 0.75rem 1rem; border-radius: 1rem 1rem 1rem 0.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; max-width: 100%;">
                    <span style="font-size: 0.9375rem; line-height: 1.5; color: #374151; word-wrap: break-word;">${message.body}</span>
                </div>
                <div style="font-size: 0.6875rem; color: #9ca3af; margin-top: 0.25rem;">
                    ${time}
                </div>
            </div>
        `;
    }

    chatBox.appendChild(div);
    chatBox.scrollTop = chatBox.scrollHeight;
}

// Mark messages as read
async function markAsRead() {
    try {
        await axios.post('/mark-read', {
            conversation_id: window.conversationId
        });
    } catch (error) {
        console.log('Failed to mark as read', error);
    }
}

// Load previous messages
let loadedMessageIds = new Set();

async function loadMessages() {
    try {
        const response = await axios.get(`/messages/${window.conversationId}`);
        const messages = response.data.messages || [];
        console.log('Loaded messages:', messages.length);

        // Sort messages by created_at ascending (oldest first)
        messages.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

        messages.forEach(msg => {
            if (!loadedMessageIds.has(msg.id)) {
                loadedMessageIds.add(msg.id);
                appendMessage(msg, true);
            }
        });

        // Mark as read after loading
        markAsRead();
    } catch (error) {
        const status = error.response?.status;
        const msg = error.response?.data?.error || error.message;
        console.error(`[loadMessages] Failed (HTTP ${status}):`, msg);
    }
}

// Online status check
async function checkOnlineStatus() {
    const otherParticipantId = window.otherParticipantId;
    const otherParticipantType = window.otherParticipantType;

    console.log('[OnlineStatus] Checking for:', otherParticipantId, otherParticipantType);

    if (!otherParticipantId || !otherParticipantType) {
        console.log('[OnlineStatus] Missing participant info');
        return;
    }

    try {
        const url = `/online-status/${otherParticipantId}/${otherParticipantType}`;
        console.log('[OnlineStatus] Calling:', url);
        const response = await axios.get(url, {
            timeout: 5000
        });
        console.log('[OnlineStatus] Response:', response.data);

        if (response.data && response.data.online !== undefined) {
            const isOnline = response.data.online;
            console.log('[OnlineStatus] isOnline:', isOnline, 'Type:', typeof isOnline);
            updateOnlineIndicator(isOnline);
        } else {
            console.log('[OnlineStatus] Invalid response, assuming offline');
            updateOnlineIndicator(false);
        }
    } catch (error) {
        console.log('[OnlineStatus] Error:', error.message);
        console.log('[OnlineStatus] Response:', error.response?.data);
        console.log('[OnlineStatus] Status:', error.response?.status);
        // If 401/403, it's an auth issue, don't change indicator
        if (error.response?.status === 401 || error.response?.status === 403) {
            console.log('[OnlineStatus] Auth issue - keeping current status');
            return;
        }
        updateOnlineIndicator(false);
    }
}

function updateOnlineIndicator(isOnline) {
    console.log('[OnlineIndicator] Function called with:', isOnline);

    let dot = document.getElementById('online-dot');
    let text = document.getElementById('online-text');

    console.log('[OnlineIndicator] Dot element:', dot);
    console.log('[OnlineIndicator] Text element:', text);

    if (dot) {
        if (isOnline) {
            dot.style.setProperty('background', '#22c55e', 'important');
            dot.style.setProperty('box-shadow', '0 0 8px #22c55e', 'important');
            console.log('[OnlineIndicator] Set GREEN - isOnline:', isOnline);
        } else {
            dot.style.setProperty('background', '#9ca3af', 'important');
            dot.style.setProperty('box-shadow', 'none', 'important');
            console.log('[OnlineIndicator] Set GRAY - isOnline:', isOnline);
        }
    } else {
        console.log('[OnlineIndicator] ERROR: Dot element not found!');
    }

    if (text) {
        text.textContent = isOnline ? 'Online' : 'Offline';
        console.log('[OnlineIndicator] Text updated to:', text.textContent);
    }
}

// Set up online status polling - every 3 seconds
setInterval(checkOnlineStatus, 3000);
checkOnlineStatus(); // Initial check



// Show typing indicator
let typingIndicatorTimeout;
window.showTypingIndicator = function (show) {
    console.log('[TypingIndicator] Show:', show);
    let chatBox = document.getElementById('chat-box');
    if (!chatBox) {
        console.log('[TypingIndicator] Chat box not found!');
        return;
    }

    let typingDiv = document.getElementById('typing-indicator');
    if (!typingDiv) {
        typingDiv = document.createElement('div');
        typingDiv.id = 'typing-indicator';
        typingDiv.style.cssText = 'display: flex; justify-content: flex-start; width: 100%; padding: 0.5rem 0; order: 9999;';
        typingDiv.innerHTML = `
            <div style="background: #e5e7eb; border-radius: 18px; padding: 8px 14px; display: flex; align-items: center; gap: 4px;">
                <span style="width: 6px; height: 6px; background: #6b7280; border-radius: 50%; animation: bounce 1s infinite;"></span>
                <span style="width: 6px; height: 6px; background: #6b7280; border-radius: 50%; animation: bounce 1s infinite; animation-delay: 0.15s;"></span>
                <span style="width: 6px; height: 6px; background: #6b7280; border-radius: 50%; animation: bounce 1s infinite; animation-delay: 0.3s;"></span>
            </div>
        `;
        chatBox.appendChild(typingDiv);
    }

    if (show) {
        typingDiv.style.display = 'flex';
        // Auto-hide after 3 seconds
        if (typingIndicatorTimeout) clearTimeout(typingIndicatorTimeout);
        typingIndicatorTimeout = setTimeout(() => {
            showTypingIndicator(false);
        }, 3000);
    } else {
        typingDiv.style.display = 'none';
        if (typingIndicatorTimeout) clearTimeout(typingIndicatorTimeout);
    }

    // Scroll to bottom
    chatBox.scrollTop = chatBox.scrollHeight;

    console.log('[TypingIndicator] Div display:', typingDiv.style.display);
}

// Handle typing event
let typingTimeout;
window.handleTyping = function () {
    console.log('[Typing] handleTyping called for user:', window.uniqueUserId, 'conversation:', conversationId);

    // Trigger typing event via Echo (broadcast to other participants)
    if (typeof Echo !== 'undefined') {
        console.log('[Typing] Sending whisper to: conversation.' + conversationId);
        const channel = Echo.private('conversation.' + conversationId);
        channel.whisper('typing', {
            userId: window.uniqueUserId,
            typing: true
        });
        console.log('[Typing] Whisper sent');
    } else {
        console.log('[Typing] Echo not defined!');
    }

    // Clear previous timeout
    if (typingTimeout) clearTimeout(typingTimeout);

    // Send typing: false after 2 seconds of no typing
    typingTimeout = setTimeout(() => {
        console.log('[Typing] Sending typing: false and hiding indicator');
        if (typeof Echo !== 'undefined') {
            const channel = Echo.private('conversation.' + conversationId);
            channel.whisper('typing', {
                userId: window.uniqueUserId,
                typing: false
            });
        }
    }, 2000);
}

// Send message function
window.sendMessage = async function () {
    let input = document.getElementById('message-input');
    let message = input.value.trim();

    if (!message) return;

    input.disabled = true;

    try {
        const response = await axios.post('/send-message', {
            message: message,
            conversation_id: window.conversationId
        });

        console.log('Send response:', response.data);
        input.value = '';

        if (response.data && response.data.id) {
            if (!loadedMessageIds.has(response.data.id)) {
                loadedMessageIds.add(response.data.id);
                appendMessage(response.data, true, true);
            }
        }

        markAsRead();
    } catch (error) {
        alert('Failed to send message');
        console.error(error);
    } finally {
        input.disabled = false;
        input.focus();
    }
}

// Handle Enter key
window.handleEnterKey = function (event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        window.sendMessage();
    } else {
        window.handleTyping();
    }
}

// Global flag to prevent double initialization
let chatInitialized = false;

// Initialize on page load
function initChat() {
    if (chatInitialized) {
        console.log('Chat already initialized, skipping');
        return;
    }
    chatInitialized = true;

    console.log('Initializing chat for conversation:', window.conversationId);
    console.log('Window userId:', window.userId, 'uniqueUserId:', window.uniqueUserId);
    console.log('Echo available:', typeof Echo !== 'undefined');

    // Send heartbeat every 5 seconds to stay online
    setInterval(() => {
        axios.post('/online-heartbeat').catch(() => { });
    }, 5000);
    // Send initial heartbeat
    axios.post('/online-heartbeat').catch(() => { });

    // Listen for messages
    if (typeof Echo !== 'undefined') {
        const channel = Echo.private('conversation.' + window.conversationId);
        console.log('[Echo] Subscribing to channel: conversation.' + window.conversationId);

        channel.error((error) => {
            console.error('[Echo] Channel authorization failed:', error);
        });

        channel.subscribed(() => {
            console.log('[Echo] Successfully subscribed to channel: conversation.' + conversationId);
        });

        channel.listen('.message.sent', (e) => {
            console.log('Real-time message received:', e.message);
            console.log(' sender_id:', e.message.sender_id, ' window.userId:', window.userId);

            // Always append incoming messages at bottom
            if (e.message.sender_id != window.userId) {
                console.log('Appending incoming message');

                // Add to loaded messages to prevent duplicate
                if (!loadedMessageIds.has(e.message.id)) {
                    loadedMessageIds.add(e.message.id);
                    appendMessage(e.message, true, true);
                }

                // Mark as read when receiving new message
                markAsRead();
            }
        });

        // Listen for typing events
        channel.listenForWhisper('typing', (e) => {
            console.log('[Typing] Received whisper from:', e.userId, 'typing:', e.typing, 'current user:', window.uniqueUserId);
            if (e.userId !== window.uniqueUserId) {
                console.log('[Typing] Showing indicator for remote user');
                showTypingIndicator(e.typing);
            } else {
                console.log('[Typing] Ignoring own typing event');
            }
        });

    } else {
        console.log('[Echo] Echo is not defined!');
    }

    // Also listen on global event for debugging
    window.Echo = Echo;

    // Load previous messages
    loadMessages();

    // Add Enter key listener
    const input = document.getElementById('message-input');
    if (input) {
        input.removeEventListener('keydown', window.handleEnterKey);
        input.addEventListener('keydown', window.handleEnterKey);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initChat);
} else {
    initChat();
}
