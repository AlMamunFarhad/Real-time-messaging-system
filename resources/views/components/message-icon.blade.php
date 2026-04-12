@php
    $userId = $currentUserId ?? AuthParticipant::id();
    $userType = $currentUserType ?? AuthParticipant::type();
    $userTypeShort = $userType ? strtolower(class_basename($userType)) : null;
    $unread = $unreadCount ?? 0;
    $convs = $conversations ?? collect();
    
    $hasConversation = $convs->isNotEmpty();
    
    // Get current logged in user's name
    $currentUserName = 'User';
    if ($userType && $userId) {
        $user = $userType::find($userId);
        if ($user) {
            $currentUserName = $user->name ?? ($user->email ?? 'User #' . $userId);
        }
    }
@endphp
<div x-data="{
    open: false,
    showNewMessage: false,
    conversations: @js($convs),
    unreadCount: {{ $unread }},
    userId: {{ $userId ?? 0 }},
    userType: '{{ $userType }}',
    userTypeShort: '{{ $userTypeShort }}',
    currentUserName: '{{ $currentUserName }}',
    init() {
        this.refreshConversations();

        this.$watch('open', (value) => {
            if (value) {
                this.refreshConversations();
                this.showNewMessage = false;
            }
        });

        setInterval(() => {
            this.refreshConversations();
        }, 3000);

        if (typeof Echo !== 'undefined' && this.userId) {
            const channelName = 'user.' + this.userTypeShort + '.' + this.userId;

            Echo.private(channelName)
                .listen('.message.sent', (e) => {
                    console.log('New message received in message-icon:', e.message);
                    this.refreshConversations();
                })
                .error((error) => {
                    console.log('Echo subscription error:', error);
                });
        }
    },
    formatTime(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        
        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return diffMins + 'm ago';
        if (diffHours < 24) return diffHours + 'h ago';
        if (diffDays < 7) return diffDays + 'd ago';
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
    },
    getOtherParticipantName(participants, currentUserId, currentUserTypeShort) {
        if (!participants || participants.length === 0) return 'Unknown';
        for (let p of participants) {
            let pTypeShort = p.participant_type ? p.participant_type.split('\\\\').pop().toLowerCase() : '';
            if (p.participant_id != currentUserId || pTypeShort != currentUserTypeShort) {
                return p.participant_name || 'User #' + p.participant_id;
            }
        }
        return 'User';
    },
    async refreshConversations() {
        try {
            console.log('Refreshing conversations...');
            const response = await fetch('{{ route('messages.conversations') }}', {
                credentials: 'include'
            });
            if (response.ok) {
                const data = await response.json();
                this.conversations = data.conversations || [];
                this.unreadCount = data.unread_count || 0;
                console.log('Conversations refreshed:', this.conversations.length);
            }
        } catch (error) {
            console.error('Failed to refresh conversations:', error);
        }
    }
}" x-init="init()" class="relative">
    <!-- Message Icon Button -->
    <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <template x-if="unreadCount > 0">
            <span
                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center animate-pulse"
                x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
        </template>
    </button>

    <!-- Dropdown Popup -->
    <div x-show="open" @click.outside="open = false; showNewMessage = false" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden z-50">

        <!-- Header with User Name -->
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3">
            <h3 class="text-white font-semibold flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                Messages
                <template x-if="unreadCount > 0">
                    <span class="bg-red-400 text-white text-xs px-2 py-0.5 rounded-full"
                        x-text="unreadCount + ' new'"></span>
                </template>
            </h3>
            <p class="text-blue-100 text-xs mt-1" x-text="'Logged in as: ' + currentUserName"></p>
        </div>

        <!-- New Message Button (show only if no conversations exist yet) -->
        <div x-show="conversations.length === 0" class="px-4 py-2 border-b border-gray-100">
            <button @click="showNewMessage = !showNewMessage" 
                class="w-full flex items-center gap-2 text-blue-600 hover:text-blue-700 text-sm font-medium py-2 px-3 rounded-lg hover:bg-blue-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Message
            </button>
            <!-- New Message Options -->
            <div x-show="showNewMessage" x-transition class="mt-2 bg-gray-50 rounded-lg p-2">
                <p class="text-xs text-gray-500 mb-2">Select recipient:</p>
                <div class="space-y-1">
                    <a href="{{ route('chat.index', ['userId' => 1, 'type' => 'admin']) }}" 
                        class="block px-3 py-2 text-sm text-gray-700 hover:bg-white hover:text-blue-600 rounded-lg transition-colors">
                        Message Admin
                    </a>
                </div>
            </div>
        </div>

        <!-- Conversation List -->
        <div class="max-h-80 overflow-y-auto" x-show="conversations.length > 0">
            <template x-for="conversation in conversations" :key="conversation.id">
                <a :href="'/chat/' + conversation.id"
                    class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors duration-150 border-b border-gray-50 last:border-b-0">
                    <!-- Avatar -->
                    <div class="relative">
                        <div
                            class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white font-semibold">
                            <span
                                x-text="conversation.participants && conversation.participants.length > 1 ? conversation.participants[0].participant_type.split('\\\\').pop().charAt(0).toUpperCase() : '?'"></span>
                        </div>
                        <template x-if="conversation.unread_count > 0">
                            <span
                                class="absolute -bottom-1 -right-1 bg-green-500 h-3 w-3 rounded-full border-2 border-white"></span>
                        </template>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-semibold text-gray-900 truncate"
                                x-text="getOtherParticipantName(conversation.participants, userId, userTypeShort)"></span>
                            <span class="text-xs text-gray-400"
                                x-text="conversation.last_message ? formatTime(conversation.last_message.created_at) : ''"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-gray-500 truncate"
                                x-text="conversation.last_message ? conversation.last_message.body.substring(0, 30) : 'No messages yet'">
                            </p>
                            <template x-if="conversation.unread_count > 0">
                                <span
                                    class="bg-blue-500 text-white text-xs font-bold rounded-full h-5 min-w-5 px-1.5 flex items-center justify-center"
                                    x-text="conversation.unread_count"></span>
                            </template>
                        </div>
                    </div>
                </a>
            </template>
        </div>
        <!-- Empty state -->
        <div x-show="conversations.length === 0" class="px-4 py-8 text-center text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-300" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <p>No conversations yet</p>
        </div>
    </div>
</div>
