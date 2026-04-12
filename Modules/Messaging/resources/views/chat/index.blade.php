<x-messaging::layouts.master>

    {{-- ================== PHP LOGIC FIRST ================== --}}
    @php
        $participantId = \Modules\Messaging\Helpers\AuthParticipant::id();
        $participantType = \Modules\Messaging\Helpers\AuthParticipant::type();
        $participantTypeShort = strtolower(class_basename($participantType));

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

        console.log('Chat initialized - conversationId:', window.conversationId, 'userId:', window.userId);
        console.log('Other participant ID:', window.otherParticipantId, 'Type:', window.otherParticipantType);
    </script>

    {{-- ================== VITE ================== --}}
    @vite(['resources/js/app.js', 'Modules/Messaging/resources/assets/js/app.js', 'Modules/Messaging/resources/assets/js/chat.js'])

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
