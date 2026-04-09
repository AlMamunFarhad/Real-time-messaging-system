<div x-data="{ open: false }" class="relative">
    <!-- Message Icon Button -->
    <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center animate-pulse">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Popup -->
    <div x-show="open" @click.outside="open = false" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden z-50">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3">
            <h3 class="text-white font-semibold flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                Messages
                @if($unreadCount > 0)
                    <span class="bg-red-400 text-white text-xs px-2 py-0.5 rounded-full">{{ $unreadCount }} new</span>
                @endif
            </h3>
        </div>

        <!-- Conversation List -->
        <div class="max-h-80 overflow-y-auto">
            @forelse($conversations as $conversation)
                @php
                    $otherParticipant = $conversation->participants->first(function($p) use ($currentUserId, $currentUserType) {
                        return $p->participant_id != $currentUserId || $p->participant_type != $currentUserType;
                    });
                    $lastMessage = $conversation->lastMessage;
                    $unread = $conversation->unread_count ?? 0;
                @endphp
                <a href="{{ route('chat.show', $conversation->id) }}" 
                   class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors duration-150 border-b border-gray-50 last:border-b-0">
                    <!-- Avatar -->
                    <div class="relative">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white font-semibold">
                            {{ $otherParticipant ? strtoupper(substr($otherParticipant->participant_type, 0, 1)) : '?' }}
                        </div>
                        @if($unread > 0)
                            <span class="absolute -bottom-1 -right-1 bg-green-500 h-3 w-3 rounded-full border-2 border-white"></span>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-semibold text-gray-900 truncate">
                                {{ $otherParticipant ? class_basename($otherParticipant->participant_type) . ' #' . $otherParticipant->participant_id : 'Unknown' }}
                            </span>
                            @if($lastMessage)
                                <span class="text-xs text-gray-400">{{ $lastMessage->created_at->diffForHumans() }}</span>
                            @endif
                        </div>
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-gray-500 truncate">
                                {{ $lastMessage ? Str::limit($lastMessage->body, 30) : 'No messages yet' }}
                            </p>
                            @if($unread > 0)
                                <span class="bg-blue-500 text-white text-xs font-bold rounded-full h-5 min-w-5 px-1.5 flex items-center justify-center">
                                    {{ $unread }}
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="px-4 py-8 text-center text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <p>No conversations yet</p>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-4 py-2 border-t border-gray-100">
            <a href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center justify-center gap-1">
                View all messages
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>
</div>