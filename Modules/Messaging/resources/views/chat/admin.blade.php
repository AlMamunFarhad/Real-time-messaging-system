<x-admin-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Admin Messages</h2>
        </div>
    </x-slot>

    @php
        $participantId = \Modules\Messaging\Helpers\AuthParticipant::id();
        $participantType = \Modules\Messaging\Helpers\AuthParticipant::type();
        $participantTypeShort = strtolower(class_basename($participantType));
        $initialConversationId = (int) ($conversation?->id ?? 0);
        $initialOtherParticipantId = (int) ($otherParticipant?->participant_id ?? 0);
        $initialOtherParticipantType = $otherParticipant
            ? strtolower(
                class_basename(
                    match ($otherParticipant->participant_type) {
                        'admin' => \App\Models\Admin::class,
                        'user' => \App\Models\User::class,
                        default => $otherParticipant->participant_type,
                    },
                ),
            )
            : '';
        $initialOtherUserName = $otherUserName ?? '';
        $initialActiveUserId = (int) ($activeUserId ?? 0);
    @endphp

    <div x-data="adminMessagesApp({
        userId: {{ $participantId ?? 0 }},
        userTypeShort: '{{ $participantTypeShort }}',
        initialConversationId: {{ $initialConversationId }},
        initialOtherParticipantId: {{ $initialOtherParticipantId }},
        initialOtherParticipantType: '{{ $initialOtherParticipantType }}',
        initialOtherUserName: @js($initialOtherUserName),
        initialActiveUserId: {{ $initialActiveUserId }},
        showChat: false,
        isFileTooLarge: false
    })" x-init="init()" class="-m-6 mx-auto max-w-7xl overflow-hidden">
        <div class="mx-3 my-3 overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-[0_24px_80px_-28px_rgba(15,23,42,0.35)] md:mx-4 md:my-4 lg:mx-0 lg:my-0 lg:rounded-[28px]">
            <div class="relative flex h-[88vh] overflow-hidden md:grid md:grid-cols-[300px_minmax(0,1fr)] lg:grid-cols-[340px_minmax(0,1fr)]">
                <aside class="flex h-full w-full flex-col overflow-hidden border-b border-stone-200 bg-[#fafafa] md:w-auto md:border-b-0 md:border-r">
                    <div class="shrink-0 border-b border-stone-200/60 bg-white/80 backdrop-blur-xl px-6 py-5">
                        <div class="flex items-center gap-4">
                            <div class="flex h-[46px] w-[46px] items-center justify-center rounded-[16px] bg-stone-100 text-stone-700 shadow-sm border border-stone-200/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-3 3-3-3z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-[17px] font-bold text-stone-800 tracking-tight">Conversations</h3>
                                <p class="text-[11px] font-semibold text-stone-400 uppercase tracking-widest mt-0.5">Manage Users</p>
                            </div>
                        </div>
                        <div class="relative mt-5">
                            <input x-model="search" @input="debouncedLoadUsers()" type="text"
                                placeholder="Search here..."
                                class="w-full rounded-[16px] border border-transparent bg-stone-100/80 px-5 py-3 pr-11 text-[13.5px] font-medium text-stone-700 outline-none transition-all duration-300 placeholder:text-stone-400 focus:border-stone-300/60 focus:bg-white focus:ring-4 focus:ring-stone-100/50 focus:shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="pointer-events-none absolute right-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-stone-400 transition-colors"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z" />
                            </svg>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto px-4 py-4 space-y-1.5 custom-scrollbar">
                        <template x-if="loadingUsers">
                            <div class="space-y-3">
                                <div class="h-[72px] animate-pulse rounded-[20px] bg-white border border-stone-100 shadow-sm"></div>
                                <div class="h-[72px] animate-pulse rounded-[20px] bg-white border border-stone-100 shadow-sm"></div>
                                <div class="h-[72px] animate-pulse rounded-[20px] bg-white border border-stone-100 shadow-sm"></div>
                            </div>
                        </template>

                        <template x-if="!loadingUsers && users.length === 0">
                            <div class="rounded-[20px] border border-dashed border-stone-200 bg-stone-50 px-4 py-12 text-center mt-2">
                                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-stone-400 mb-3 shadow-sm border border-stone-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </span>
                                <p class="text-[13px] font-semibold text-stone-500">No users found</p>
                            </div>
                        </template>

                        <div class="space-y-1.5 pointer-events-auto" x-show="!loadingUsers && users.length">
                            <template x-for="user in users" :key="user.id">
                                <button type="button" @click="selectUser(user)"
                                    class="group block w-full rounded-[20px] px-3.5 py-3 text-left transition-all duration-300"
                                    :class="Number(activeUserId) === Number(user.id) ?
                                        'bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] ring-1 ring-stone-200/60 scale-[1.01] z-10 relative' :
                                        'bg-transparent hover:bg-white hover:shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)] text-stone-800'">
                                    <div class="flex items-center gap-3.5">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[16px] text-[14px] font-bold shadow-sm transition-all duration-300"
                                            :class="Number(activeUserId) === Number(user.id) ? 'bg-gradient-to-br from-rose-400 to-orange-300 text-white shadow-md shadow-rose-200' :
                                                'bg-[#f4efe8] text-stone-600 group-hover:bg-[#f0ece5]'"
                                            x-text="initialFor(user.name)"></div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5">
                                                <div class="truncate text-[14.5px] font-bold text-stone-800 tracking-tight" x-text="user.name"></div>
                                                <span class="inline-flex h-2 w-2 shrink-0 rounded-full transition-colors ml-1"
                                                    :class="user.is_online ?
                                                        'bg-emerald-400 shadow-[0_0_0_3px_rgba(16,185,129,0.12)]' :
                                                        'bg-stone-200'"></span>
                                            </div>
                                            <div class="truncate text-[12px] mt-0.5 font-medium transition-colors"
                                                :class="Number(activeUserId) === Number(user.id) ? 'text-rose-500' :
                                                    'text-stone-400'"
                                                x-text="user.last_message?.body || (user.last_message?.file_path ? (user.last_message.file_path.match(/\.(webm|mp3|wav|ogg|m4a)$/i) ? 'Voice Message' : 'Photo') : (user.is_online ? 'Active now' : 'Offline'))">
                                            </div>
                                        </div>
                                        <template x-if="Number(user.unseen_count || 0) > 0">
                                            <span
                                                class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1.5 text-[10px] font-bold shadow-sm transition-transform group-hover:scale-110"
                                                :class="Number(activeUserId) === Number(user.id) ? 'bg-rose-100 text-rose-600' :
                                                    'bg-gradient-to-br from-rose-500 to-orange-400 text-white'"
                                                x-text="Number(user.unseen_count) > 99 ? '99+' : user.unseen_count"></span>
                                        </template>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </aside>

                <section
                    class="absolute inset-0 z-10 flex h-full w-full flex-col overflow-hidden bg-[#fcfbf9] transition-transform duration-300 ease-in-out md:static md:transform-none md:transition-none"
                    :class="(isMobileChatOpen || window.innerWidth >= 768) && activeConversationId ? 'translate-x-0' : 'translate-x-full md:translate-x-0'">
                    <template x-if="activeConversationId">
                        <div class="flex h-full flex-col overflow-hidden bg-[#fcfbf9]">
                            <div class="border-b border-orange-100 bg-white/80 backdrop-blur-md px-6 py-[18px]">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center">
                                        <button type="button" @click="isMobileChatOpen = false" class="mr-3 flex h-10 w-10 items-center justify-center rounded-[14px] bg-stone-50 text-stone-500 transition hover:bg-stone-100 hover:text-stone-800 md:hidden" title="Back to list">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="flex h-12 w-12 items-center justify-center rounded-[20px] bg-gradient-to-br from-rose-400 to-orange-300 text-white shadow-sm ring-[3px] ring-rose-50">
                                                <span class="text-[16px] font-bold tracking-tight"
                                                    x-text="initialFor(activeUserName)"></span>
                                            </div>
                                            <div class="flex flex-col">
                                                <div class="flex items-center gap-2">
                                                    <div id="online-dot" class="h-2.5 w-2.5 rounded-full bg-stone-300 transition-colors duration-300 shadow-[0_0_0_3px_rgba(214,211,209,0.2)] ml-0.5"></div>
                                                    <h3 class="text-[17.5px] font-bold text-stone-800 tracking-tight" x-text="activeUserName || 'User'"></h3>
                                                    <div id="online-text" class="text-[11px] font-bold uppercase tracking-widest text-stone-400 ml-1">Offline</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                            class="hidden md:flex h-10 w-10 items-center justify-center rounded-[14px] border border-stone-200/60 bg-white text-stone-400 transition hover:bg-stone-50 hover:text-stone-800 shadow-sm" title="Close chat">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div id="chat-box" class="flex-1 overflow-y-auto bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-orange-50/50 via-white to-rose-50/30 px-4 py-4 md:px-6 md:py-6 transition-opacity duration-300" style="opacity: 0;">
                                <!-- messages injected by JS -->
                                <template x-if="isLoadingMessages">
                                    <div class="space-y-6 pr-4">
                                        <div class="flex items-start gap-4">
                                            <div class="h-11 w-11 flex-shrink-0 animate-pulse rounded-2xl bg-orange-100/60"></div>
                                            <div class="flex flex-col gap-2.5 w-full">
                                                <div class="h-3 w-24 animate-pulse rounded-full bg-orange-100/60"></div>
                                                <div class="h-16 w-[65%] animate-pulse rounded-[22px] bg-white border border-rose-100/50 shadow-[0_4px_20px_-4px_rgba(251,146,60,0.05)]"></div>
                                            </div>
                                        </div>
                                        <div class="flex items-end justify-end">
                                             <div class="h-14 w-[50%] animate-pulse rounded-[22px] bg-gradient-to-r from-rose-200 to-orange-200 opacity-60"></div>
                                        </div>
                                        <div class="flex items-start gap-4 pt-2">
                                            <div class="h-11 w-11 flex-shrink-0 animate-pulse rounded-2xl bg-orange-100/60"></div>
                                            <div class="flex flex-col gap-2.5 w-full">
                                                <div class="h-3 w-20 animate-pulse rounded-full bg-orange-100/60"></div>
                                                <div class="h-14 w-[40%] animate-pulse rounded-[22px] bg-white border border-rose-100/50 shadow-[0_4px_20px_-4px_rgba(251,146,60,0.05)]"></div>
                                            </div>
                                        </div>
                                        <div class="flex items-end justify-end">
                                             <div class="h-12 w-[60%] animate-pulse rounded-[22px] bg-gradient-to-r from-rose-200 to-orange-200 opacity-60"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="shrink-0 border-t border-rose-100 bg-white/80 backdrop-blur-md px-4 py-4 md:px-6 md:py-5 shadow-[0_-10px_40px_-5px_rgba(251,146,60,0.05)]">
                                <div id="file-preview" x-cloak
                                    class="mb-3 hidden items-center justify-between rounded-2xl border px-4 py-3 shadow-sm"
                                    :class="isFileTooLarge ? 'border-rose-200 bg-rose-50' : 'border-orange-100 bg-orange-50/50'">
                                    <div class="flex items-center gap-3">
                                        <div class="flex flex-col">
                                            <span id="file-name" class="flex items-center gap-2 text-sm font-semibold" :class="isFileTooLarge ? 'text-rose-600' : 'text-stone-700'"></span>
                                            <template x-if="isFileTooLarge">
                                                <span class="mt-0.5 text-[10px] font-bold uppercase tracking-wider text-rose-500">File too large! Maximum limit is 10MB</span>
                                            </template>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeFile()"
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-stone-400 hover:bg-rose-100 hover:text-rose-600 transition-colors shadow-sm">&times;</button>
                                </div>
                                <div class="relative flex items-end gap-3" x-data="{ showEmojiPicker: false }">
                                    <div x-show="showEmojiPicker" @click.away="showEmojiPicker = false" x-cloak x-transition
                                        class="absolute bottom-16 left-0 z-50 w-72 rounded-[24px] border border-orange-100 bg-white/95 p-3 shadow-2xl backdrop-blur-xl">
                                        <div class="grid grid-cols-6 gap-1 max-h-60 overflow-y-auto p-1 custom-scrollbar">
                                            <template
                                                x-for="emoji in ['😊','😂','❤️','👍','😍','🙌','✨','🔥','✅','🚀','💡','👏','🙏','🎉','😎','🤔','😮','😢','🤝','📍','🤩','😇','🥳','🥺']"
                                                :key="emoji">
                                                <button type="button" @click="addEmoji(emoji); showEmojiPicker = false"
                                                    class="flex h-10 w-10 items-center justify-center rounded-xl text-xl transition hover:bg-orange-100 hover:scale-110 active:scale-95"
                                                    x-text="emoji"></button>
                                            </template>
                                        </div>
                                    </div>

                                    <input type="file" id="file-input" class="hidden"
                                        @change="handleFileSelect($event)">
                                    <button type="button" @click="openFilePicker()"
                                        class="flex h-[52px] w-[52px] shrink-0 items-center justify-center rounded-[20px] bg-orange-50 text-orange-500 shadow-sm transition-all hover:bg-orange-100 hover:text-orange-600 hover:shadow-md active:scale-95"
                                        title="Attach file">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                    </button>

                                    <button type="button" @click="showEmojiPicker = !showEmojiPicker"
                                        class="flex h-[52px] w-[52px] shrink-0 items-center justify-center rounded-[20px] bg-rose-50 text-rose-400 shadow-sm transition-all hover:bg-rose-100 hover:text-rose-500 hover:shadow-md active:scale-95"
                                        title="Add emoji">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>

                                    <div class="relative flex-1 group">
                                        <textarea id="message-input" x-model="draftMessage"
                                            @keydown.enter.prevent="sendMessage()" rows="1"
                                            class="min-h-[52px] w-full rounded-[24px] border border-orange-100 bg-white px-5 py-3.5 text-[15px] text-stone-700 shadow-sm outline-none transition-all focus:border-rose-300 focus:bg-white focus:ring-4 focus:ring-rose-100/50 resize-none"
                                            placeholder="Type your message..."></textarea>
                                    </div>
                                    <button type="button" @click="toggleVoiceRecord()"
                                        class="flex h-[52px] w-[52px] shrink-0 items-center justify-center rounded-[20px] shadow-[0_2px_8px_rgba(225,29,72,0.1)] border transition-all hover:scale-105 active:scale-95"
                                        :class="isRecordingVoice ? 'border-rose-200 bg-rose-100 text-rose-600' : 'border-rose-100 bg-rose-50 text-rose-500 hover:bg-rose-100 hover:text-rose-600'"
                                        title="Record Voice Message">
                                        <template x-if="!isRecordingVoice">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                            </svg>
                                        </template>
                                        <template x-if="isRecordingVoice">
                                            <span class="block h-3.5 w-3.5 rounded-sm bg-rose-600 animate-pulse"></span>
                                        </template>
                                    </button>
                                    <button type="button" @click="sendMessage()"
                                        :disabled="isFileTooLarge || (!draftMessage.trim() && !document.getElementById('file-input')?.files[0])"
                                        class="group relative flex h-[52px] w-[52px] shrink-0 items-center justify-center rounded-[20px] bg-gradient-to-br from-rose-500 to-orange-400 text-white shadow-[0_4px_14px_0_rgba(251,113,133,0.39)] transition-all hover:translate-y-[-2px] hover:shadow-[0_6px_20px_rgba(251,113,133,0.5)] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none disabled:pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="!activeConversationId">
                        <div class="flex flex-1 items-center justify-center px-6 py-10">
                            <div class="max-w-md text-center">
                                <div
                                    class="mx-auto flex h-20 w-20 items-center justify-center rounded-[28px] bg-stone-50 border border-stone-100 text-stone-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-3 3-3-3z" />
                                    </svg>
                                </div>
                                <h3 class="mt-6 text-xl font-bold tracking-tight text-stone-800">Message box blank</h3>
                                <p class="mt-2 text-[14px] font-medium leading-6 text-stone-500">Select a user from the left sidebar to
                                    open chat here.</p>
                            </div>
                        </div>
                    </template>
                </section>
            </div>

            <!-- Modal for medium and smaller screens -->
            <div x-cloak x-show="showChat" x-transition.opacity class="fixed inset-x-0 bottom-0 top-0 z-50 lg:hidden">
                <div class="fixed inset-x-0 bottom-0 top-0 mt-16 flex h-[calc(100dvh-4rem)] w-full flex-col overflow-hidden bg-white shadow-[0_24px_80px_-28px_rgba(15,23,42,0.35)]"
                    @click.stop>
                    <template x-if="activeConversationId">
                        <div class="flex h-full flex-col">
                            <div
                                class="border-b border-slate-200 bg-white px-6 py-5 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-300 text-slate-900">
                                        <span class="text-base font-semibold"
                                            x-text="initialFor(activeUserName)"></span>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-semibold text-slate-900"
                                            x-text="activeUserName || 'User'"></h3>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                                        aria-label="Close chat">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div id="chat-box-modal" class="flex-1 h-[calc(100dvh-220px)] min-h-[300px] overflow-y-auto px-6 py-6 transition-opacity duration-300" style="opacity: 0;">
                                <template x-if="isLoadingMessages">
                                    <div class="space-y-5">
                                        <div class="flex items-start gap-2">
                                            <div class="h-8 w-8 flex-shrink-0 animate-pulse rounded-xl bg-slate-200"></div>
                                            <div class="h-12 w-[70%] animate-pulse rounded-2xl bg-white border border-slate-200"></div>
                                        </div>
                                        <div class="flex justify-end">
                                            <div class="h-10 w-[50%] animate-pulse rounded-2xl bg-slate-800"></div>
                                        </div>
                                        <div class="flex items-start gap-2">
                                            <div class="h-8 w-8 flex-shrink-0 animate-pulse rounded-xl bg-slate-200"></div>
                                            <div class="h-14 w-[40%] animate-pulse rounded-2xl bg-white border border-slate-200"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="border-t border-slate-200 bg-white px-6 py-2">
                                <div id="file-preview-modal" x-cloak
                                    class="mb-3 hidden rounded-2xl border px-4 py-3"
                                    :class="isFileTooLarge ? 'border-rose-200 bg-rose-50' : 'border-slate-200 bg-slate-50'">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex flex-col">
                                            <span id="file-name-modal" class="flex items-center gap-2 text-sm font-medium" :class="isFileTooLarge ? 'text-rose-600' : 'text-slate-600'"></span>
                                            <template x-if="isFileTooLarge">
                                                <span class="mt-0.5 text-[10px] font-bold uppercase tracking-wider text-rose-500">File too large! Maximum limit is 10MB</span>
                                            </template>
                                        </div>
                                        <button type="button" @click="removeFile()"
                                            class="text-xl leading-none text-slate-400 transition hover:text-slate-700">&times;</button>
                                    </div>
                                </div>
                                <div class="flex items-end gap-3">
                                <div class="relative flex items-end gap-3" x-data="{ showEmojiPicker: false }">
                                    <div x-show="showEmojiPicker" @click.away="showEmojiPicker = false" x-cloak x-transition
                                        class="absolute bottom-16 left-0 z-50 w-64 rounded-[24px] border border-slate-200 bg-white/95 p-3 shadow-2xl backdrop-blur-md">
                                        <div class="grid grid-cols-5 gap-1">
                                            <template
                                                x-for="emoji in ['😊','😂','❤️','👍','😍','🙌','✨','🔥','✅','🚀','💡','👏','🙏','🎉','😎','🤔','😮','😢','🤝','📍']"
                                                :key="emoji">
                                                <button type="button" @click="addEmoji(emoji); showEmojiPicker = false"
                                                    class="flex h-10 w-10 items-center justify-center rounded-xl text-xl transition hover:bg-slate-100"
                                                    x-text="emoji"></button>
                                            </template>
                                        </div>
                                    </div>

                                    <input type="file" id="file-input-modal" class="hidden"
                                        @change="handleFileSelect($event)">
                                    <button type="button" @click="openFilePicker()"
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-slate-100"
                                        title="Attach file">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                    </button>

                                    <button type="button" @click="showEmojiPicker = !showEmojiPicker"
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 transition hover:bg-slate-100"
                                        title="Add emoji">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <input type="text" id="message-input-modal" x-model="draftMessage"
                                        @keydown.enter.prevent="sendMessage()"
                                        class="h-12 flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none focus:border-slate-400 focus:ring-4 focus:ring-slate-200"
                                        placeholder="Write a message...">
                                    
                                    <button type="button" @click="toggleVoiceRecord()"
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border transition shadow-sm"
                                        :class="isRecordingVoice ? 'border-rose-300 bg-rose-100 text-rose-600' : 'border-rose-200 bg-rose-50 text-rose-500 hover:bg-rose-100 hover:text-rose-600'"
                                        title="Record Voice Message">
                                        <template x-if="!isRecordingVoice">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                            </svg>
                                        </template>
                                        <template x-if="isRecordingVoice">
                                            <span class="block h-3.5 w-3.5 rounded-sm bg-rose-600 animate-pulse"></span>
                                        </template>
                                    </button>
                                    <button type="button" @click="sendMessage()"
                                        :disabled="isFileTooLarge || (!draftMessage.trim() && !document.getElementById('file-input-modal')?.files[0])"
                                        class="inline-flex h-12 shrink-0 items-center gap-2 rounded-2xl bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed">Send</button>
                                </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .message-row {
            display: flex;
            width: 100%;
            margin-bottom: 18px;
        }

        .message-row.my-message {
            justify-content: flex-end;
        }

        .message-row.their-message {
            justify-content: flex-start;
        }

        .message-container {
            max-width: min(78%, 680px);
            display: flex;
            flex-direction: column;
        }

        .message-row.my-message .message-container {
            align-items: flex-end;
        }

        .message-bubble {
            border-radius: 22px;
            padding: 14px 16px;
            font-size: 14px;
            line-height: 1.6;
            word-break: break-word;
            box-shadow: 0 18px 35px -26px rgba(15, 23, 42, .45);
        }

        .my-message .message-bubble {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            border-bottom-right-radius: 8px;
        }

        .their-message .message-bubble {
            background: #fff;
            color: #0f172a;
            border: 1px solid rgba(148, 163, 184, .28);
            border-bottom-left-radius: 8px;
        }

        .sender-name {
            margin-bottom: 6px;
            padding-left: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
        }

        .message-time {
            margin-top: 6px;
            padding: 0 6px;
            font-size: 11px;
            color: #64748b;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/axios@1.6.7/dist/axios.min.js"></script>
    <script>
        window.axios = axios;
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        window.axios.defaults.withCredentials = true;
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');

        function buildAttachmentHtml(fileUrl, fileName, isMe) {
            if (!fileUrl) return '';

            const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            const ext = fileUrl.split('.').pop().toLowerCase().split('?')[0];
            const isImage = imageExts.includes(ext);
            const isAudio = ['webm', 'mp3', 'wav', 'ogg', 'm4a', 'aac'].includes(ext);
            const safeName = fileName || 'Download file';

            if (isImage) {
                return `
                    <div style="margin-top:10px;">
                        <img src="${fileUrl}" alt="${safeName}" onload="const cb = this.closest('.flex-1'); if(cb) cb.scrollTop = cb.scrollHeight" style="max-width:220px; max-height:220px; border-radius:16px; border:1px solid rgba(148,163,184,.25); display:block;">
                        <a href="${fileUrl}" download="${safeName}" style="display:inline-flex;align-items:center;gap:8px;margin-top:10px;padding:10px 12px;border-radius:14px;background:${isMe ? 'rgba(255,255,255,0.14)' : '#f8fafc'};color:${isMe ? '#fff' : '#0f172a'};text-decoration:none;font-size:12px;">Download</a>
                    </div>
                `;
            }

            if (isAudio) {
                return `
                    <div style="margin-top: 8px; padding: 12px; background: ${isMe ? 'rgba(255,255,255,0.12)' : '#f1f5f9'}; border-radius: 16px; border: 1px solid ${isMe ? 'rgba(255,255,255,0.2)' : '#e2e8f0'};">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px; color: ${isMe ? '#fff' : '#64748b'};" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                            </svg>
                            <span style="color: ${isMe ? '#fff' : '#64748b'}; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">Voice Message</span>
                        </div>
                        <audio controls src="${fileUrl}" style="height: 36px; max-width: 240px; width: 100%; border-radius: 18px; outline: none;"></audio>
                    </div>
                `;
            }

            return `
                <a href="${fileUrl}" download="${safeName}" style="display:inline-flex;align-items:center;gap:8px;margin-top:10px;padding:10px 12px;border-radius:14px;background:${isMe ? 'rgba(255,255,255,0.14)' : '#f8fafc'};color:${isMe ? '#fff' : '#0f172a'};text-decoration:none;font-size:12px;">Download file</a>
            `;
        }

        function adminMessagesApp(config) {
            return {
                users: [],
                search: '',
                loadingUsers: true,
                activeUserId: Number(localStorage.getItem('admin_active_user_id')) || config.initialActiveUserId || 0,
                activeConversationId: Number(localStorage.getItem('admin_active_conversation_id')) || config.initialConversationId || 0,
                activeUserName: localStorage.getItem('admin_active_user_name') || config.initialOtherUserName || '',
                otherParticipantId: Number(localStorage.getItem('admin_other_participant_id')) || config.initialOtherParticipantId || 0,
                otherParticipantType: localStorage.getItem('admin_other_participant_type') || config.initialOtherParticipantType || '',
                draftMessage: '',
                isMobileChatOpen: false,
                showChat: Boolean(localStorage.getItem('admin_active_user_id')) || Boolean(config.showChat),
                searchTimer: null,
                pollTimer: null,
                usersRefreshTimer: null,
                onlineStatusTimer: null,
                heartbeatTimer: null,
                lastMessageId: 0,
                loadedMessageIds: new Set(),
                lastMarkedReadAt: 0,
                isLoadingMessages: false,
                lastLoadTime: 0, loadDebounceMs: 2000,
                lastUserLoadTime: 0, userLoadDebounceMs: 2500,
                isFileTooLarge: false,
                isRecordingVoice: false,
                voiceMediaRecorder: null,
                voiceAudioChunks: [],
                scrollToBottom(el, smooth = false) {
                    if (!el) return;
                    const doScroll = () => {
                        if (smooth) {
                            el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
                        } else {
                            el.scrollTop = el.scrollHeight;
                        }
                    };
                    // Immediate
                    doScroll();
                    // After paint
                    requestAnimationFrame(() => {
                        doScroll();
                    });
                },
                init() {
                    this.loadUsers();
                    this.sendHeartbeat();
                    this.startHeartbeatLoop();
                    this.startUsersRefreshLoop();
                    window.addEventListener('message-counter-sync', () => this.loadUsers(false));
                    window.addEventListener('focus', () => this.loadUsers(false));
                    window.addEventListener('storage', (event) => {
                        if (event.key === 'message-counter-sync') this.loadUsers(false);
                    });
                    document.addEventListener('visibilitychange', () => {
                        if (document.visibilityState === 'visible') this.loadUsers(false);
                    });

                    // Ensure only one audio plays at a time
                    document.addEventListener('play', (event) => {
                        const audios = document.getElementsByTagName('audio');
                        for (let i = 0, len = audios.length; i < len; i++) {
                            if (audios[i] != event.target) {
                                audios[i].pause();
                            }
                        }
                    }, true);

                    if (this.activeConversationId) {
                        this.loadMessages().then(() => {
                            const chatBox = document.getElementById('chat-box');
                            const chatBoxModal = document.getElementById('chat-box-modal');
                            this.scrollToBottom(chatBox);
                            this.scrollToBottom(chatBoxModal);
                        });
                        this.startPolling();
                        this.checkOnlineStatus();
                        this.startOnlineStatusLoop();
                        this.showChat = true;
                    }
                },
                initialFor(name) {
                    return name ? name.charAt(0).toUpperCase() : '?';
                },
                debouncedLoadUsers() {
                    clearTimeout(this.searchTimer);
                    this.searchTimer = setTimeout(() => this.loadUsers(), 250);
                },
                startUsersRefreshLoop() {
                    if (this.usersRefreshTimer) clearInterval(this.usersRefreshTimer);
                    this.usersRefreshTimer = setInterval(() => { const now = Date.now(); if (now - this.lastUserLoadTime >= this.userLoadDebounceMs) this.loadUsers(false); }, 3000);
                },
                syncCounterState(reason = 'refresh') {
                    try {
                        const payload = JSON.stringify({
                            reason,
                            conversationId: this.activeConversationId || null,
                            userId: this.activeUserId || null,
                            at: Date.now(),
                        });
                        localStorage.setItem('message-counter-sync', payload);
                        window.dispatchEvent(new CustomEvent('message-counter-sync', {
                            detail: JSON.parse(payload)
                        }));
                    } catch (error) {
                        console.error('Counter sync error:', error);
                    }
                },
                async loadUsers(showLoader = true) {
                    if (showLoader) this.loadingUsers = true;
                    const now = Date.now();
                    if (now - this.lastUserLoadTime < this.userLoadDebounceMs) {
                        if (showLoader) this.loadingUsers = false;
                        return;
                    }
                    this.lastUserLoadTime = now;
                    try {
                        const response = await fetch(`/admin/users/list?q=${encodeURIComponent(this.search.trim())}`, {
                            credentials: 'include',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });
                        const data = await response.json();
                        this.users = data.users || [];
                    } catch (error) {
                        console.error(error);
                        this.users = [];
                    } finally {
                        if (showLoader) this.loadingUsers = false;
                    }
                },
                async selectUser(user) {
                    this.showChat = true;
                    this.isMobileChatOpen = true;
                    this.activeUserId = user.id;
                    this.activeUserName = user.name;
                    localStorage.setItem('admin_active_user_id', user.id);
                    localStorage.setItem('admin_active_user_name', user.name);
                    try {
                        const response = await axios.get(`{{ route('admin.messages.conversation') }}?user=${user.id}`);
                        this.activeConversationId = response.data.conversation.id;
                        this.otherParticipantId = response.data.other_participant.id || 0;
                        this.otherParticipantType = response.data.other_participant.type || '';
                        this.activeUserName = response.data.other_participant.name || user.name;
                        
                        localStorage.setItem('admin_active_conversation_id', this.activeConversationId);
                        localStorage.setItem('admin_other_participant_id', this.otherParticipantId);
                        localStorage.setItem('admin_other_participant_type', this.otherParticipantType);
                        
                        history.replaceState({}, '', `{{ route('admin.messages') }}?user=${user.id}`);
                        
                        const chatBox = document.getElementById('chat-box');
                        const chatBoxModal = document.getElementById('chat-box-modal');
                        if (chatBox) chatBox.style.opacity = '0';
                        if (chatBoxModal) chatBoxModal.style.opacity = '0';
                        
                        await this.loadMessages();
                        await this.loadUsers(false);
                        this.startPolling();
                        this.checkOnlineStatus();
                        this.startOnlineStatusLoop();
                    } catch (error) {
                        console.error('Conversation load error:', error);
                        this.showChat = false;
                    }
                },
                renderMessage(message, autoScroll = true) {
                    const isLargeScreen = window.innerWidth >= 1024; // lg breakpoint
                    const chatBox = isLargeScreen ? document.getElementById('chat-box') : document.getElementById(
                        'chat-box-modal');
                    if (!chatBox) return;
                    const typeShort = message.sender_type ? message.sender_type.split('\\').pop().toLowerCase() : '';
                    const isMe = message.sender_id == config.userId && typeShort === config.userTypeShort;
                    const row = document.createElement('div');
                    const rowClass = isMe ? 'flex justify-end mb-4' : 'flex justify-start mb-4';
                    row.className = `w-full ${rowClass}`;
                    const container = document.createElement('div');
                    container.className = 'max-w-[80%] flex flex-col animate-[fadeInUp_0.3s_ease-out_forwards]';
                    let content = message.body || '';
                    if (content) {
                        content = `<p class="whitespace-pre-wrap text-[14.5px] leading-relaxed font-medium">${content}</p>`;
                    }
                    const fileUrl = message.file_url || '';
                    const fileName = message.file_name || 'Attachment';
                    content += buildAttachmentHtml(fileUrl, fileName, isMe);
                    const time = message.created_at ? new Date(message.created_at).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : '';
                    
                    const bubbleClasses = isMe 
                        ? 'rounded-[24px] px-5 py-3.5 shadow-sm transform transition-all duration-300 hover:-translate-y-0.5 bg-gradient-to-br from-rose-500 to-orange-400 text-white shadow-[0_4px_14px_0_rgba(251,113,133,0.39)] rounded-br-md border border-rose-400/20'
                        : 'rounded-[24px] px-5 py-3.5 shadow-sm transform transition-all duration-300 hover:-translate-y-0.5 bg-white text-stone-800 shadow-[0_4px_20px_-4px_rgba(251,146,60,0.08)] rounded-bl-md border border-orange-50';

                    const timeClass = isMe ? 'mt-1.5 px-2 text-[11px] font-medium text-rose-300 text-right' : 'mt-1.5 px-2 text-[11px] font-medium text-rose-300 text-left';
                    const senderHtml = isMe ? '' : `<p class="mb-1 px-3 text-[11px] uppercase tracking-wider font-bold text-rose-400">${message.sender_name || this.activeUserName}</p>`;

                    container.innerHTML = `${senderHtml}<div class="${bubbleClasses}">${content}</div><p class="${timeClass}">${time}</p>`;
                    row.appendChild(container);
                    chatBox.appendChild(row);
                    if (autoScroll) {
                        this.scrollToBottom(chatBox, true);
                    }
                },
async loadMessages() {
                    if (!this.activeConversationId) return;
                    const now = Date.now();
                    if (now - this.lastLoadTime < this.loadDebounceMs) return;
                    this.lastLoadTime = now;
                    this.isLoadingMessages = true;
                    try {
                        const response = await axios.get(`/messages/${this.activeConversationId}?t=${now}`);
                        const messages = response.data.messages || [];
                        const chatBox = document.getElementById('chat-box');
                        const chatBoxModal = document.getElementById('chat-box-modal');
                        
                        const isFirstLoad = this.loadedMessageIds.size === 0;
                        if (isFirstLoad) {
                            if (chatBox) chatBox.innerHTML = '';
                            if (chatBoxModal) chatBox.innerHTML = '';
                        }
                        
                        let newMessagesCount = 0;
                        messages.forEach((message) => {
                            if (!this.loadedMessageIds.has(message.id)) {
                                this.loadedMessageIds.add(message.id);
                                this.renderMessage(message, false);
                                newMessagesCount++;
                            }
                        });
                        
                        // Scroll to bottom after rendering
                        if (isFirstLoad || newMessagesCount > 0) {
                            this.scrollToBottom(chatBox, !isFirstLoad);
                            this.scrollToBottom(chatBoxModal, !isFirstLoad);
                        }
                        
                        if (isFirstLoad || newMessagesCount > 0) {
                            setTimeout(() => {
                                if (chatBox) chatBox.style.opacity = '1';
                                if (chatBoxModal) chatBoxModal.style.opacity = '1';
                            }, 50);
                        }

                        this.lastMessageId = messages.length ? messages[messages.length - 1].id : 0;
                        await this.markConversationAsRead(true);
                        await this.loadUsers(false);
                    } catch (error) {
                        console.error('Messages load error:', error);
                    } finally {
                        this.isLoadingMessages = false;
                    }
                },
                async sendMessage() {
                    if (!this.activeConversationId) return;
                    const isLargeScreen = window.innerWidth >= 1024;
                    const fileInput = isLargeScreen ? document.getElementById('file-input') : document.getElementById(
                        'file-input-modal');
                    const file = fileInput ? fileInput.files[0] : null;
                    const message = this.draftMessage.trim();
                    if (!message && !file) return;
                    const formData = new FormData();
                    if (message) formData.append('message', message);
                    if (file) formData.append('file', file);
                    formData.append('conversation_id', this.activeConversationId);
                    try {
                        const response = await axios.post('/send-message', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        });
                        this.draftMessage = '';
                        if (fileInput) fileInput.value = '';
                        const previewEl = isLargeScreen ? document.getElementById('file-preview') : document
                            .getElementById('file-preview-modal');
                        if (previewEl) previewEl.style.display = 'none';
                        this.loadedMessageIds.add(response.data.id);
                        this.lastMessageId = Math.max(this.lastMessageId, response.data.id);
                        this.renderMessage(response.data);
                        await this.loadUsers(false);
                        this.syncCounterState('sent');
                    } catch (error) {
                        console.error('Send error:', error);
                    }
                },
                openFilePicker() {
                    const isLargeScreen = window.innerWidth >= 1024;
                    const fileInput = isLargeScreen ? document.getElementById('file-input') : document.getElementById(
                        'file-input-modal');
                    if (fileInput) fileInput.click();
                },
                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    this.isFileTooLarge = file.size > 10 * 1024 * 1024;
                    
                    const isLargeScreen = window.innerWidth >= 1024;
                    const nameEl = isLargeScreen ? document.getElementById('file-name') : document.getElementById(
                        'file-name-modal');
                    const previewEl = isLargeScreen ? document.getElementById('file-preview') : document.getElementById(
                        'file-preview-modal');
                    if (nameEl) nameEl.innerHTML = '';
                    if (file.type.startsWith('image/')) {
                        const img = document.createElement('img');
                        img.src = URL.createObjectURL(file);
                        img.className = 'h-10 w-10 rounded-lg border border-slate-200 object-cover';
                        nameEl.appendChild(img);
                    }
                    const text = document.createElement('span');
                    text.textContent = file.name;
                    text.className = 'text-sm font-medium';
                    nameEl.appendChild(text);

                    if (previewEl) previewEl.style.display = 'block';
                },
                removeFile() {
                    const isLargeScreen = window.innerWidth >= 1024;
                    const fileInput = isLargeScreen ? document.getElementById('file-input') : document.getElementById(
                        'file-input-modal');
                    const previewEl = isLargeScreen ? document.getElementById('file-preview') : document.getElementById(
                        'file-preview-modal');
                    if (fileInput) fileInput.value = '';
                    if (previewEl) previewEl.style.display = 'none';
                    this.isFileTooLarge = false;
                },
                async markConversationAsRead(force = false) {
                    const now = Date.now();
                    if (!force && now - this.lastMarkedReadAt < 1500) return;
                    try {
                        await axios.post('/mark-read', {
                            conversation_id: this.activeConversationId
                        });
                        this.lastMarkedReadAt = now;
                        await this.loadUsers(false);
                        this.syncCounterState('read');
                    } catch (error) {
                        console.error('Mark read error:', error);
                    }
                },
                async toggleVoiceRecord() {
                    if (this.isRecordingVoice) {
                        if (this.voiceMediaRecorder) {
                            this.voiceMediaRecorder.stop();
                        }
                        this.isRecordingVoice = false;
                        return;
                    }

                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        this.voiceMediaRecorder = new MediaRecorder(stream);
                        this.voiceAudioChunks = [];

                        this.voiceMediaRecorder.ondataavailable = e => {
                            if (e.data.size > 0) this.voiceAudioChunks.push(e.data);
                        };

                        this.voiceMediaRecorder.onstop = () => {
                            stream.getTracks().forEach(track => track.stop());
                            if (this.voiceAudioChunks.length === 0) return;

                            const audioBlob = new Blob(this.voiceAudioChunks, { type: 'audio/webm' });
                            const file = new File([audioBlob], `voice_${Date.now()}.webm`, { type: 'audio/webm' });

                            this.sendVoiceFile(file);
                        };

                        this.voiceMediaRecorder.start();
                        this.isRecordingVoice = true;
                    } catch (err) {
                        alert('Microphone access is required to send voice messages.');
                        console.error(err);
                    }
                },
                async sendVoiceFile(file) {
                    if (!this.activeConversationId) return;
                    let formData = new FormData();
                    formData.append('file', file);
                    formData.append('conversation_id', this.activeConversationId);

                    try {
                        const response = await axios.post('/send-message', formData, {
                            headers: { 'Content-Type': 'multipart/form-data' }
                        });
                        if (response.data && response.data.id) {
                            this.loadedMessageIds.add(response.data.id);
                            this.lastMessageId = Math.max(this.lastMessageId, response.data.id);
                            this.renderMessage(response.data);
                            this.syncCounterState('sent');
                            await this.loadUsers(false);
                            setTimeout(() => {
                                const chatBox = document.getElementById('chat-box');
                                if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
                                const modalChat = document.getElementById('chat-box-modal');
                                if (modalChat) modalChat.scrollTop = modalChat.scrollHeight;
                            }, 50);
                        }
                    } catch (error) {
                        console.error('Record send error:', error);
                    }
                },
                startPolling() {
                    if (this.pollTimer) clearInterval(this.pollTimer);
                    this.pollTimer = setInterval(async () => {
                        if (!this.activeConversationId) return;
                        if (this.isLoadingMessages) return;
                        const now = Date.now();
                        if (now - this.lastLoadTime < this.loadDebounceMs) return;
                        this.lastLoadTime = now;
                        this.isLoadingMessages = true;
                        try {
                            const response = await axios.get(
                                `/messages/${this.activeConversationId}?t=${Date.now()}`);
                            const messages = response.data.messages || [];
                            if (!messages.length) {
                                this.isLoadingMessages = false;
                                return;
                            }
                            const newestId = messages[messages.length - 1].id;
                            if (newestId > this.lastMessageId) {
                                messages.forEach((message) => {
                                    if (!this.loadedMessageIds.has(message.id)) {
                                        this.loadedMessageIds.add(message.id);
                                        this.renderMessage(message);
                                    }
                                });
                                this.lastMessageId = newestId;
                                await this.markConversationAsRead();
                                await this.loadUsers(false);
                            }
                        } catch (error) {
                            console.error('Poll error:', error);
                        } finally {
                            this.isLoadingMessages = false;
                        }
                    }, 3000);
                },
                startHeartbeatLoop() {
                    if (this.heartbeatTimer) clearInterval(this.heartbeatTimer);
                    this.heartbeatTimer = setInterval(() => this.sendHeartbeat(), 8000);
                },
                startOnlineStatusLoop() {
                    if (this.onlineStatusTimer) clearInterval(this.onlineStatusTimer);
                    this.onlineStatusTimer = setInterval(() => this.checkOnlineStatus(), 5000);
                },
                async sendHeartbeat() {
                    try {
                        await axios.post('/online-heartbeat');
                    } catch (error) {
                        console.error('Heartbeat error:', error);
                    }
                },
                addEmoji(emoji) {
                    this.draftMessage += emoji;
                    const isLargeScreen = window.innerWidth >= 1024;
                    const input = isLargeScreen ? document.getElementById('message-input') : document.getElementById('message-input-modal');
                    if (input) input.focus();
                },
                async checkOnlineStatus() {
                    if (!this.otherParticipantId || !this.otherParticipantType) return;
                    try {
                        const response = await axios.get(
                            `/online-status/${this.otherParticipantId}/${this.otherParticipantType}`);
                        const dot = document.getElementById('online-dot');
                        const text = document.getElementById('online-text');
                        if (dot && text) {
                            dot.style.background = response.data.online ? '#10b981' : '#94a3b8';
                            text.textContent = response.data.online ? 'Online' : 'Offline';
                        }
                    } catch (error) {
                        console.error('Online status error:', error);
                    }
                },
                closeChat() {
                    this.activeConversationId = null;
                    this.activeUserId = null;
                    this.activeUserName = '';
                    this.otherParticipantId = 0;
                    this.otherParticipantType = '';
                    ['admin_active_user_id', 'admin_active_user_name', 'admin_active_conversation_id', 'admin_other_participant_id', 'admin_other_participant_type'].forEach(k => localStorage.removeItem(k));
                    this.loadedMessageIds = new Set();
                    this.lastMessageId = 0;
                    const chatBox = document.getElementById('chat-box');
                    if (chatBox) chatBox.innerHTML = '';
                    if (this.pollTimer) clearInterval(this.pollTimer);
                    if (this.onlineStatusTimer) clearInterval(this.onlineStatusTimer);
                }
            }
        }

        function buildAttachmentHtml(url, name, isMe) {
            if (!url) return '';
            const isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(url);
            const isAudio = /\.(webm|mp3|wav|ogg|m4a|aac)$/i.test(url);
            if (isImage) {
                const borderClass = isMe ? 'border-white/20' : 'border-rose-100';
                return `
                    <div class="mt-2 text-left">
                        <div class="group relative inline-block overflow-hidden rounded-[20px] shadow-sm transition-all duration-300 hover:shadow-md border ${borderClass}">
                            <img src="${url}" class="max-h-52 max-w-[280px] w-full object-cover cursor-zoom-in transition-transform duration-500 group-hover:scale-105" onclick="window.open('${url}', '_blank')" alt="Attachment">
                            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                        </div>
                    </div>
                `;
            }
            if (isAudio) {
                return `
                    <div class="mt-2 text-left">
                        <audio controls src="${url}" style="height: 44px; max-width: 240px; outline: none; border-radius: 22px;"></audio>
                    </div>
                `;
            }
            const btnClass = isMe ? 'bg-white/20 text-white hover:bg-white/30 backdrop-blur-sm' : 'bg-orange-50 text-orange-700 hover:bg-orange-100';
            return `
                <div class="mt-2 text-left">
                    <a href="${url}" target="_blank" class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold shadow-sm transition-transform hover:scale-105 ${btnClass}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        ${name || 'View attachment'}
                    </a>
                </div>
            `;
        }
    </script>
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('adminMessagesApp', (config) => ({
            ...adminMessagesApp(config)
        }));
    });
    </script>
</x-admin-layout>
