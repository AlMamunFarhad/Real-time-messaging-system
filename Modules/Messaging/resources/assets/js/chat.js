window.appendMessage = function (message, append = true, isNew = false) {
    let chatBox = document.getElementById('chat-box');
    if (!chatBox) return;
    
    let isMe = message.sender_id == window.userId;
    let time = message.created_at ? new Date(message.created_at).toLocaleTimeString() : new Date().toLocaleTimeString();
    let senderName = message.sender_name || (isMe ? 'You' : 'Unknown');

    let div = document.createElement('div');
    div.className = 'flex ' + (isMe ? 'justify-end' : 'justify-start');
    div.dataset.messageId = message.id;

    if (isMe) {
        // My messages - right side with tail
        div.innerHTML = `
            <div class="flex flex-col items-end max-w-[70%] ${isNew ? 'animate-slide-in' : ''}">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-3 rounded-2xl rounded-br-md shadow-md">
                    <div class="text-sm">${message.body}</div>
                </div>
                <div class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                    ${time}
                    ${message.read_at ? '<span class="text-blue-500">✓✓</span>' : '<span class="text-gray-300">✓✓</span>'}
                </div>
            </div>
        `;
    } else {
        // Their messages - left side with tail
        div.innerHTML = `
            <div class="flex flex-col items-start max-w-[70%] ${isNew ? 'animate-slide-in' : ''}">
                <span class="text-xs text-gray-500 mb-1 font-medium">${senderName}</span>
                <div class="bg-white text-gray-800 px-4 py-3 rounded-2xl rounded-bl-md shadow-sm border border-gray-100">
                    <div class="text-sm">${message.body}</div>
                </div>
                <div class="text-xs text-gray-400 mt-1">
                    ${time}
                </div>
            </div>
        `;
    }

    if (append) {
        chatBox.appendChild(div);
    } else {
        chatBox.prepend(div);
    }
    chatBox.scrollTop = chatBox.scrollHeight;
}

// Mark messages as read
async function markAsRead() {
    try {
        await axios.post('/mark-read', {
            conversation_id: conversationId
        });
    } catch (error) {
        console.log('Failed to mark as read', error);
    }
}

// Load previous messages
async function loadMessages() {
    try {
        const response = await axios.get(`/api/web/messagings/${conversationId}/messages`);
        const messages = response.data.messages || [];
        console.log('Loaded messages:', messages);
        messages.forEach(msg => appendMessage(msg, false));
        
        // Mark as read after loading
        markAsRead();
    } catch (error) {
        console.log('No previous messages', error);
    }
}

// Show typing indicator
window.showTypingIndicator = function(show) {
    let typingDiv = document.getElementById('typing-indicator');
    if (!typingDiv) {
        typingDiv = document.createElement('div');
        typingDiv.id = 'typing-indicator';
        typingDiv.className = 'flex justify-start px-4 py-2';
        typingDiv.innerHTML = `
            <div class="bg-gray-200 rounded-full px-4 py-2 flex items-center gap-1">
                <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce"></span>
                <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></span>
                <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
            </div>
        `;
        document.getElementById('chat-box').appendChild(typingDiv);
    }
    typingDiv.style.display = show ? 'flex' : 'none';
}

// Handle typing event
let typingTimeout;
window.handleTyping = function() {
    // Trigger typing event via Echo (broadcast to other participants)
    if (typeof Echo !== 'undefined') {
        Echo.private('conversation.' + conversationId)
            .whisper('typing', {
                userId: window.userId,
                typing: true
            });
    }
    
    // Clear previous timeout
    if (typingTimeout) clearTimeout(typingTimeout);
    
    // Hide typing indicator after 2 seconds of no typing
    typingTimeout = setTimeout(() => {
        showTypingIndicator(false);
    }, 2000);
}

// send message
window.sendMessage = async function () {
    let input = document.getElementById('message-input');
    let message = input.value.trim();

    if (!message) return;

    input.disabled = true;

    try {
        const response = await axios.post('/send-message', {
            message: message,
            conversation_id: conversationId
        });

        input.value = '';
    } catch (error) {
        alert('Failed to send message');
        console.error(error);
    } finally {
        input.disabled = false;
        input.focus();
    }
}

// Handle Enter key
window.handleEnterKey = function(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        window.sendMessage();
    } else {
        window.handleTyping();
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing chat for conversation:', conversationId);
    console.log('Window userId:', window.userId);
    console.log('Echo available:', typeof Echo !== 'undefined');
    
    if (typeof Echo === 'undefined') {
        console.error('Echo is not defined! Make sure echo.js is loaded.');
        return;
    }
    
    // Subscribe to private channel
    const channel = Echo.private('conversation.' + conversationId);
    
    // Handle subscription success
    channel.subscribed(() => {
        console.log('Successfully subscribed to conversation.' + conversationId);
    }).error((error) => {
        console.error('Subscription error:', error);
    });
    
    // Listen for messages
    channel.listen('.message.sent', (e) => {
        console.log('Real-time message received:', e.message);
        console.log(' sender_id:', e.message.sender_id, ' window.userId:', window.userId);
        
        // Always append incoming messages
        if (e.message.sender_id != window.userId) {
            console.log('Appending incoming message');
            appendMessage(e.message, true, true);
            
            // Mark as read when receiving new message
            markAsRead();
        }
    });

    // Listen for typing events
    channel.listenForWhisper('typing', (e) => {
        console.log('Typing from:', e.userId);
        if (e.userId != window.userId) {
            showTypingIndicator(e.typing);
        }
    });

    // Also listen on global event for debugging
    window.Echo = Echo;

    // Load previous messages
    loadMessages();
    
    // Add Enter key listener
    const input = document.getElementById('message-input');
    if (input) {
        input.addEventListener('keydown', handleEnterKey);
    }
});