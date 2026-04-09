<x-admin-layout>
    <x-slot name="header">
        <h3 class="flex justify-end items-end">
            <a href="{{ route('admin.logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                class="text-white dark:text-gray-50 flex items-end space-x-1 btn">
                {{ __('Logout') }}
            </a>
        </h3>
        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white text-white dark:text-gray-8000 dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                Welcome, Admin!
            </div>
            
            <!-- Chat Links -->
            <div class="mt-6">
                <h4 class="text-lg font-semibold mb-4">Chat with Users</h4>
                <a href="{{ route('chat.index', ['userId' => 1, 'type' => 'user']) }}" 
                   class="inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                    Chat with User #1 (user@gmail.com)
                </a>
                <a href="{{ route('chat.index', ['userId' => 3, 'type' => 'user']) }}" 
                   class="inline-block bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 ml-4">
                    Chat with User #3 (test@example.com)
                </a>
            </div>
            
            <!-- User List for Chat -->
            <div class="mt-6">
                <h4 class="text-lg font-semibold mb-4">All Users</h4>
                <ul class="space-y-2">
                    @php
                    $users = \App\Models\User::all();
                    @endphp
                    @foreach($users as $user)
                    <li>
                        @php
                        // Find or create conversation with this user
                        $conversation = \Modules\Messaging\Models\Conversation::whereHas('participants', function($q) use ($user) {
                            $q->where('participant_id', $user->id)->whereIn('participant_type', [\App\Models\User::class, 'user']);
                        })
                        ->whereHas('participants', function($q) {
                            $q->where('participant_id', 1)->whereIn('participant_type', [\App\Models\Admin::class, 'admin']);
                        })
                        ->first();
                        
                        // Create if doesn't exist
                        if(!$conversation) {
                            $conversation = \Modules\Messaging\Models\Conversation::create(['type' => 'private']);
                            \Modules\Messaging\Models\ConversationParticipant::insert([
                                ['conversation_id' => $conversation->id, 'participant_id' => 1, 'participant_type' => \App\Models\Admin::class, 'joined_at' => now(), 'created_at' => now(), 'updated_at' => now()],
                                ['conversation_id' => $conversation->id, 'participant_id' => $user->id, 'participant_type' => \App\Models\User::class, 'joined_at' => now(), 'created_at' => now(), 'updated_at' => now()],
                            ]);
                        }
                        @endphp
                        <a href="{{ route('chat.show', $conversation->id) }}" 
                           class="text-blue-600 hover:underline">
                            User #{{ $user->id }} - {{ $user->email }} (Conv: {{ $conversation->id }})
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-admin-layout>
