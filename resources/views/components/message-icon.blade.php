@php
    $userId = $currentUserId ?? AuthParticipant::id();
    $userType = $currentUserType ?? AuthParticipant::type();
    $userTypeShort = $userType ? strtolower(class_basename($userType)) : null;
    $unread = $unreadCount ?? 0;
    $convs = $conversations ?? collect();
    $userList = $userList ?? [];
    $isAdminDashboard = $isAdminDashboard ?? ($userTypeShort === 'admin');
    $lastPage = $lastPage ?? 1;
    
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
    currentPage: 1,
    totalPages: 1,
    userList: @js($userList),
    userSearch: '',
    searchTimeout: null,
    refreshInFlight: false,
    refreshTimer: null,
    async loadPage(page) {
        if (page < 1 || page > this.totalPages) return;
        this.currentPage = page;
        try {
            const query = encodeURIComponent(this.userSearch.trim());
            const response = await fetch('/admin/users/list?page=' + page + '&q=' + query, { credentials: 'include' });
            if (response.ok) {
                const data = await response.json();
                this.userList = data.users || [];
                this.currentPage = data.currentPage || 1;
                this.totalPages = data.lastPage || 1;
            }
        } catch (e) { console.error(e); }
    },
    handleSearchInput() {
        clearTimeout(this.searchTimeout);

        this.searchTimeout = setTimeout(() => {
            const query = this.userSearch.trim();

            if (!query) {
                this.userList = [];
                this.currentPage = 1;
                this.totalPages = 1;
                return;
            }

            this.loadPage(1);
        }, 250);
    },
    init() {
        this.refreshConversations(true);

        this.$watch('open', (value) => {
            if (value) {
                this.refreshConversations(true);
                this.showNewMessage = false;
            }
        });

        this.refreshTimer = setInterval(() => {
            this.refreshConversations();
        }, 1500);

        window.addEventListener('focus', () => this.refreshConversations(true));
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                this.refreshConversations(true);
            }
        });
        window.addEventListener('message-counter-sync', () => this.refreshConversations(true));
        window.addEventListener('storage', (event) => {
            if (event.key === 'message-counter-sync') {
                this.refreshConversations(true);
            }
        });

        if (typeof Echo !== 'undefined' && this.userId) {
            const channelName = 'user.' + this.userTypeShort + '.' + this.userId;

            Echo.private(channelName)
                .listen('.message.sent', (e) => {
                    console.log('New message received in message-icon:', e.message);
                    this.refreshConversations(true);
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
    getOtherParticipantName(conversation) {
        if (conversation?.other_participant_name) {
            return conversation.other_participant_name;
        }

        const participants = conversation?.participants || [];
        const currentUserId = this.userId;
        const currentUserTypeShort = this.userTypeShort;

        if (!participants || participants.length === 0) return 'Unknown';
        for (let p of participants) {
            let pTypeShort = p.participant_type ? p.participant_type.split('\\\\').pop().toLowerCase() : '';
            if (p.participant_id != currentUserId || pTypeShort != currentUserTypeShort) {
                return p.participant_name || 'User #' + p.participant_id;
            }
        }
        return 'User';
    },
    getConversationInitial(conversation) {
        const name = this.getOtherParticipantName(conversation);
        return name ? name.charAt(0).toUpperCase() : '?';
    },
    async refreshConversations(force = false) {
        if (this.refreshInFlight && !force) {
            return;
        }

        this.refreshInFlight = true;

        try {
            console.log('Refreshing conversations...');
            const url = '{{ route('messages.conversations') }}' + '?t=' + Date.now();
            const response = await fetch(url, {
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
        } finally {
            this.refreshInFlight = false;
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

        <!-- Admin: Show user list by default with pagination -->
        @if($isAdminDashboard)
        <div class="px-4 py-2 border-b border-gray-100">
            <div class="bg-gray-50 rounded-lg p-2">
                <p class="text-xs text-gray-500 mb-2">Select user to message:</p>
                <div class="mb-2">
                    <input
                        x-model="userSearch"
                        @input="handleSearchInput()"
                        type="text"
                        placeholder="Search user by name or email"
                        class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 placeholder:text-gray-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                    >
                </div>
                <div class="space-y-1 max-h-48 overflow-y-auto">
                    <template x-if="!userSearch.trim()">
                        <div class="text-sm text-gray-500 text-center py-2">Type a user name or email to search</div>
                    </template>
                    <template x-if="userSearch.trim() && userList.length === 0">
                        <div class="text-sm text-gray-500 text-center py-2">No user found</div>
                    </template>
                    <template x-for="user in userList" :key="user.id">
                        <a :href="'/chat/' + user.id + '/user'" 
                            class="block px-3 py-2 text-sm text-gray-700 hover:bg-white hover:text-blue-600 rounded-lg transition-colors">
                            <div class="font-medium" x-text="user.name"></div>
                            <div class="text-xs text-gray-400" x-text="user.email"></div>
                        </a>
                    </template>
                </div>
                <!-- Pagination -->
                <div class="mt-2 pt-2 border-t border-gray-200 flex justify-between items-center">
                    <button @click="loadPage(currentPage - 1)" 
                        :disabled="!userSearch.trim() || currentPage <= 1"
                        class="text-xs px-2 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">
                        Prev
                    </button>
                    <span class="text-xs text-gray-500">Page <span x-text="currentPage"></span> / <span x-text="totalPages"></span></span>
                    <button @click="loadPage(currentPage + 1)" 
                        :disabled="!userSearch.trim() || currentPage >= totalPages"
                        class="text-xs px-2 py-1 rounded bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed">
                        Next
                    </button>
                </div>
            </div>
        </div>
        @else
        <!-- User Dashboard - Direct message to admin without select option -->
        <div x-show="conversations.length === 0" class="px-4 py-2 border-b border-gray-100">
            @php
                $firstAdmin = \App\Models\Admin::first();
            @endphp
            @if($firstAdmin)
            <a href="{{ route('chat.index', ['userId' => $firstAdmin->id, 'type' => 'admin']) }}" 
                class="w-full flex items-center gap-2 text-blue-600 hover:text-blue-700 text-sm font-medium py-2 px-3 rounded-lg hover:bg-blue-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                Message Admin
            </a>
            @endif
        </div>
        @endif

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
                                x-text="getConversationInitial(conversation)"></span>
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
                                x-text="getOtherParticipantName(conversation)"></span>
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
