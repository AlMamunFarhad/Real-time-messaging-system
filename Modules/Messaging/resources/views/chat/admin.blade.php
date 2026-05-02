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
        <div class="pointer-events-none fixed right-4 top-4 z-[85] flex w-full max-w-sm flex-col gap-3 sm:right-6 sm:top-6">
            <template x-for="toast in notificationToasts" :key="toast.id">
                <button type="button" @click="openToastConversation(toast)"
                    class="pointer-events-auto overflow-hidden rounded-[24px] border border-white/80 bg-white/95 text-left shadow-[0_24px_60px_rgba(15,23,42,0.16)] ring-1 ring-orange-100/80 backdrop-blur-xl transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_28px_70px_rgba(15,23,42,0.2)]">
                    <div class="h-1.5 bg-gradient-to-r from-rose-500 via-orange-400 to-amber-400"></div>
                    <div class="flex items-start gap-3 px-4 py-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-orange-400 text-white shadow-lg shadow-orange-200/70">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-[12px] font-black uppercase tracking-[0.2em] text-orange-500" x-text="toast.label"></p>
                                <span class="rounded-full bg-orange-50 px-2 py-1 text-[10px] font-bold text-orange-500">Live</span>
                            </div>
                            <p class="mt-1 truncate text-[15px] font-bold tracking-tight text-stone-800" x-text="toast.sender"></p>
                            <p class="mt-1 line-clamp-2 text-[13px] leading-5 text-stone-500" x-text="toast.preview"></p>
                        </div>
                    </div>
                </button>
            </template>
        </div>
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
                            <template x-if="pinnedUsers.length">
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between px-1 pt-1">
                                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-rose-400">Pinned</p>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.14em] text-rose-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-3 w-3">
                                                <path d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" />
                                                <path d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v3.153l-1.082 1.082a.75.75 0 0 0 .53 1.28h3.354a.75.75 0 0 0 .53-1.28l-1.082-1.082V4.125c0-1.035-.84-1.875-1.875-1.875Z" />
                                            </svg>
                                            Pinned
                                        </span>
                                    </div>
                                    <template x-for="user in pinnedUsers" :key="'pinned-user-' + user.id">
                                        <button type="button" @click="selectUser(user)"
                                            class="group block w-full rounded-[20px] border border-rose-100 bg-rose-50/60 px-3.5 py-3 text-left transition-all duration-300 hover:bg-white hover:shadow-[0_2px_10px_-4_rgba(0,0,0,0.05)]"
                                            :class="Number(activeUserId) === Number(user.id) ? 'bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] ring-1 ring-rose-200/80 scale-[1.01] z-10 relative' : 'text-stone-800'">
                                            <div class="flex items-center gap-3.5">
                                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[16px] text-[14px] font-bold shadow-sm transition-all duration-300"
                                                    :class="Number(activeUserId) === Number(user.id) ? 'bg-gradient-to-br from-rose-400 to-orange-300 text-white shadow-md shadow-rose-200' : 'bg-rose-100 text-rose-500'"
                                                    x-text="initialFor(user.name)"></div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-1.5">
                                                        <div class="truncate text-[14.5px] font-bold text-stone-800 tracking-tight" x-text="user.name"></div>
                                                        <span class="inline-flex h-2 w-2 shrink-0 rounded-full transition-colors ml-1" :class="user.is_online ? 'bg-emerald-400 shadow-[0_0_0_3px_rgba(16,185,129,0.12)]' : 'bg-stone-200'"></span>
                                                    </div>
                                                    <div class="truncate text-[12px] mt-0.5 font-medium transition-colors" :class="Number(activeUserId) === Number(user.id) ? 'text-amber-600' : 'text-stone-500'" x-text="userPreview(user)"></div>
                                                </div>
                                                <div class="flex-shrink-0 flex items-center gap-2">
                                                    <template x-if="Number(user.unseen_count || 0) > 0">
                                                        <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-gradient-to-br from-rose-500 to-orange-400 px-1.5 text-[10px] font-bold text-white shadow-sm" x-text="Number(user.unseen_count) > 99 ? '99+' : user.unseen_count"></span>
                                                    </template>
                                                    <button @click.stop="togglePin(user)" type="button" class="p-1.5 rounded-lg text-rose-400 transition-all duration-200 hover:bg-rose-100" title="Unpin conversation">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
                                                            <path d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" />
                                                            <path d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v3.153l-1.082 1.082a.75.75 0 0 0 .53 1.28h3.354a.75.75 0 0 0 .53-1.28l-1.082-1.082V4.125c0-1.035-.84-1.875-1.875-1.875Z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </template>
                            <template x-if="otherUsers.length && pinnedUsers.length">
                                <p class="px-1 pt-3 text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">All Conversations</p>
                            </template>
                            <template x-for="user in (pinnedUsers.length ? otherUsers : users)" :key="user.id">
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
                                                x-text="userPreview(user)">
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 flex items-center gap-2">
                                            <template x-if="Number(user.unseen_count || 0) > 0">
                                                <span
                                                    class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1.5 text-[10px] font-bold shadow-sm transition-transform group-hover:scale-110"
                                                    :class="Number(activeUserId) === Number(user.id) ? 'bg-rose-100 text-rose-600' :
                                                        'bg-gradient-to-br from-rose-500 to-orange-400 text-white'"
                                                    x-text="Number(user.unseen_count) > 99 ? '99+' : user.unseen_count"></span>
                                            </template>
                                            <button @click.stop="togglePin(user)" type="button" 
                                                class="p-1.5 rounded-lg transition-all duration-200 hover:bg-amber-50 group/pin"
                                                :class="user.is_pinned ? 'text-amber-500' : 'text-stone-300 hover:text-amber-500'">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4 transition-transform group-hover/pin:scale-110">
                                                    <path d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" />
                                                    <path x-show="user.is_pinned" d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v3.153l-1.082 1.082a.75.75 0 0 0 .53 1.28h3.354a.75.75 0 0 0 .53-1.28l-1.082-1.082V4.125c0-1.035-.84-1.875-1.875-1.875Z" />
                                                    <path x-show="!user.is_pinned" fill-rule="evenodd" d="M11.47 2.47a.75.75 0 0 1 1.06 0l4.5 4.5a.75.75 0 0 1-1.06 1.06l-3.22-3.22V16.5a.75.75 0 0 1-1.5 0V4.81L8.03 8.03a.75.75 0 0 1-1.06-1.06l4.5-4.5ZM3 15.75a.75.75 0 0 1 .75.75v2.25a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5V16.5a.75.75 0 0 1 1.5 0v2.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V16.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" style="display: none;"/>
                                                </svg>
                                            </button>
                                        </div>
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
                                        <button type="button" @click="toggleSummary()" :disabled="isFetchingSummary" 
                                                class="flex items-center justify-center gap-2 rounded-[14px] border border-stone-200/60 bg-white px-4 py-2 text-[13px] font-bold text-stone-600 transition hover:bg-stone-50 hover:text-stone-900 shadow-sm disabled:opacity-50" 
                                                title="View Conversation Summary">
                                            <template x-if="!isFetchingSummary">
                                                <div class="flex items-center gap-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <span x-text="showSummary ? 'Hide Summary' : 'Summary'"></span>
                                                </div>
                                            </template>
                                            <template x-if="isFetchingSummary">
                                                <div class="flex items-center gap-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4 text-rose-500 animate-spin">
                                                        <path d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" />
                                                    </svg>
                                                    <span>...</span>
                                                </div>
                                            </template>
                                        </button>

                                        <button @click.stop="togglePin(users.find(u => Number(u.id) === Number(activeUserId)))" type="button" 
                                            class="flex h-10 w-10 items-center justify-center rounded-[14px] border border-stone-200/60 bg-white shadow-sm transition hover:bg-stone-50"
                                            :class="users.find(u => Number(u.id) === Number(activeUserId))?.is_pinned ? 'border-rose-200 bg-rose-50 text-rose-500' : 'text-stone-400 hover:text-rose-500'"
                                            :title="users.find(u => Number(u.id) === Number(activeUserId))?.is_pinned ? 'Pinned conversation' : 'Pin conversation'">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                                                <path d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" />
                                                <path x-show="users.find(u => Number(u.id) === Number(activeUserId))?.is_pinned" d="M18.375 2.25c-1.035 0-1.875.84-1.875 1.875v3.153l-1.082 1.082a.75.75 0 0 0 .53 1.28h3.354a.75.75 0 0 0 .53-1.28l-1.082-1.082V4.125c0-1.035-.84-1.875-1.875-1.875Z" />
                                            </svg>
                                        </button>

                                        <button type="button" @click="activeConversationId = 0; localStorage.removeItem('admin_active_conversation_id');"
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

                            <!-- Conversation Summary Panel -->
                            <template x-if="showSummary && chatSummary">
                                <div x-transition:enter="transition ease-out duration-300" 
                                     x-transition:enter-start="opacity-0 -translate-y-4" 
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 -translate-y-4"
                                     class="relative border-b border-orange-100 bg-white p-6 shadow-sm z-20 max-h-[40vh] overflow-y-auto custom-scrollbar">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-orange-400 text-white shadow-md shadow-rose-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="text-[15px] font-bold tracking-tight text-stone-800">Conversation Summary</h4>
                                            </div>
                                        </div>
                                        <button @click="showSummary = false" class="group flex h-8 w-8 items-center justify-center rounded-full hover:bg-rose-50 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-stone-400 group-hover:text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="mt-5 rounded-[20px] bg-gradient-to-br from-orange-50/30 to-rose-50/20 p-5 shadow-inner">
                                        <div class="prose prose-sm prose-stone max-w-none">
                                            <div class="text-[14px] leading-relaxed text-stone-700" x-html="parseSummary(chatSummary)"></div>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex items-center justify-between px-2">
                                        <div class="flex items-center gap-3">
                                            <p class="text-[11px] font-bold text-stone-400" x-text="'Refreshed on ' + new Date().toLocaleTimeString()"></p>
                                            <button @click="navigator.clipboard.writeText(chatSummary); $el.textContent = 'Copied!'; setTimeout(() => $el.textContent = 'Copy', 2000)" 
                                                    class="text-[11px] font-bold text-rose-500 hover:text-rose-600 underline decoration-rose-200 underline-offset-4">
                                                Copy
                                            </button>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="h-1.5 w-1.5 rounded-full bg-orange-400 animate-pulse"></span>
                                            <span class="text-[11px] font-black tracking-tighter text-orange-500 uppercase">Live Summary</span>
                                        </div>
                                    </div>
                                </div>
                            </template>

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

        window.downloadFile = function(url, filename, relativePath = null) {
            if (!url && !relativePath) return;
            
            let path = relativePath;
            if (!path && url) {
                // Extract relative path from absolute URL as fallback
                const match = url.match(/(uploads\/messages|chat-images)\/(.+)$/);
                if (match) {
                    path = (match[1] === 'uploads/messages' ? 'uploads/messages/' : 'chat-images/') + match[2].split('?')[0];
                }
            }

            if (path) {
                const downloadUrl = `/messages/download-attachment?path=${encodeURIComponent(path)}&name=${encodeURIComponent(filename || 'file')}`;
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                setTimeout(() => document.body.removeChild(link), 100);
                return;
            }

            // Fallback for external or non-standard URLs
            if (url) {
                fetch(url).then(r => r.blob()).then(blob => {
                    const blobUrl = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = blobUrl;
                    link.download = filename || 'download';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(blobUrl);
                }).catch(err => {
                    console.error('Download fallback failed:', err);
                    window.location.href = url;
                });
            }
        };

        function buildAttachmentHtml(fileUrl, fileName, isMe, filePath = null) {
            if (!fileUrl) return '';

            const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            const ext = fileUrl.split('.').pop().toLowerCase().split('?')[0];
            const isImage = imageExts.includes(ext);
            const isAudio = ['webm', 'mp3', 'wav', 'ogg', 'm4a', 'aac'].includes(ext);
            const safeName = fileName || 'Download file';
            const escapedFilePath = filePath ? filePath.replace(/'/g, "\\'") : '';

            if (isImage) {
                return `
                    <div style="position: relative; margin-top: 10px; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                        <img src="${fileUrl}" alt="${safeName}" onload="const cb = this.closest('.flex-1'); if(cb) cb.scrollTop = cb.scrollHeight" style="max-width:240px; display:block; border-radius:16px;">
                        ${!isMe ? `
                        <a href="javascript:void(0)" onclick="downloadFile('${fileUrl}', '${safeName}', '${escapedFilePath}')" style="position: absolute; bottom: 10px; right: 10px; display: flex; align-items: center; gap: 6px; padding: 8px 14px; background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; text-decoration: none; font-size: 12px; color: white; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.7)'; this.style.transform='scale(1.05)'" onmouseout="this.style.background='rgba(0,0,0,0.5)'; this.style.transform='scale(1)'">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download
                        </a>` : ''}
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
                <div style="display: flex; flex-direction: column; gap: 4px; margin-top: 8px;">
                    <a href="${fileUrl}" target="_blank" style="display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: ${isMe ? '#fff' : '#f8fafc'}; border: 1px solid ${isMe ? 'rgba(0,0,0,0.05)' : '#e2e8f0'}; border-radius: 16px; text-decoration: none; transition: all 0.2s; box-shadow: 0 4px 12px rgba(0,0,0,0.05);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)'">
                        <div style="width: 32px; height: 32px; background: #fff1f2; border-radius: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 18px; height: 18px; color: #f43f5e;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <span style="color: #e11d48; font-size: 13px; font-weight: 700; line-height: 1.2;">${safeName}</span>
                            <span style="color: #9f1239; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em; opacity: 0.8;">View Document</span>
                        </div>
                    </a>
                    ${!isMe ? `
                    <a href="javascript:void(0)" onclick="downloadFile('${fileUrl}', '${safeName}', '${escapedFilePath}')" style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 8px; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 10px; text-decoration: none; font-size: 11px; color: #e11d48; font-weight: 800; transition: all 0.2s;" onmouseover="this.style.background='#ffe4e6'" onmouseout="this.style.background='#fff1f2'">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        DOWNLOAD FILE
                    </a>` : ''}
                </div>
            `;
        }

        function adminMessagesApp(config) {
            return {
                users: [],
                pinnedUsers: [],
                otherUsers: [],
                notificationToasts: [],
                seenUserSnapshots: {},
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
                loadedMessageIds: new Set(),
                lastMarkedReadAt: 0,
                isLoadingMessages: false,
                isFetchingSummary: false,
                chatSummary: '',
                showSummary: false,
                lastLoadTime: 0, loadDebounceMs: 2000,
                lastUserLoadTime: 0, userLoadDebounceMs: 2500,
                isFileTooLarge: false,
                isRecordingVoice: false,
                voiceMediaRecorder: null,
                voiceAudioChunks: [],
                notificationPreview(payload) {
                    const body = String(payload?.body || '').trim();
                    if (body) return body.length > 120 ? `${body.slice(0, 120)}...` : body;
                    const fileName = String(payload?.file_name || '').trim();
                    if (fileName) {
                        if (/\.(webm|mp3|wav|ogg|m4a|aac)$/i.test(fileName)) return 'Voice Message';
                        if (/\.(jpg|jpeg|png|gif|webp|bmp|svg)$/i.test(fileName)) return 'Photo';

                        return 'Attachment';
                    }
                    return payload?.type === 'file' ? 'Attachment' : 'New message received';
                },
                pushNotificationToast(payload) {
                    if (window.__dashboardGlobalMessageNotifications) return;
                    const senderType = String(payload?.sender_type || '').split('\\').pop().toLowerCase();
                    const isOwnMessage = String(payload?.sender_id) === String(config.userId) && senderType === config.userTypeShort;
                    if (!payload?.id || isOwnMessage) return;

                    const alreadyVisible = this.notificationToasts.some((item) => String(item.messageId) === String(payload.id));
                    if (alreadyVisible) return;

                    const toast = {
                        id: `${payload.id}-${Date.now()}`,
                        messageId: payload.id,
                        conversationId: payload.conversation_id,
                        userId: payload.user_id || 0,
                        sender: payload.sender_name || 'Someone',
                        preview: this.notificationPreview(payload),
                        label: payload.user_name ? `Message from ${payload.user_name}` : 'New Message',
                    };

                    this.notificationToasts = [...this.notificationToasts, toast].slice(-4);

                    setTimeout(() => {
                        this.notificationToasts = this.notificationToasts.filter((item) => item.id !== toast.id);
                    }, 5000);
                },
                syncUserSnapshots(users, force = false) {
                    const nextSnapshots = {};

                    users.forEach((user) => {
                        const lastMessage = user.last_message || null;
                        const snapshot = {
                            lastMessageId: String(lastMessage?.id || ''),
                            unreadCount: Number(user.unread_count || 0),
                        };

                        const previousSnapshot = this.seenUserSnapshots[String(user.id)];
                        if (!force && previousSnapshot && snapshot.lastMessageId && previousSnapshot.lastMessageId !== snapshot.lastMessageId) {
                            this.pushNotificationToast({
                                id: lastMessage.id,
                                conversation_id: user.conversation_id || this.activeConversationId || 0,
                                user_id: user.id,
                                user_name: user.name,
                                sender_id: lastMessage.sender_id,
                                sender_type: lastMessage.sender_type,
                                sender_name: lastMessage.sender_name || user.name,
                                body: lastMessage.body || this.userPreview(user),
                                type: lastMessage.type || 'text',
                                file_name: lastMessage.file_name || null,
                            });
                        }

                        nextSnapshots[String(user.id)] = snapshot;
                    });

                    this.seenUserSnapshots = nextSnapshots;
                },
                async openToastConversation(toast) {
                    this.notificationToasts = this.notificationToasts.filter((item) => item.id !== toast.id);
                    if (toast.userId) {
                        const user = this.users.find((item) => String(item.id) === String(toast.userId));
                        if (user) {
                            await this.selectUser(user);
                        }
                    }
                },
                scrollToBottom(delay = 50) {
                    this.$nextTick(() => {
                        const panel = document.getElementById('chat-box') || document.getElementById('chat-box-modal');
                        if (!panel) return;
                        
                        const performScroll = (behavior = 'smooth') => {
                            panel.scrollTo({ top: panel.scrollHeight, behavior: behavior });
                        };

                        setTimeout(() => {
                            panel.scrollTop = panel.scrollHeight;
                            requestAnimationFrame(() => performScroll('smooth'));
                            
                            panel.querySelectorAll('img').forEach(img => {
                                if (!img.complete) img.addEventListener('load', () => performScroll('smooth'), { once: true });
                            });

                            setTimeout(() => performScroll('smooth'), 200);
                            setTimeout(() => performScroll('smooth'), 600);
                        }, delay);
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
                            this.scrollToBottom();
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
                messagePreview(lastMessage, fallback = 'No message yet') {
                    if (!lastMessage) return fallback;
                    if (lastMessage.body && String(lastMessage.body).trim() !== '') return String(lastMessage.body).trim();
                    if (lastMessage.file_path) return /\.(webm|mp3|wav|ogg|m4a|aac)$/i.test(lastMessage.file_path) ? 'Voice Message' : 'Photo';
                    return fallback;
                },
                userPreview(user) {
                    return this.messagePreview(user?.last_message, user?.is_online ? 'Active now' : 'Offline');
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
                        this.syncUserSnapshots(this.users, showLoader);
                        this.pinnedUsers = this.users.filter((user) => Boolean(user.is_pinned));
                        this.otherUsers = this.users.filter((user) => !user.is_pinned);
                    } catch (error) {
                        console.error(error);
                        this.users = [];
                        this.pinnedUsers = [];
                        this.otherUsers = [];
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
                    const filePath = message.file_path || '';
                    content += buildAttachmentHtml(fileUrl, fileName, isMe, filePath);
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
                            if (chatBoxModal) chatBoxModal.innerHTML = '';
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
                async togglePin(user) {
                    try {
                        let conversationId = user.conversation_id;
                        
                        if (!conversationId) {
                            // Fallback if not loaded
                            const response = await axios.get(`{{ route('admin.messages.conversation') }}?user=${user.id}`);
                            conversationId = response.data.conversation.id;
                        }

                        if (!conversationId) return;

                        const pinResponse = await axios.post('{{ route('messages.toggle-pin') }}', {
                            conversation_id: conversationId
                        });

                        if (pinResponse.data.success) {
                            user.is_pinned = pinResponse.data.is_pinned;
                            user.conversation_id = conversationId;
                            await this.loadUsers(false);
                        }
                    } catch (error) {
                        console.error('Toggle pin error:', error);
                    }
                },
                async toggleSummary() {
                    if (this.showSummary) {
                        this.showSummary = false;
                        return;
                    }
                    await this.fetchSummary();
                },
                async fetchSummary() {
                    if (!this.activeConversationId) return;
                    this.isFetchingSummary = true;
                    try {
                        const response = await axios.get(`/messages/${this.activeConversationId}/summary`);
                        this.chatSummary = response.data.summary;
                        this.showSummary = true;
                        
                        // Scroll to bottom to show the summary panel
                        this.scrollToBottom(document.getElementById('chat-box'), true);
                    } catch (error) {
                        console.error('Fetch summary failed', error);
                        alert('Failed to generate summary. Please check your connection.');
                    } finally {
                        this.isFetchingSummary = false;
                    }
                },
                parseSummary(text) {
                    if (!text) return '';
                    
                    let html = text
                        .replace(/\*\*(.*?)\*\*/g, '<strong class="text-stone-900 font-bold">$1</strong>') // Bold text
                        .replace(/^\s*###\s*(.*$)/gm, '<h5 class="text-[13px] font-bold text-stone-800 mt-3 mb-1">$1</h5>') // H3 headings
                        .replace(/^\s*##\s*(.*$)/gm, '<h4 class="text-[14px] font-bold text-stone-900 mt-4 mb-2 border-b border-stone-100 pb-1">$1</h4>') // H2 headings
                        .replace(/^\s*#\s*(.*$)/gm, '<h3 class="text-[16px] font-extrabold text-stone-900 mt-5 mb-3">$1</h3>') // H1 headings
                        .replace(/^\s*(\d+\.\s.*)$/gm, '<h5 class="text-[13px] font-bold text-stone-800 mt-3 mb-1">$1</h5>') // Numbered headings
                        .replace(/^\s*[-*]\s(.*)$/gm, '<div class="flex items-start gap-2 ml-2 my-0"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-rose-400"></span><span class="text-stone-700">$1</span></div>'); // Bullet points
                    
                    html = html.split('\n').map(line => {
                        const trimmed = line.trim();
                        if (trimmed === '') return '';
                        if (line.includes('<div') || line.includes('<h')) return line;
                        return line + '<br>';
                    }).join('\n');
                    
                    return html.replace(/(<\/div>|<\/h[1-6]>)<br>/g, '$1').replace(/<br>\n<div/g, '\n<div').trim();
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
    </script>
</x-admin-layout>
