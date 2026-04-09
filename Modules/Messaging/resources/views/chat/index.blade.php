<x-messaging::layouts.master>
    <div class="max-w-4xl mx-auto mt-10 bg-white shadow-2xl rounded-2xl overflow-hidden border border-gray-100">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 text-white p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">Chat</h3>
                    <p class="text-xs text-blue-100">Conversation #{{ $conversation->id }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 bg-green-400 rounded-full animate-pulse"></span>
                <span class="text-sm text-white/80">Online</span>
            </div>
        </div>

        <!-- Chat Body -->
        <div id="chat-box" class="h-[450px] overflow-y-auto p-4 space-y-4 bg-gradient-to-b from-gray-50 to-gray-100">
            <!-- Messages will appear here -->
        </div>

        <!-- Input Area -->
        <div class="border-t border-gray-100 bg-white p-4">
            <div class="flex items-center gap-3 bg-gray-100 rounded-full px-4 py-2">
                <input type="text" id="message-input"
                    class="flex-1 bg-transparent border-none focus:outline-none focus:ring-0 text-gray-700 placeholder-gray-400"
                    placeholder="Type your message..."
                    autocomplete="off">
                <button onclick="sendMessage()" 
                    class="bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white p-2 rounded-full transition-all duration-200 shadow-md hover:shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <script>
        const conversationId = {{ $conversation->id }};
    </script>

    @vite(['resources/js/app.js', 'Modules/Messaging/resources/assets/js/app.js', 'Modules/Messaging/resources/assets/js/chat.js'])
</x-messaging::layouts.master>

@push('styles')
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
.animate-slide-in {
    animation: slideIn 0.3s ease-out;
}
</style>
@endpush

@push('scripts')
    <script>
        @php
            $participantId = \Modules\Messaging\Helpers\AuthParticipant::id();
            $participantType = \Modules\Messaging\Helpers\AuthParticipant::type();
        @endphp
        window.userId = {{ $participantId ?? 0 }};
        window.userType = '{{ $participantType }}';
    </script>
@endpush