@php
    $userId = $currentUserId ?? AuthParticipant::id();
    $userType = $currentUserType ?? AuthParticipant::type();
    $userTypeShort = $userType ? strtolower(class_basename($userType)) : null;
    $unread = $unreadCount ?? 0;
    $convs = $conversations ?? collect();
@endphp
<div x-data="{
    open: false,
    conversations: @js($convs),
    unreadCount: {{ $unread }},
    userId: {{ $userId ?? 0 }},
    userType: '{{ $userType }}',
    userTypeShort: '{{ $userTypeShort }}',
    init() {
        this.refreshConversations();

        // Refresh when dropdown opens
        this.$watch('open', (value) => {
            if (value) {
                this.refreshConversations();
            }
        });

        // Polling as fallback every 5 seconds
        setInterval(() => {
            this.refreshConversations();
        }, 5000);

        if (typeof Echo !== 'undefined' && this.userId) {
            const channelName = 'user.' + this.userTypeShort + '.' + this.userId;
            console.log('Subscribing to channel:', channelName);

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
    async refreshConversations() {
        try {
            const response = await fetch('{{ route('messages.conversations') }}', {
                credentials: 'include'
            });
            if (response.ok) {
                const data = await response.json();
                this.conversations = data.conversations || [];
                this.unreadCount = data.unread_count || 0;
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
    <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden z-50">

        <!-- Header -->
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
                                x-text="conversation.participants && conversation.participants.length > 1 ? conversation.participants[0].participant_type.charAt(0).toUpperCase() : '?'"></span>
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
                                x-text="'User #' + (conversation.participants && conversation.participants[0] ? conversation.participants[0].participant_id : 'Unknown')"></span>
                            <span class="text-xs text-gray-400"
                                x-text="conversation.last_message ? conversation.last_message.created_at : ''"></span>
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
