<x-messaging::layouts.master>
    <h1>Hello World</h1>

    <p>Module: {!! config('messaging.name') !!}</p>

    <x-messaging::layouts.master>
        <h1>Hello World</h1>

        <p>Module: {!! config('messaging.name') !!}</p>

        <div class="max-w-4xl mx-auto mt-10 bg-white shadow-xl rounded-2xl overflow-hidden">

            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white p-4 font-semibold">
                Chat
            </div>

            <!-- Chat Body -->
            <div id="chat-box" class="h-[400px] overflow-y-auto p-4 space-y-3 bg-gray-100">
                <!-- Messages -->
            </div>

            <!-- Typing Indicator -->
            {{-- <div id="typing" class="px-4 text-sm text-gray-500 hidden">
                typing...
            </div> --}}

            <!-- Input -->
            <div class="flex items-center border-t p-3 gap-2">
                <input type="text" id="message-input"
                    class="flex-1 border rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="Type a message...">
                <button onclick="sendMessage()" class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded-full">
                    Send
                </button>
            </div>
        </div>

        <script>
            const conversationId = {{ $conversation->id }};
        </script>

        @vite(['resources/js/app.js', 'Modules/Messaging/Resources/assets/js/chat.js'])
    </x-messaging::layouts.master>
    @push('scripts')
        <script>
            // Reverb listener
            Echo.private(`conversation.${conversationId}`)
                .listen('.message.sent', (e) => {
                    appendMessage(e.message);
                });

            window.userId = {{ auth()->id() }};
        </script>
    @endpush

@endpush
