<div x-data="messagingDashboard({
    currentId: {{ (int) $currentParticipantId }},
    currentType: @js($currentParticipantTypeShort),
    initialConversationId: {{ (int) $initialConversationId }},
    routes: {
        conversations: @js(route('messages.conversations')),
        messagesBase: @js(url('/messages')),
        send: @js(route('messages.send')),
        read: @js(route('messages.markRead')),
        clear: @js(route('messages.clear', ['conversationId' => '__CONVERSATION__'])),
        direct: @js(route('messages.direct')),
        participants: @js(route('messages.participants')),
        groupStore: @js(route('messages.groups.store')),
        groupsBase: @js(url('/messages/groups')),
        summaryBase: @js(url('/messages')),
        exitRoute: @js(Auth::guard('admin')->check() ? route('admin.dashboard') : route('dashboard'))
    }
})" x-init="init()" class="-m-6 mt-6 mx-auto max-w-7xl overflow-hidden">
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

    <div x-show="isWorkspaceVisible" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="mx-3 my-3 overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-[0_24px_80px_-28px_rgba(15,23,42,0.35)] md:mx-4 md:my-4 lg:mx-0 lg:my-0 lg:rounded-[32px]">
        <div class="relative flex h-[78vh] overflow-hidden md:grid md:grid-cols-[300px_minmax(0,1fr)] lg:grid-cols-[340px_minmax(0,1fr)]">
            <aside class="flex h-full w-full flex-col overflow-hidden border-b border-stone-200 bg-[#fafafa] md:w-auto md:border-b-0 md:border-r">
                <div class="border-b border-stone-200/60 bg-white/80 backdrop-blur-xl px-6 py-[22px]">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[10.5px] font-bold uppercase tracking-[0.2em] text-stone-400">Workspace</p>
                            <h3 class="mt-0.5 text-[18px] font-extrabold tracking-tight text-stone-800">Chats & Groups</h3>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-1.5 rounded-[20px] bg-stone-100/70 p-1.5 ring-1 ring-stone-900/5 shadow-inner">
                        <button type="button"
                            @click="activeTab = 'contacts'; showDirectPicker = true; $nextTick(() => $refs.directSearchInput?.focus())"
                            :class="activeTab === 'contacts' ? 'bg-white text-stone-800 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.08)] ring-1 ring-stone-200' : 'text-stone-500 hover:text-stone-800 hover:bg-white/60'"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-[16px] py-2.5 text-[11px] font-bold transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Contacts
                        </button>
                        <button type="button"
                            @click="activeTab = 'direct'; showDirectPicker = false"
                            :class="activeTab === 'direct' ? 'bg-white text-stone-800 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.08)] ring-1 ring-stone-200' : 'text-stone-500 hover:text-stone-800 hover:bg-white/60'"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-[16px] py-2.5 text-[11px] font-bold transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-3 3-3-3z" />
                            </svg>
                            Chats
                        </button>
                        <button type="button"
                            @click="activeTab = 'groups'; showDirectPicker = false"
                            :class="activeTab === 'groups' ? 'bg-white text-stone-800 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.08)] ring-1 ring-stone-200' : 'text-stone-500 hover:text-stone-800 hover:bg-white/60'"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-[16px] py-2.5 text-[11px] font-bold transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Groups
                        </button>
                    </div>
                </div>

                <div class="px-5 py-5 overflow-hidden">
                    <!-- Chats Tab -->
                    <div x-show="activeTab === 'direct'" class="flex flex-col h-[52vh]" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-x-2" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="mb-4 flex items-center justify-between px-1">
                            <p class="text-[10.5px] font-bold uppercase tracking-[0.2em] text-stone-400">Recent Conversations</p>
                            <template x-if="pinnedDirectConversations.length">
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.14em] text-amber-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M7 3.75A2.75 2.75 0 0 1 9.75 1h4.5A2.75 2.75 0 0 1 17 3.75V22a.75.75 0 0 1-1.2.6L12 19.75 8.2 22.6A.75.75 0 0 1 7 22V3.75Z" />
                                    </svg>
                                    Pinned
                                </span>
                            </template>
                        </div>

                        <div class="flex-1 space-y-1.5 overflow-y-auto pr-1 custom-scrollbar">
                            <template x-if="!directConversations.length">
                                <div class="rounded-[20px] border border-dashed border-stone-200 bg-stone-50 px-4 py-12 text-center mt-2">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white border border-stone-100 text-stone-300 mb-3 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                    </div>
                                    <p class="text-[12.5px] font-medium text-stone-400 leading-relaxed">No chats found.<br>Go to Contacts to start one.</p>
                                </div>
                            </template>
                            <template x-if="pinnedDirectConversations.length">
                                <div class="space-y-1.5">
                                    <p class="px-1 pt-1 text-[10px] font-bold uppercase tracking-[0.2em] text-amber-500">Pinned</p>
                                    <template x-for="conversation in pinnedDirectConversations" :key="'pd-' + conversation.id">
                                        <div role="button" tabindex="0" @click="selectConversation(conversation)" @keydown.enter.prevent="selectConversation(conversation)" @keydown.space.prevent="selectConversation(conversation)"
                                            class="group block w-full cursor-pointer rounded-[20px] px-4 py-3.5 text-left transition-all duration-300"
                                            :class="activeConversationId === conversation.id ? 'bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] ring-1 ring-amber-200/80 scale-[1.01] z-10 relative' : 'bg-amber-50/60 text-stone-800 hover:bg-white hover:shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)]'">
                                            <div class="relative min-w-0 flex-1">
                                                <div class="flex items-center justify-between gap-2">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <template x-if="conversation.is_online">
                                                            <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]"></span>
                                                        </template>
                                                        <div class="truncate text-[14.5px] font-bold tracking-tight text-stone-800" x-text="conversation.title"></div>
                                                    </div>
                                                    <div class="flex items-center gap-1.5">
                                                        <template x-if="Number(conversation.unread_count || 0) > 0">
                                                            <span class="inline-flex h-5 min-w-[1.25rem] shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-rose-500 to-orange-400 px-1.5 text-[10px] font-black text-white shadow-sm" x-text="conversation.unread_count"></span>
                                                        </template>
                                                        <button @click.stop="togglePin(conversation)" type="button" class="group/pin flex h-9 w-9 items-center justify-center rounded-[12px] to-rose-50 text-amber-600 transition-all duration-100" title="Unpin conversation">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200 group-hover/pin:scale-110" viewBox="0 0 24 24" fill="currentColor">
                                                                <path d="M9.75 3a.75.75 0 0 0-.75.75v2.19l-2.47 2.47a.75.75 0 0 0 .53 1.28h3.19v7.75a.75.75 0 0 0 1.28.53l.97-.97.97.97a.75.75 0 0 0 1.28-.53V9.69h3.19a.75.75 0 0 0 .53-1.28L16 5.94V3.75A.75.75 0 0 0 15.25 3h-5.5Z" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mt-0.5 truncate text-[12px] font-medium transition-colors" :class="activeConversationId === conversation.id ? 'text-amber-600' : 'text-stone-500'" x-text="conversationPreview(conversation, 'No message yet')"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="otherDirectConversations.length && pinnedDirectConversations.length">
                                <p class="px-1 pt-3 text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">All Chats</p>
                            </template>
                            <template x-for="conversation in (pinnedDirectConversations.length ? otherDirectConversations : directConversations)" :key="conversation.id">
                                <div role="button" tabindex="0" @click="selectConversation(conversation)" @keydown.enter.prevent="selectConversation(conversation)" @keydown.space.prevent="selectConversation(conversation)"
                                    class="group block w-full cursor-pointer rounded-[20px] px-4 py-3.5 text-left transition-all duration-300"
                                    :class="activeConversationId === conversation.id ? 'bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] ring-1 ring-stone-200/60 scale-[1.01] z-10 relative' : 'bg-transparent text-stone-800 hover:bg-white hover:shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)]'">
                                    <div class="relative min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <template x-if="conversation.is_online">
                                                    <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]"></span>
                                                </template>
                                                <div class="truncate text-[14.5px] font-bold tracking-tight text-stone-800" x-text="conversation.title"></div>
                                            </div>
                                            <template x-if="Number(conversation.unread_count || 0) > 0">
                                                <span class="inline-flex h-5 min-w-[1.25rem] shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-rose-500 to-orange-400 px-1.5 text-[10px] font-black text-white shadow-sm transition-transform group-hover:scale-110" x-text="conversation.unread_count"></span>
                                            </template>
                                            <button @click.stop="togglePin(conversation)" type="button"
                                                class="group/pin flex h-9 w-9 items-center justify-center rounded-[12px] to-rose-50 text-amber-600 transition-all duration-100"
                                                :class="conversation.is_pinned ? 'via-orange-50 to-rose-50 text-amber-600 shadow-sm' : 'border-transparent bg-transparent text-stone-300 hover:text-amber-500'"
                                                :title="conversation.is_pinned ? 'Unpin conversation' : 'Pin conversation'">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200 group-hover/pin:scale-110"
                                                    :fill="conversation.is_pinned ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9.75 3.75v2.19L7.28 8.41h3.22v7.78L12 14.69l1.5 1.5V8.41h3.22l-2.47-2.47V3.75h-4.5Z" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="mt-0.5 truncate text-[12px] font-medium transition-colors" :class="activeConversationId === conversation.id ? 'text-rose-500' : 'text-stone-400'"
                                            x-text="conversationPreview(conversation, 'No message yet')"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Groups Tab -->
                    <div x-show="activeTab === 'groups'" class="flex flex-col h-[52vh]" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="mb-5 flex items-center justify-between px-1">
                            <p class="text-[10.5px] font-bold uppercase tracking-[0.2em] text-stone-400">Team Groups</p>
                            <button type="button" @click="openCreateGroup()" class="inline-flex h-[30px] items-center justify-center rounded-xl bg-gradient-to-br from-rose-400 to-orange-400 px-3.5 text-[10.5px] font-black tracking-widest text-white shadow-md shadow-rose-200 transition-all hover:scale-105 hover:shadow-lg">
                                + NEW
                            </button>
                        </div>

                        <div class="flex-1 space-y-1.5 overflow-y-auto pr-1 custom-scrollbar">
                            <template x-if="!groupConversations.length">
                                <div class="rounded-[20px] border border-dashed border-stone-200 bg-stone-50 px-4 py-12 text-center text-[12.5px] font-medium text-stone-400 mt-2">
                                    Collaborate with your team<br>in group chats.
                                </div>
                            </template>
                            <template x-if="pinnedGroupConversations.length">
                                <div class="space-y-1.5">
                                    <p class="px-1 pt-1 text-[10px] font-bold uppercase tracking-[0.2em] text-amber-500">Pinned Groups</p>
                                    <template x-for="conversation in pinnedGroupConversations" :key="'pg-' + conversation.id">
                                        <div role="button" tabindex="0" @click="selectConversation(conversation)" @keydown.enter.prevent="selectConversation(conversation)" @keydown.space.prevent="selectConversation(conversation)"
                                            class="group block w-full cursor-pointer rounded-[20px] px-4 py-3.5 text-left transition-all duration-300"
                                            :class="activeConversationId === conversation.id ? 'bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] ring-1 ring-amber-200/80 scale-[1.01] z-10 relative' : 'bg-amber-50/60 text-stone-800 hover:bg-white hover:shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)]'">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between gap-3">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <div class="truncate text-[14.5px] font-bold tracking-tight text-stone-800" x-text="conversation.title"></div>
                                                    </div>
                                                    <div class="flex items-center gap-1.5">
                                                        <template x-if="Number(conversation.unread_count || 0) > 0">
                                                            <span class="inline-flex h-5 min-w-[1.25rem] shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-rose-500 to-orange-400 px-1.5 text-[10px] font-black text-white shadow-sm" x-text="conversation.unread_count"></span>
                                                        </template>
                                                        <button @click.stop="togglePin(conversation)" type="button" class="group/pin flex h-5 w-5 items-center justify-center rounded-[12px] to-rose-50 text-amber-600 transition-all duration-100" title="Unpin group">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 transition-transform duration-200 group-hover/pin:scale-110" viewBox="0 0 24 24" fill="currentColor">
                                                                <path d="M9.75 3a.75.75 0 0 0-.75.75v2.19l-2.47 2.47a.75.75 0 0 0 .53 1.28h3.19v7.75a.75.75 0 0 0 1.28.53l.97-.97.97.97a.75.75 0 0 0 1.28-.53V9.69h3.19a.75.75 0 0 0 .53-1.28L16 5.94V3.75A.75.75 0 0 0 15.25 3h-5.5Z" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mt-0.5 truncate text-[12px] font-medium transition-colors" :class="activeConversationId === conversation.id ? 'text-amber-600' : 'text-stone-500'" x-text="conversationPreview(conversation, 'Start a discussion')"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="otherGroupConversations.length && pinnedGroupConversations.length">
                                <p class="px-1 pt-3 text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400">All Groups</p>
                            </template>
                            <template x-for="conversation in (pinnedGroupConversations.length ? otherGroupConversations : groupConversations)" :key="conversation.id">
                                <div role="button" tabindex="0" @click="selectConversation(conversation)" @keydown.enter.prevent="selectConversation(conversation)" @keydown.space.prevent="selectConversation(conversation)"
                                    class="group block w-full cursor-pointer rounded-[20px] px-4 py-3.5 text-left transition-all duration-300"
                                    :class="activeConversationId === conversation.id ? 'bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] ring-1 ring-stone-200/60 scale-[1.01] z-10 relative' : 'bg-transparent text-stone-800 hover:bg-white hover:shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)]'">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="truncate text-[14.5px] font-bold tracking-tight text-stone-800" x-text="conversation.title"></div>
                                            <template x-if="Number(conversation.unread_count || 0) > 0">
                                                <span class="inline-flex h-5 min-w-[1.25rem] shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-rose-500 to-orange-400 px-1.5 text-[10px] font-black text-white shadow-sm transition-transform group-hover:scale-110" x-text="conversation.unread_count"></span>
                                            </template>
                                            <button @click.stop="togglePin(conversation)" type="button"
                                                class="group/pin ml-1 flex h-9 w-9 items-center justify-center rounded-[12px] border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm"
                                                :class="conversation.is_pinned ? 'border-amber-200/80 bg-gradient-to-br from-amber-50 via-orange-50 to-rose-50 text-amber-600 shadow-sm' : 'border-transparent bg-transparent text-stone-300 hover:border-amber-100 hover:bg-amber-50/80 hover:text-amber-500'"
                                                :title="conversation.is_pinned ? 'Unpin group' : 'Pin group'">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 transition-transform duration-200 group-hover/pin:scale-110"
                                                    :fill="conversation.is_pinned ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9.75 3.75v2.19L7.28 8.41h3.22v7.78L12 14.69l1.5 1.5V8.41h3.22l-2.47-2.47V3.75h-4.5Z" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="mt-0.5 truncate text-[12px] font-medium transition-colors" :class="activeConversationId === conversation.id ? 'text-rose-500' : 'text-stone-400'" x-text="conversationPreview(conversation, 'Start a discussion')"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <!-- Contacts Tab -->
                    <div x-show="activeTab === 'contacts'" class="flex flex-col h-[52vh]" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0">
                        <p class="text-[10.5px] font-bold uppercase tracking-[0.2em] text-stone-400 mb-4 px-1">Find Members</p>

                        <div class="relative px-1">
                            <input x-ref="directSearchInput" x-model="directSearch" @input.debounce.250ms="loadDirectCandidates()" type="text" placeholder="Type a name..." class="w-full rounded-[16px] border border-transparent bg-white px-4 py-3 text-[13.5px] font-medium text-stone-700 outline-none focus:border-stone-200 focus:ring-4 focus:ring-stone-100 shadow-[0_2px_12px_-2px_rgba(0,0,0,0.08)] transition-all placeholder:text-stone-400">
                            <div class="absolute right-5 top-1/2 -translate-y-1/2 text-stone-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <div class="flex-1 mt-5 space-y-1.5 overflow-y-auto pr-1 custom-scrollbar">
                            <template x-if="!directCandidates.length && directSearch">
                                <div class="px-4 py-12 text-center text-[12.5px] font-medium text-stone-400">User not found. Try another name.</div>
                            </template>
                            <template x-for="item in directCandidates" :key="item.type + '-' + item.id">
                                <button type="button" @click="startDirectChat(item)" class="group flex w-full items-center justify-between rounded-[20px] bg-transparent px-4 py-3 text-left transition-all duration-300 hover:bg-white hover:shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <template x-if="item.is_online">
                                                <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]"></span>
                                            </template>
                                            <div class="truncate text-[14.5px] font-bold tracking-tight text-stone-800" x-text="item.name"></div>
                                        </div>
                                        <div class="mt-0.5 truncate text-[12px] font-medium text-stone-400" x-text="item.preview_text || item.subtitle"></div>
                                    </div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-[12px] bg-stone-50 text-stone-400 shadow-sm transition-all duration-300 group-hover:bg-gradient-to-br group-hover:from-rose-400 group-hover:to-orange-300 group-hover:text-white group-hover:shadow-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </aside>
            <section class="absolute inset-0 z-10 flex h-full w-full flex-col overflow-hidden bg-[#fcfbf9] transition-transform duration-300 ease-in-out md:static md:transform-none md:transition-none md:overflow-hidden"
                :class="(isMobileChatOpen || window.innerWidth >= 768) && activeConversationId ? 'translate-x-0' : 'translate-x-full md:translate-x-0'">
                <template x-if="activeConversation">
                    <div class="flex h-full flex-col overflow-hidden bg-[#fcfbf9]">
                        <div class="border-b border-orange-100 bg-white/80 backdrop-blur-md px-6 py-[18px]">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="flex w-full flex-col gap-4 sm:w-auto sm:flex-row sm:items-center">
                                    <div class="flex items-center">
                                        <button type="button" @click="isMobileChatOpen = false" class="mr-3 flex h-10 w-10 items-center justify-center rounded-[14px] bg-stone-50 text-stone-500 transition hover:bg-stone-100 hover:text-stone-800 md:hidden" title="Back to chats">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        <div class="flex items-center gap-4">
                                            <div class="relative">
                                                <div class="flex h-12 w-12 items-center justify-center rounded-[20px] bg-gradient-to-br from-rose-400 to-orange-300 text-white shadow-sm ring-[3px] ring-rose-50">
                                                    <span class="text-[16px] font-bold tracking-tight" x-text="initialFor(activeConversation.title)"></span>
                                                </div>
                                                <template x-if="activeConversation.is_online">
                                                    <span class="absolute -right-0.5 -bottom-0.5 block h-3.5 w-3.5 rounded-full border-[2.5px] border-white bg-emerald-400 shadow-sm"></span>
                                                </template>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <h3 class="text-[17.5px] font-bold text-stone-800 tracking-tight" x-text="activeConversation.title"></h3>
                                                </div>
                                                <p class="text-[12.5px] font-medium text-stone-500 mt-0.5 ml-1" x-text="activeConversation.is_group ? ((groupDetails.members?.length || activeConversation.members_count || 0) + ' members in this group') : (activeConversation.is_online ? 'Active now' : 'Personal chat')"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 justify-end w-full sm:w-auto">
                                    <template x-if="!activeConversation.is_group">
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="startCall('audio')"
                                                class="flex h-10 w-10 items-center justify-center rounded-[14px] border border-emerald-100 bg-emerald-50 text-emerald-600 shadow-sm transition hover:bg-emerald-100 hover:shadow-md"
                                                title="Audio call">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 0 1 2-2h2.28a2 2 0 0 1 1.948 1.553l.57 2.28a2 2 0 0 1-.502 1.86L7.414 10.586a16.001 16.001 0 0 0 6 6l1.893-1.882a2 2 0 0 1 1.86-.502l2.28.57A2 2 0 0 1 21 16.72V19a2 2 0 0 1-2 2h-1C9.716 21 3 14.284 3 6V5Z" />
                                                </svg>
                                            </button>
                                            <button type="button" @click="startCall('video')"
                                                class="flex h-10 w-10 items-center justify-center rounded-[14px] border border-sky-100 bg-sky-50 text-sky-600 shadow-sm transition hover:bg-sky-100 hover:shadow-md"
                                                title="Video call">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 10 4.553-2.276A1 1 0 0 1 21 8.618v6.764a1 1 0 0 1-1.447.894L15 14m-9 4h8a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2Z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                    <button type="button" @click="clearConversation()" :disabled="isClearingConversation"
                                        class="flex items-center justify-center gap-2 rounded-[14px] border border-rose-100 bg-rose-50 px-4 py-2 text-[13px] font-bold text-rose-500 transition hover:bg-rose-100 shadow-sm disabled:cursor-not-allowed disabled:opacity-60"
                                        title="Clear chat">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7 5 7m5-3h4m-5 7v6m4-6v6m5-10v12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7h12Z" />
                                        </svg>
                                        <span>Clear Chat</span>
                                    </button>
                                    <button @click.stop="togglePin(activeConversation)" type="button" 
                                        class="group/pin flex h-10 w-10 items-center justify-center rounded-[14px] border shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                                        :class="activeConversation?.is_pinned ? 'border-amber-200/80 bg-gradient-to-br from-amber-50 via-orange-50 to-rose-50 text-amber-600' : 'border-stone-200/60 bg-white text-stone-400 hover:border-amber-100 hover:bg-amber-50/80 hover:text-amber-500'"
                                        :title="activeConversation?.is_pinned ? 'Pinned conversation' : 'Pin conversation'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200 group-hover/pin:scale-110"
                                            :fill="activeConversation?.is_pinned ? 'currentColor' : 'none'"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9.75 3.75v2.19L7.28 8.41h3.22v7.78L12 14.69l1.5 1.5V8.41h3.22l-2.47-2.47V3.75h-4.5Z" />
                                        </svg>
                                    </button>

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
                                                <svg class="h-4 w-4 animate-spin text-rose-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span>Processing...</span>
                                            </div>
                                        </template>
                                    </button>
                                    <button type="button" @click="closeWorkspace()" class="hidden md:flex h-10 w-10 items-center justify-center rounded-[14px] border border-stone-200/60 bg-white text-stone-400 transition hover:bg-stone-50 hover:text-stone-800 shadow-sm" title="Close Workspace">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                    <template x-if="activeConversation.is_group && groupDetails.can_manage"><button type="button" @click="openManageMembers()" class="rounded-[14px] border border-stone-200/60 bg-white px-4 py-2 text-[13px] font-bold text-stone-600 transition hover:bg-stone-50 hover:text-stone-900 shadow-sm">Manage</button></template>
                                    <template x-if="activeConversation.is_group"><button type="button" @click="leaveGroup()" class="rounded-[14px] border border-rose-100 bg-rose-50 px-4 py-2 text-[13px] font-bold text-rose-500 transition hover:bg-rose-100 shadow-sm">Leave</button></template>
                                </div>
                            </div>
                        </div>
                        <div id="messages-panel" class="flex-1 overflow-y-auto bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-orange-50/50 via-white to-rose-50/30 px-4 py-4 md:px-6 md:py-6">
                            <template x-if="loadingMessages">
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
                            <template x-if="!loadingMessages && !messages.length">
                                <div class="rounded-[28px] border border-dashed border-slate-300 bg-white/80 px-6 py-12 text-center text-slate-500">
                                    <p class="text-base font-semibold text-slate-700">Conversation is empty</p>
                                    <p class="mt-2 text-sm">Send the first message and start the discussion.</p>
                                </div>
                            </template>
                            <div id="messages-list" class="space-y-4" x-show="!loadingMessages" x-cloak>
                                <template x-for="message in messages" :key="message.id">
                                    <div class="flex" :class="isMine(message) ? 'justify-end' : 'justify-start'">
                                        <div class="max-w-[80%]">
                                            <template x-if="!isMine(message)">
                                                <p class="mb-1 px-3 text-[11px] uppercase tracking-wider font-bold text-rose-400" x-text="message.sender_name"></p>
                                            </template>
                                            <div class="relative rounded-[24px] px-5 py-3.5 shadow-sm transform transition-all duration-300 hover:-translate-y-0.5"
                                                :class="isMine(message) 
                                                    ? 'bg-blue-50 text-blue-800 shadow-[0_4px_12px_rgba(37,99,235,0.05)] rounded-br-md border border-blue-100' 
                                                    : 'bg-white text-stone-800 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] rounded-bl-md border border-stone-100'">
                                                <template x-if="isMine(message)">
                                                    <div class="absolute right-3 top-3 z-10" @click.outside="closeMessageMenu(message.id)">
                                                        <button type="button" @click.stop="toggleMessageMenu(message.id)"
                                                            class="flex h-8 w-8 items-center justify-center rounded-full transition"
                                                            :class="openMessageMenuId === message.id ? 'bg-white text-blue-600 shadow-sm' : 'text-blue-400 hover:bg-white/80 hover:text-blue-600'">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                                                <circle cx="12" cy="5" r="1.8" />
                                                                <circle cx="12" cy="12" r="1.8" />
                                                                <circle cx="12" cy="19" r="1.8" />
                                                            </svg>
                                                        </button>
                                                        <div x-show="openMessageMenuId === message.id" x-cloak x-transition
                                                            class="absolute right-0 mt-2 w-36 overflow-hidden rounded-2xl border border-slate-200 bg-white py-1.5 shadow-[0_18px_40px_rgba(15,23,42,0.14)]">
                                                            <template x-if="message.body">
                                                                <button type="button" @click="startInlineEdit(message)"
                                                                    class="flex w-full items-center gap-2 px-3.5 py-2 text-left text-[12px] font-semibold text-stone-600 transition hover:bg-blue-50 hover:text-blue-700">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                                                    </svg>
                                                                    <span>Edit</span>
                                                                </button>
                                                            </template>
                                                            <button type="button" @click="requestDeleteMessage(message)"
                                                                class="flex w-full items-center gap-2 px-3.5 py-2 text-left text-[12px] font-semibold text-rose-500 transition hover:bg-rose-50 hover:text-rose-600">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7 5 7m5-3h4m-5 7v6m4-6v6m5-10v12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7h12Z" />
                                                                </svg>
                                                                <span>Delete</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template x-if="isEditingMessage(message.id)">
                                                    <div class="pr-10">
                                                        <textarea x-model="editingMessageDraft" rows="3"
                                                            class="w-full rounded-[18px] border border-blue-200 bg-white px-4 py-3 text-[14.5px] font-medium text-stone-700 outline-none transition focus:border-blue-300 focus:ring-4 focus:ring-blue-100/70 resize-none"
                                                            @keydown.escape.prevent="cancelInlineEdit()"
                                                            @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); saveInlineEdit(message); }"></textarea>
                                                        <div class="mt-3 flex items-center justify-end gap-2">
                                                            <button type="button" @click="cancelInlineEdit()"
                                                                class="rounded-full border border-stone-200 bg-white px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500 transition hover:bg-stone-50">
                                                                Cancel
                                                            </button>
                                                            <button type="button" @click="saveInlineEdit(message)" :disabled="isUpdatingMessage || !editingMessageDraft.trim()"
                                                                class="rounded-full bg-blue-600 px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
                                                                Save
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template x-if="!isEditingMessage(message.id) && message.body">
                                                    <p class="pr-10 whitespace-pre-wrap text-[14.5px] leading-relaxed font-medium" x-text="message.body"></p>
                                                </template>
                                                <template x-if="message.file_url">
                                                    <div class="mt-2 text-left">
                                                        <template x-if="message.file_url && (message.file_url.match(/\.(jpg|jpeg|png|gif|webp)$/i))">
                                                            <div class="group relative mt-2 inline-block overflow-hidden rounded-[20px] shadow-sm transition-all duration-300 hover:shadow-md" :class="isMine(message) ? 'border border-white/20' : 'border border-rose-100'">
                                                                <img :src="message.file_url" @load="const cb = $el.closest('.flex-1'); if(cb) cb.scrollTop = cb.scrollHeight;" class="max-h-52 max-w-[280px] w-full object-cover cursor-zoom-in transition-transform duration-500 group-hover:scale-105" @click="window.open(message.file_url, '_blank')" alt="Attachment">
                                                                <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                                                            </div>
                                                        </template>
                                                        <template x-if="message.file_url && (message.file_url.match(/\.(webm|mp3|wav|ogg|m4a)$/i))">
                                                            <div class="mt-2 flex flex-col gap-2 p-2 rounded-2xl bg-white/40 border border-white/20">
                                                                <div class="flex items-center gap-2 px-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                                                    </svg>
                                                                    <span class="text-[11px] font-bold uppercase tracking-widest text-rose-500">Voice Message</span>
                                                                </div>
                                                                <audio controls class="h-8 max-w-[240px] w-full" :src="message.file_url"></audio>
                                                            </div>
                                                        </template>
                                                        <template x-if="!(message.file_url && (message.file_url.match(/\.(jpg|jpeg|png|gif|webp|webm|mp3|wav|ogg|m4a)$/i)))">
                                                            <a :href="message.file_url" target="_blank" class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold shadow-sm transition-transform hover:scale-105"
                                                                :class="isMine(message) ? 'bg-white/20 text-white hover:bg-white/30 backdrop-blur-sm' : 'bg-orange-50 text-orange-700 hover:bg-orange-100'">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                </svg>
                                                                <span x-text="message.file_name || 'View attachment'"></span>
                                                            </a>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                            <p class="mt-1.5 px-2 text-[11px] font-medium" :class="isMine(message) ? 'text-right text-blue-400' : 'text-left text-stone-400'" x-text="formatTime(message.created_at)"></p>
                                        </div>
                                    </div>
                                </template>

                                <!-- Conversation Summary Panel -->
                                <template x-if="showSummary && chatSummary">
                                    <div x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 translate-y-4 transform scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 transform scale-100"
                                        x-transition:leave="transition ease-in duration-200"
                                        x-transition:leave-start="opacity-100 translate-y-0 transform scale-100"
                                        x-transition:leave-end="opacity-0 translate-y-4 transform scale-95"
                                        class="relative mt-8 mb-4 overflow-hidden rounded-[28px] border border-orange-100 bg-white p-6 shadow-[0_20px_50px_rgba(251,146,60,0.12)] ring-1 ring-orange-50/50">
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
                            </div>
                        </div>
                        <div class="shrink-0 border-t border-rose-100 bg-white/80 backdrop-blur-md px-4 py-4 md:px-6 md:py-5 shadow-[0_-10px_40px_-5px_rgba(251,146,60,0.05)]">
                            <div x-show="selectedFileName" x-cloak class="mb-3 flex items-center justify-between rounded-2xl border px-4 py-3 shadow-sm" :class="isFileTooLarge ? 'border-rose-200 bg-rose-50' : 'border-orange-100 bg-orange-50/50'">
                                <div class="flex items-center gap-3">
                                    <template x-if="selectedFilePreview">
                                        <div class="h-10 w-10 shrink-0 overflow-hidden rounded-xl border border-orange-200 shadow-sm">
                                            <img :src="selectedFilePreview" class="h-full w-full object-cover" alt="Preview">
                                        </div>
                                    </template>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold" :class="isFileTooLarge ? 'text-rose-600' : 'text-stone-700'" x-text="selectedFileName"></span>
                                        <template x-if="isFileTooLarge">
                                            <span class="mt-0.5 text-[10px] font-bold uppercase tracking-wider text-rose-500">File too large! Maximum limit is 10MB</span>
                                        </template>
                                    </div>
                                </div>
                                <button type="button" @click="clearFile()" class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-stone-400 hover:bg-rose-100 hover:text-rose-600 transition-colors shadow-sm">&times;</button>
                            </div>
                            <div class="relative flex items-end gap-3" x-data="{ showEmojiPicker: false }">
                                <div x-show="showEmojiPicker" @click.away="showEmojiPicker = false" x-cloak x-transition class="absolute bottom-16 left-0 z-50 w-72 rounded-[24px] border border-orange-100 bg-white/95 p-3 shadow-2xl backdrop-blur-xl">
                                    <div class="grid grid-cols-6 gap-1 max-h-60 overflow-y-auto p-1 custom-scrollbar">
                                        <template x-for="emoji in ['😊','😂','❤️','👍','😍','🙌','✨','🔥','✅','🚀','💡','👏','🙏','🎉','😎','🤔','😮','😢','🤝','📍','🤩','😇','🥳','🥺','🤫','🤯','😴','🧡','💛','💚','💙','💜','🤍','💘','❣️','🎈','🎁','💎','📱','💻','☕','🌍','⚡','💪','🌈','🌟','💯','🔥','✨','😀','😁','😆','😅','🤣','🙂','🙃','😉','😊','😇','🥰','😍','🤩','😘','😗','😚','😙','😋','😛','😜','🤪','😝','🤑','🤗','🤭','🤫','🤔','🤐','🤨','😐','😑','😶','😏','😒','🙄','😬','🤥','😌','😔','😪','🤤','😴','😷','🤒','🤕','🤢','🤮','🤧','🥵','🥶','🥴','😵','🤯','🤠','🥳','😎','🤓','🧐','😕','😟','🙁','☹️','😮','😯','😲','😳','🥺','😦','😧','😨','😰','😥','😢','😭','😱','😖','😣','😞','😓','😩','😫','🥱','😤','😡','😠','🤬','😈','👿','💀','☠️','💩','🤡','👹','👺','👻','👽','👾','🤖','😺','😸','😹','😻','😼','😽','🙀','😿','😾']" :key="emoji">
                                            <button type="button" @click="addEmoji(emoji); showEmojiPicker = false" class="flex h-10 w-10 items-center justify-center rounded-xl text-xl transition hover:bg-orange-100 hover:scale-110 active:scale-95" x-text="emoji"></button>
                                        </template>
                                    </div>
                                </div>

                                <input type="file" x-ref="fileInput" class="hidden" @change="pickFile">
                                <button type="button" @click="$refs.fileInput.click()" class="flex h-[52px] w-[52px] shrink-0 items-center justify-center rounded-[20px] bg-orange-50 text-orange-500 shadow-sm transition-all hover:bg-orange-100 hover:text-orange-600 hover:shadow-md active:scale-95" title="Attach file">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                </button>

                                <button type="button" @click="showEmojiPicker = !showEmojiPicker" class="flex h-[52px] w-[52px] shrink-0 items-center justify-center rounded-[20px] bg-rose-50 text-rose-400 shadow-sm transition-all hover:bg-rose-100 hover:text-rose-500 hover:shadow-md active:scale-95" title="Add emoji">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>

                                <div class="relative flex-1 group">
                                    <textarea x-model="draftMessage" x-ref="messageInput" @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); sendMessage(); }" rows="1" class="min-h-[52px] w-full rounded-[24px] border border-orange-100 bg-white px-5 py-3.5 text-[15px] text-stone-700 shadow-sm outline-none transition-all focus:border-rose-300 focus:bg-white focus:ring-4 focus:ring-rose-100/50 resize-none" placeholder="Type your message..."></textarea>
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

                                <button type="button" @click="sendMessage()" :disabled="isFileTooLarge || (!draftMessage.trim() && !selectedFile)"
                                    class="group relative flex h-[52px] w-[52px] shrink-0 items-center justify-center rounded-[20px] bg-gradient-to-br from-rose-500 to-orange-400 text-white shadow-[0_4px_14px_0_rgba(251,113,133,0.39)] transition-all hover:translate-y-[-2px] hover:shadow-[0_6px_20px_rgba(251,113,133,0.5)] disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
                <template x-if="!activeConversation">
                    <div class="flex h-full flex-1 items-center justify-center px-8 py-12">
                        <div class="max-w-lg text-center">
                            <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-[30px] bg-stone-50 border border-stone-100 text-4xl text-stone-400">💬</div>
                            <h3 class="mt-6 text-2xl font-bold tracking-tight text-stone-800">Pick a conversation</h3>
                            <p class="mt-3 text-[14px] font-medium leading-7 text-stone-500">Open a direct message, create a group, or search contacts to start chatting.</p>
                        </div>
                    </div>
                </template>
            </section>
        </div>
    </div>

    <div x-cloak x-show="showCreateGroupModal" class="fixed inset-0 z-50 flex items-start justify-center bg-slate-950/40 p-4 backdrop-blur-sm overflow-y-auto custom-scrollbar">
        <div class="my-auto w-full max-w-2xl rounded-[28px] bg-white p-6 shadow-2xl transition-all">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-xl font-semibold text-slate-900">Create Group</h3>
                    <p class="mt-1 text-sm text-slate-500">Admins and users can join the same group.</p>
                </div><button type="button" @click="closeCreateGroup()" class="text-2xl text-slate-400 hover:text-slate-700">&times;</button>
            </div>
            <div class="mt-6 grid gap-4">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Group Name <span class="text-rose-500">*</span></label>
                    <input x-model="groupForm.name" @input="groupNameError = false" type="text" placeholder="Enter group name..." class="w-full rounded-2xl border bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition-all focus:border-slate-400 focus:ring-4 focus:ring-slate-200" :class="groupNameError ? 'border-rose-400 bg-rose-50' : 'border-slate-200'">
                    <template x-if="groupNameError">
                        <p class="mt-1.5 px-1 text-xs font-medium text-rose-500">Group name is required to create a group.</p>
                    </template>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Description</label>
                    <textarea x-model="groupForm.description" rows="3" placeholder="What is this group about? (Optional)" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-slate-400 focus:ring-4 focus:ring-slate-200"></textarea>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Search Members</label>
                    <input x-model="groupMemberSearch" @input.debounce.250ms="loadGroupCandidates()" type="text" placeholder="Search by name or email" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-slate-400 focus:ring-4 focus:ring-slate-200">
                </div>
            </div>
            <div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px]">
                <div class="max-h-72 space-y-2 overflow-y-auto rounded-3xl border border-slate-200 bg-slate-50 p-3"><template x-for="item in groupCandidates" :key="item.type + '-' + item.id"><button type="button" @click="toggleMember(item)" class="flex w-full items-center justify-between rounded-2xl bg-white px-4 py-3 text-left transition hover:bg-slate-100">
                            <div>
                                <div class="text-sm font-semibold text-slate-900" x-text="item.name"></div>
                                <div class="text-xs text-slate-500" x-text="item.subtitle + '  ' + item.email"></div>
                            </div><span class="rounded-full px-3 py-1 text-xs font-medium" :class="isSelectedMember(item) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-500'" x-text="isSelectedMember(item) ? 'Selected' : 'Add'"></span>
                        </button></template></div>
                <div class="rounded-3xl border border-slate-200 bg-white p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Selected</p>
                    <div class="mt-3 space-y-2"><template x-if="!groupForm.participants.length">
                            <p class="text-sm text-slate-500">Choose at least one member besides you.</p>
                        </template><template x-for="item in groupForm.participants" :key="item.type + '-' + item.id">
                            <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-3 py-2">
                                <div>
                                    <div class="text-sm font-medium text-slate-800" x-text="item.name"></div>
                                    <div class="text-xs text-slate-500" x-text="item.subtitle"></div>
                                </div><button type="button" @click="toggleMember(item)" class="text-sm text-rose-500">Remove</button>
                            </div>
                        </template></div>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3"><button type="button" @click="closeCreateGroup()" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600">Cancel</button><button type="button" @click="createGroup()" class="rounded-2xl bg-slate-900 px-5 py-2 text-sm font-semibold text-white">Create group</button></div>
        </div>
    </div>

    <div x-cloak x-show="showManageMembersModal && activeConversation?.is_group" class="fixed inset-0 z-50 flex items-start justify-center bg-slate-950/40 p-4 backdrop-blur-sm overflow-y-auto custom-scrollbar">
        <div class="my-auto w-full max-w-2xl rounded-[28px] bg-white p-6 shadow-2xl transition-all">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-xl font-semibold text-slate-900">Manage Members</h3>
                    <p class="mt-1 text-sm text-slate-500" x-text="activeConversation?.title"></p>
                </div><button type="button" @click="showManageMembersModal = false" class="text-2xl text-slate-400 hover:text-slate-700">&times;</button>
            </div>
            <div class="mt-5"><input x-model="manageMemberSearch" @input.debounce.250ms="loadGroupCandidates()" type="text" placeholder="Search users or admins to add" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-slate-400 focus:ring-4 focus:ring-slate-200"></div>
            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                <div class="max-h-80 space-y-2 overflow-y-auto rounded-3xl border border-slate-200 bg-slate-50 p-3"><template x-for="member in groupDetails.members || []" :key="member.type + '-' + member.id">
                        <div class="flex items-center justify-between rounded-2xl bg-white px-4 py-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-900" x-text="member.name"></div>
                                <div class="text-xs text-slate-500" x-text="member.type + '  ' + member.role"></div>
                            </div><button type="button" @click="removeMember(member)" class="text-sm text-rose-500">Remove</button>
                        </div>
                    </template></div>
                <div class="max-h-80 space-y-2 overflow-y-auto rounded-3xl border border-slate-200 bg-white p-3"><template x-for="item in availableNewMembers()" :key="item.type + '-' + item.id"><button type="button" @click="addMembers([item])" class="flex w-full items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 text-left transition hover:bg-slate-100">
                            <div>
                                <div class="text-sm font-semibold text-slate-900" x-text="item.name"></div>
                                <div class="text-xs text-slate-500" x-text="item.subtitle + '  ' + item.email"></div>
                            </div><span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-500">Add</span>
                        </button></template></div>
            </div>
        </div>
    </div>

    <div x-cloak x-show="confirmDialog.open" class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/45 p-4 backdrop-blur-sm">
        <div x-show="confirmDialog.open" x-transition class="w-full max-w-md overflow-hidden rounded-[30px] border border-white/70 bg-white shadow-[0_30px_80px_rgba(15,23,42,0.22)]">
            <div class="bg-gradient-to-br from-slate-50 via-white to-amber-50 px-6 py-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 via-orange-400 to-amber-400 text-white shadow-lg shadow-orange-200/80">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7 5 7m5-3h4m-5 7v6m4-6v6m5-10v12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7h12Z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-[20px] font-extrabold tracking-tight text-stone-900" x-text="confirmDialog.title"></h3>
                        <p class="mt-2 text-[14px] leading-6 text-stone-500" x-text="confirmDialog.description"></p>
                    </div>
                    <button type="button" @click="closeConfirmDialog()" class="flex h-9 w-9 items-center justify-center rounded-full text-stone-400 transition hover:bg-white hover:text-stone-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="px-6 pb-6">
                <div class="rounded-[22px] border border-amber-100 bg-gradient-to-r from-amber-50 to-rose-50 px-4 py-3 text-[12px] font-semibold text-amber-700">
                    This action cannot be undone.
                </div>
                <div class="mt-5 flex items-center justify-end gap-3">
                    <button type="button" @click="closeConfirmDialog()"
                        class="rounded-[16px] border border-stone-200 bg-white px-4 py-2.5 text-[13px] font-bold text-stone-600 transition hover:bg-stone-50 hover:text-stone-800">
                        Cancel
                    </button>
                    <button type="button" @click="confirmDialog.onConfirm && confirmDialog.onConfirm()" :disabled="confirmDialog.loading"
                        class="inline-flex items-center justify-center gap-2 rounded-[16px] bg-gradient-to-r from-rose-500 via-orange-400 to-amber-400 px-4 py-2.5 text-[13px] font-bold text-white shadow-lg shadow-orange-200/80 transition hover:translate-y-[-1px] hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60">
                        <template x-if="!confirmDialog.loading">
                            <span x-text="confirmDialog.confirmText"></span>
                        </template>
                        <template x-if="confirmDialog.loading">
                            <span>Working...</span>
                        </template>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div x-cloak x-show="showCallModal" class="fixed inset-0 z-[75] flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
        <div class="w-full max-w-4xl overflow-hidden rounded-[32px] border border-white/10 bg-slate-950 text-white shadow-[0_30px_100px_rgba(15,23,42,0.45)]">
            <div class="flex items-center justify-between border-b border-white/10 px-6 py-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-orange-300" x-text="callMode === 'video' ? 'Video Call' : 'Audio Call'"></p>
                    <h3 class="mt-1 text-xl font-bold text-white" x-text="callPeerName || activeConversation?.title || 'Calling'"></h3>
                    <p class="mt-1 text-sm text-slate-300" x-text="callStatus"></p>
                    <template x-if="microphoneUnavailable && !callConnected">
                        <p class="mt-1 text-sm text-rose-200">Microphone unavailable — you can hear the other side but cannot speak.</p>
                    </template>
                    <template x-if="cameraUnavailable && !callConnected">
                        <p class="mt-1 text-sm text-amber-200">Camera unavailable — you can hear the side, but you will not be seen.</p>
                    </template>
                </div>
                <button type="button" @click="endCall()" class="flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="grid gap-4 bg-slate-900 p-4 md:grid-cols-[minmax(0,1fr)_220px]">
                <div class="relative flex min-h-[320px] items-center justify-center overflow-hidden rounded-[28px] bg-slate-800">
                    <video x-ref="remoteVideo" autoplay playsinline class="h-full min-h-[320px] w-full object-cover" :class="callMode === 'audio' ? 'hidden' : 'block'"></video>
                    <div x-show="callMode === 'audio' || !remoteStreamActive" class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-[radial-gradient(circle_at_top,_rgba(251,146,60,0.2),_transparent_55%)]">
                        <div class="flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-rose-500 to-orange-400 text-3xl font-bold text-white shadow-lg"
                             :class="incomingCall && !callConnected ? 'ring-4 ring-orange-300/60 animate-pulse' : ''"
                             x-text="initialFor(callPeerName || activeConversation?.title)"></div>
                        <p class="text-lg font-semibold text-white" x-text="callPeerName || activeConversation?.title"></p>
                    </div>
                </div>
                <div class="flex flex-col gap-4">
                    <div class="relative overflow-hidden rounded-[24px] bg-slate-800">
                        <video x-ref="localVideo" autoplay playsinline muted class="h-[180px] w-full object-cover" :class="callMode === 'video' && !cameraUnavailable ? 'block' : 'hidden'"></video>
                        <div x-show="callMode === 'video' && cameraUnavailable" class="flex h-[180px] items-center justify-center bg-slate-800 text-slate-300">Camera unavailable</div>
                        <div x-show="callMode === 'audio'" class="flex h-[180px] items-center justify-center bg-slate-800 text-slate-300">Microphone only</div>
                    </div>
                    <div class="grid grid-cols-2 gap-3" x-show="incomingCall && !callConnected">
                        <button type="button" @click="acceptIncomingCall()" class="rounded-[18px] bg-emerald-500 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-600">Accept</button>
                        <button type="button" @click="rejectIncomingCall()" class="rounded-[18px] bg-rose-500 px-4 py-3 text-sm font-bold text-white transition hover:bg-rose-600">Reject</button>
                    </div>
                    <div class="flex items-center gap-3" x-show="!incomingCall || callConnected">
                        <button type="button" @click="toggleMute()" class="flex-1 rounded-[18px] bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/20" x-text="isMuted ? 'Unmute' : 'Mute'"></button>
                        <button type="button" @click="toggleCamera()" x-show="callMode === 'video'" class="flex-1 rounded-[18px] bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/20" x-text="isCameraOff ? 'Camera On' : 'Camera Off'"></button>
                        <button type="button" @click="endCall()" class="rounded-[18px] bg-rose-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-rose-600">End</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function messagingDashboard(config) {
        return {
            conversations: [],
            directConversations: [],
            groupConversations: [],
            pinnedDirectConversations: [],
            pinnedGroupConversations: [],
            otherDirectConversations: [],
            otherGroupConversations: [],
            directCandidates: [],
            groupCandidates: [],
            messages: [],
            notificationToasts: [],
            seenConversationSnapshots: {},
            activeConversationId: localStorage.getItem('user_active_conversation_id') && localStorage.getItem('user_active_conversation_id') !== 'null' ? localStorage.getItem('user_active_conversation_id') : (config.initialConversationId || null),
            activeConversation: null,
            groupDetails: {
                members: []
            },
            loadingMessages: false,
            isMobileChatOpen: false,
            activeTab: localStorage.getItem('messaging_active_tab') || 'direct',
            isWorkspaceVisible: true,
            exitRoute: config.routes.exitRoute || '/',
            showDirectPicker: (localStorage.getItem('messaging_active_tab') === 'contacts'),
            directSearch: '',
            draftMessage: '',
            showCallModal: false,
            callMode: 'audio',
            callStatus: 'Ready',
            callPeerId: null,
            callPeerType: null,
            callPeerName: '',
            incomingCall: false,
            callConnected: false,
            incomingCallRingingAckSent: false,
            microphoneUnavailable: false,
            cameraUnavailable: false,
            remoteStreamActive: false,
            isMuted: false,
            isCameraOff: false,
            ringAudioContext: null,
            ringOscillatorInterval: null,
            isRinging: false,
            localStream: null,
            remoteStream: null,
            peerConnection: null,
            signalPollTimer: null,
            pendingIncomingSignal: null,
            selectedFile: null,
            selectedFileName: '',
            isFileTooLarge: false,
            selectedFilePreview: null,
            isRecordingVoice: false,
            showCreateGroupModal: false,
            showManageMembersModal: false,
            groupMemberSearch: '',
            manageMemberSearch: '',
            groupNameError: false,
            openMessageMenuId: null,
            editingMessageId: null,
            editingMessageDraft: '',
            isFetchingSummary: false,
            isUpdatingMessage: false,
            isClearingConversation: false,
            chatSummary: '',
            showSummary: false,
            confirmDialog: {
                open: false,
                title: '',
                description: '',
                confirmText: 'Confirm',
                loading: false,
                onConfirm: null
            },
            groupForm: {
                name: '',
                description: '',
                participants: []
            },
            pollTimer: null,
            lastLoadTime: 0,
            loadDebounceMs: 2500,
            lastConversationLoadTime: 0,
            conversationLoadDebounceMs: 3000,
            isFetchingPin: false,
            async togglePin(conversation) {
                if (!conversation || this.isFetchingPin) return;
                this.isFetchingPin = true;
                try {
                    const response = await axios.post('/messages/toggle-pin', {
                        conversation_id: conversation.id
                    });
                    if (response.data.success) {
                        conversation.is_pinned = response.data.is_pinned;
                        await this.loadConversations(true);
                    }
                } catch (e) {
                    console.error('Pin toggle failed', e);
                } finally {
                    this.isFetchingPin = false;
                }
            },
            messageUpdateRoute(messageId) {
                return `${config.routes.messagesBase}/${messageId}`;
            },
            messageDeleteRoute(messageId) {
                return `${config.routes.messagesBase}/${messageId}`;
            },
            clearConversationRoute() {
                return config.routes.clear.replace('__CONVERSATION__', this.activeConversationId);
            },
            async ensureMediaStream(mode) {
                if (this.localStream) return this.localStream;

                this.microphoneUnavailable = false;
                this.cameraUnavailable = false;
                const constraints = {
                    audio: true,
                    video: mode === 'video',
                };

                try {
                    this.localStream = await navigator.mediaDevices.getUserMedia(constraints);
                } catch (error) {
                    let gotStream = false;
                    if (mode === 'video') {
                        try {
                            this.localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
                            this.cameraUnavailable = true;
                            gotStream = true;
                        } catch (audioOnlyError) {
                            this.microphoneUnavailable = true;
                        }

                        if (!gotStream) {
                            try {
                                this.localStream = await navigator.mediaDevices.getUserMedia({ audio: false, video: true });
                                this.microphoneUnavailable = true;
                                this.cameraUnavailable = false;
                                gotStream = true;
                            } catch (videoOnlyError) {
                                if (!this.microphoneUnavailable) {
                                    this.cameraUnavailable = true;
                                }
                            }
                        }
                    }

                    if (!gotStream) {
                        if (mode === 'audio') {
                            this.microphoneUnavailable = true;
                        }
                        if (!this.microphoneUnavailable && !this.cameraUnavailable) {
                            this.microphoneUnavailable = true;
                            this.cameraUnavailable = true;
                        }
                        this.localStream = new MediaStream();
                    }

                    if (this.microphoneUnavailable && this.cameraUnavailable) {
                        this.callStatus = 'Microphone and camera unavailable — listen only';
                    } else if (this.microphoneUnavailable) {
                        this.callStatus = 'Microphone unavailable — listen only';
                    } else if (this.cameraUnavailable) {
                        this.callStatus = 'Camera unavailable — audio-only';
                    }
                }

                if (this.$refs.localVideo) {
                    this.$refs.localVideo.srcObject = this.localStream;
                }

                return this.localStream;
            },
            createPeerConnection() {
                const connection = new RTCPeerConnection({
                    iceServers: [
                        { urls: 'stun:stun.l.google.com:19302' },
                        { urls: 'stun:stun1.l.google.com:19302' },
                    ],
                });

                connection.ontrack = (event) => {
                    this.remoteStream = event.streams[0];
                    this.remoteStreamActive = true;
                    if (this.$refs.remoteVideo) {
                        this.$refs.remoteVideo.srcObject = this.remoteStream;
                    }
                };

                connection.onicecandidate = async (event) => {
                    if (!event.candidate || !this.callPeerId || !this.callPeerType || !this.activeConversationId) return;
                    await axios.post('/voice-call/ice-candidate', {
                        conversation_id: this.activeConversationId,
                        to_id: this.callPeerId,
                        to_type: this.callPeerType,
                        call_mode: this.callMode,
                        payload: JSON.stringify(event.candidate),
                    });
                };

                this.peerConnection = connection;
                return connection;
            },
            startRingTone() {
                if (this.isRinging) return;
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;

                this.ringAudioContext = new AudioContext();
                const playPulse = () => {
                    if (!this.ringAudioContext) return;
                    const oscillator = this.ringAudioContext.createOscillator();
                    const gain = this.ringAudioContext.createGain();
                    oscillator.type = 'sine';
                    oscillator.frequency.value = 440;
                    gain.gain.setValueAtTime(0, this.ringAudioContext.currentTime);
                    gain.gain.linearRampToValueAtTime(0.08, this.ringAudioContext.currentTime + 0.05);
                    gain.gain.setValueAtTime(0.08, this.ringAudioContext.currentTime + 0.35);
                    gain.gain.linearRampToValueAtTime(0, this.ringAudioContext.currentTime + 0.4);
                    oscillator.connect(gain);
                    gain.connect(this.ringAudioContext.destination);
                    oscillator.start();
                    oscillator.stop(this.ringAudioContext.currentTime + 0.4);
                };

                playPulse();
                this.ringOscillatorInterval = setInterval(() => {
                    if (this.ringAudioContext.state === 'suspended') {
                        this.ringAudioContext.resume().catch(() => {});
                    }
                    playPulse();
                }, 1000);
                this.isRinging = true;
            },
            stopRingTone() {
                if (!this.isRinging) return;
                if (this.ringOscillatorInterval) {
                    clearInterval(this.ringOscillatorInterval);
                    this.ringOscillatorInterval = null;
                }
                if (this.ringAudioContext) {
                    this.ringAudioContext.close().catch(() => {});
                    this.ringAudioContext = null;
                }
                this.isRinging = false;
            },
            async sendRingingSignal(signal) {
                if (this.incomingCallRingingAckSent || !signal) return;
                try {
                    await axios.post('/voice-call/ringing', {
                        conversation_id: signal.conversation_id,
                        to_id: signal.from_id,
                        to_type: signal.from_type,
                        call_mode: signal.call_mode || 'audio',
                    });
                    this.incomingCallRingingAckSent = true;
                } catch (error) {
                    console.error('Failed to send ringing acknowledgement', error);
                }
            },
            async prepareCallUi(mode, peerId, peerType, peerName) {
                this.stopRingTone();
                this.microphoneUnavailable = false;
                this.cameraUnavailable = false;
                this.callMode = mode;
                this.callPeerId = peerId;
                this.callPeerType = peerType;
                this.callPeerName = peerName || '';
                this.showCallModal = true;
                this.callConnected = false;
                this.remoteStreamActive = false;
                await this.ensureMediaStream(mode);
            },
            async startCall(mode) {
                if (!this.activeConversation || this.activeConversation.is_group) return;
                await this.prepareCallUi(mode, this.activeConversation.other_participant_id, this.activeConversation.other_participant_type, this.activeConversation.title);
                if (this.microphoneUnavailable && this.cameraUnavailable) {
                    this.callStatus = 'Calling — microphone and camera unavailable';
                } else if (this.microphoneUnavailable) {
                    this.callStatus = 'Calling — microphone unavailable, listen only';
                } else if (this.cameraUnavailable) {
                    this.callStatus = 'Calling — camera unavailable, audio-only';
                } else {
                    this.callStatus = mode === 'video' ? 'Calling with video...' : 'Calling...';
                }
                this.startRingTone();

                const connection = this.createPeerConnection();
                if (this.localStream) {
                    this.localStream.getTracks().forEach((track) => connection.addTrack(track, this.localStream));
                }

                const offer = await connection.createOffer();
                await connection.setLocalDescription(offer);

                await axios.post('/voice-call/initiate', {
                    conversation_id: this.activeConversationId,
                    to_id: this.callPeerId,
                    to_type: this.callPeerType,
                    call_mode: mode,
                    payload: JSON.stringify(offer),
                });
            },
            async handleSignal(signal) {
                if (!signal) return;

                if (signal.type === 'offer') {
                    const conversation = this.conversations.find((item) => String(item.id) === String(signal.conversation_id));
                    if (conversation && String(this.activeConversationId) !== String(signal.conversation_id)) {
                        await this.selectConversation(conversation);
                    }
                    this.pendingIncomingSignal = signal;
                    this.incomingCall = true;
                    this.callMode = signal.call_mode || 'audio';
                    this.callPeerId = signal.from_id;
                    this.callPeerType = signal.from_type;
                    this.callPeerName = conversation?.title || 'Incoming call';
                    this.callStatus = this.callMode === 'video' ? 'Incoming video call...' : 'Incoming audio call...';
                    this.showCallModal = true;
                    this.startRingTone();
                    await this.sendRingingSignal(signal);
                    return;
                }

                if (signal.type === 'ringing') {
                    if (!this.callConnected) {
                        this.callStatus = 'Ringing...';
                        this.startRingTone();
                    }
                    return;
                }

                if (signal.type === 'answer' && this.peerConnection) {
                    this.stopRingTone();
                    await this.peerConnection.setRemoteDescription(new RTCSessionDescription(JSON.parse(signal.payload)));
                    this.callConnected = true;
                    this.callStatus = 'Connected';
                    return;
                }

                if (signal.type === 'ice_candidate' && this.peerConnection) {
                    await this.peerConnection.addIceCandidate(new RTCIceCandidate(JSON.parse(signal.payload)));
                    return;
                }

                if (signal.type === 'reject') {
                    this.stopRingTone();
                    this.callStatus = 'Call declined';
                    setTimeout(() => this.endCall(false), 1000);
                    return;
                }

                if (signal.type === 'hangup') {
                    this.stopRingTone();
                    this.callStatus = 'Call ended';
                    setTimeout(() => this.endCall(false), 800);
                }
            },
            async acceptIncomingCall() {
                if (!this.pendingIncomingSignal) return;
                const signal = this.pendingIncomingSignal;
                this.pendingIncomingSignal = null;
                this.incomingCall = false;
                this.incomingCallRingingAckSent = false;

                await this.prepareCallUi(signal.call_mode || 'audio', signal.from_id, signal.from_type, this.callPeerName);
                this.callStatus = this.microphoneUnavailable ? 'Connecting — listen only' : 'Connecting...';

                const connection = this.createPeerConnection();
                if (this.localStream) {
                    this.localStream.getTracks().forEach((track) => connection.addTrack(track, this.localStream));
                }
                await connection.setRemoteDescription(new RTCSessionDescription(JSON.parse(signal.payload)));

                const answer = await connection.createAnswer();
                await connection.setLocalDescription(answer);

                await axios.post('/voice-call/answer', {
                    conversation_id: signal.conversation_id,
                    to_id: signal.from_id,
                    to_type: signal.from_type,
                    call_mode: signal.call_mode || 'audio',
                    payload: JSON.stringify(answer),
                });

                this.callConnected = true;
                this.callStatus = 'Connected';
            },
            async rejectIncomingCall() {
                if (!this.pendingIncomingSignal) return;
                const signal = this.pendingIncomingSignal;
                await axios.post('/voice-call/reject', {
                    conversation_id: signal.conversation_id,
                    to_id: signal.from_id,
                    to_type: signal.from_type,
                    call_mode: signal.call_mode || 'audio',
                });
                this.pendingIncomingSignal = null;
                this.incomingCall = false;
                this.incomingCallRingingAckSent = false;
                this.stopRingTone();
                this.showCallModal = false;
                this.callStatus = 'Ready';
            },
            async endCall(notifyPeer = true) {
                if (notifyPeer && this.activeConversationId && this.callPeerId && this.callPeerType) {
                    try {
                        await axios.post('/voice-call/hangup', {
                            conversation_id: this.activeConversationId,
                            to_id: this.callPeerId,
                            to_type: this.callPeerType,
                            call_mode: this.callMode,
                        });
                    } catch (error) {
                        console.error('Hangup failed', error);
                    }
                }

                this.stopRingTone();

                if (this.peerConnection) {
                    this.peerConnection.close();
                    this.peerConnection = null;
                }

                if (this.localStream) {
                    this.localStream.getTracks().forEach((track) => track.stop());
                    this.localStream = null;
                }

                this.remoteStream = null;
                this.remoteStreamActive = false;
                this.pendingIncomingSignal = null;
                this.incomingCall = false;
                this.incomingCallRingingAckSent = false;
                this.callConnected = false;
                this.showCallModal = false;
                this.isMuted = false;
                this.isCameraOff = false;
                this.callStatus = 'Ready';
                if (this.$refs.localVideo) this.$refs.localVideo.srcObject = null;
                if (this.$refs.remoteVideo) this.$refs.remoteVideo.srcObject = null;
            },
            toggleMute() {
                if (!this.localStream) return;
                const shouldEnable = this.isMuted;
                this.localStream.getAudioTracks().forEach((track) => track.enabled = shouldEnable);
                this.isMuted = !this.isMuted;
            },
            toggleCamera() {
                if (!this.localStream) return;
                const shouldEnable = this.isCameraOff;
                this.localStream.getVideoTracks().forEach((track) => track.enabled = shouldEnable);
                this.isCameraOff = !this.isCameraOff;
            },
            startSignalPolling() {
                if (this.signalPollTimer) clearInterval(this.signalPollTimer);
                this.signalPollTimer = setInterval(async () => {
                    try {
                        const response = await axios.get('/voice-call/poll');
                        const signals = response.data?.signals || [];
                        for (const signal of signals) {
                            await this.handleSignal(signal);
                        }
                    } catch (error) {
                        console.error('Signal poll failed', error);
                    }
                }, 2000);
            },
            notificationChannelName() {
                return `user.${config.currentType}.${config.currentId}`;
            },
            notificationPreview(message) {
                const body = String(message?.body || '').trim();
                if (body) {
                    return body.length > 120 ? `${body.slice(0, 120)}...` : body;
                }

                const fileName = String(message?.file_name || '').trim();
                if (fileName) {
                    return `Attachment: ${fileName}`;
                }

                return message?.type === 'file' ? 'Sent an attachment' : 'New message received';
            },
            notificationLabel(message) {
                const conversation = this.conversations.find((item) => String(item.id) === String(message?.conversation_id));
                return conversation?.title ? `Message from ${conversation.title}` : 'New Message';
            },
            connectNotificationListener(retryCount = 0) {
                if (typeof window.Echo === 'undefined') {
                    if (retryCount < 10) {
                        setTimeout(() => this.connectNotificationListener(retryCount + 1), 800);
                    }
                    return;
                }

                window.Echo.private(this.notificationChannelName())
                    .listen('.message.sent', async (message) => {
                        if (!message) return;
                        await this.loadConversations(true);
                        this.pushNotificationToast(message);
                    });
            },
            pushNotificationToast(message) {
                if (window.__dashboardGlobalMessageNotifications) return;
                const senderType = String(message?.sender_type || '').split('\\').pop().toLowerCase();
                const isOwnMessage = String(message?.sender_id) === String(config.currentId) && senderType === config.currentType;
                if (!message?.id || isOwnMessage) return;

                const alreadyVisible = this.notificationToasts.some((item) => String(item.messageId) === String(message.id));
                if (alreadyVisible) return;

                const toast = {
                    id: `${message.id}-${Date.now()}`,
                    messageId: message.id,
                    conversationId: message.conversation_id,
                    sender: message.sender_name || 'Someone',
                    preview: this.notificationPreview(message),
                    label: this.notificationLabel(message),
                };

                this.notificationToasts = [...this.notificationToasts, toast].slice(-4);

                setTimeout(() => {
                    this.notificationToasts = this.notificationToasts.filter((item) => item.id !== toast.id);
                }, 5000);
            },
            syncConversationSnapshots(conversations, force = false) {
                const nextSnapshots = {};

                conversations.forEach((conversation) => {
                    const lastMessage = conversation.last_message || conversation.lastMessage || null;
                    const snapshot = {
                        lastMessageId: String(lastMessage?.id || ''),
                        unreadCount: Number(conversation.unread_count || 0),
                        preview: String(conversation.preview_text || ''),
                    };

                    const previousSnapshot = this.seenConversationSnapshots[String(conversation.id)];

                    if (!force && previousSnapshot && snapshot.lastMessageId && previousSnapshot.lastMessageId !== snapshot.lastMessageId) {
                        const senderType = String(lastMessage?.sender_type || '').split('\\').pop().toLowerCase();
                        const isMine = String(lastMessage?.sender_id) === String(config.currentId) && senderType === config.currentType;

                        if (!isMine) {
                            this.pushNotificationToast({
                                id: lastMessage.id,
                                conversation_id: conversation.id,
                                sender_id: lastMessage.sender_id,
                                sender_type: lastMessage.sender_type,
                                sender_name: lastMessage.sender_name || conversation.title || 'Someone',
                                body: lastMessage.body || conversation.preview_text || '',
                                type: lastMessage.type || 'text',
                                file_name: lastMessage.file_name || null,
                            });
                        }
                    }

                    nextSnapshots[String(conversation.id)] = snapshot;
                });

                this.seenConversationSnapshots = nextSnapshots;
            },
            async openToastConversation(toast) {
                this.notificationToasts = this.notificationToasts.filter((item) => item.id !== toast.id);
                await this.loadConversations(true);
                const conversation = this.conversations.find((item) => String(item.id) === String(toast.conversationId));
                if (conversation) {
                    await this.selectConversation(conversation);
                }
            },
            toggleMessageMenu(messageId) {
                this.openMessageMenuId = this.openMessageMenuId === messageId ? null : messageId;
            },
            closeMessageMenu(messageId = null) {
                if (messageId === null || this.openMessageMenuId === messageId) {
                    this.openMessageMenuId = null;
                }
            },
            isEditingMessage(messageId) {
                return String(this.editingMessageId) === String(messageId);
            },
            startInlineEdit(message) {
                if (!message || !message.body) return;
                this.editingMessageId = message.id;
                this.editingMessageDraft = message.body;
                this.closeMessageMenu(message.id);
            },
            cancelInlineEdit() {
                this.editingMessageId = null;
                this.editingMessageDraft = '';
            },
            openConfirmDialog({ title, description, confirmText = 'Confirm', onConfirm }) {
                this.confirmDialog = {
                    open: true,
                    title,
                    description,
                    confirmText,
                    loading: false,
                    onConfirm
                };
            },
            closeConfirmDialog(force = false) {
                if (this.confirmDialog.loading && !force) return;
                this.confirmDialog = {
                    open: false,
                    title: '',
                    description: '',
                    confirmText: 'Confirm',
                    loading: false,
                    onConfirm: null
                };
            },
            scrollToBottom(delay = 60, force = false) {
                this.$nextTick(() => {
                    setTimeout(() => {
                        const panel = document.getElementById('messages-panel');
                        if (panel) {
                            const threshold = 50; // strictly at the very bottom
                            const isAtBottom = panel.scrollHeight - panel.scrollTop - panel.clientHeight < threshold;

                            if (force || isAtBottom) {
                                panel.scrollTo({
                                    top: panel.scrollHeight,
                                    behavior: 'smooth'
                                });
                            }
                        }
                    }, delay);
                });
            },
            init() {
                this.loadConversations();
                this.loadDirectCandidates();
                this.loadGroupCandidates();
                this.startPolling();
                this.startSignalPolling();
                this.$watch('activeTab', value => localStorage.setItem('messaging_active_tab', value));
                this.sendHeartbeat();
                setInterval(() => this.sendHeartbeat(), 30000);

                // Ensure only one audio plays at a time
                document.addEventListener('play', (event) => {
                    const audios = document.getElementsByTagName('audio');
                    for (let i = 0, len = audios.length; i < len; i++) {
                        if (audios[i] != event.target) {
                            audios[i].pause();
                        }
                    }
                }, true);

                // Real-time listener using Laravel Echo
                this.connectNotificationListener();

                if (window.Echo) {
                    this.$watch('activeConversationId', (newId, oldId) => {
                        if (oldId) {
                            console.log('Leaving conversation channel:', oldId);
                            window.Echo.leave(`conversation.${oldId}`);
                        }
                        if (!newId) return;

                        localStorage.setItem('user_active_conversation_id', newId);

                        console.log('Joining conversation channel:', newId);
                        window.Echo.private(`conversation.${newId}`)
                            .listen('.message.sent', (message) => {
                                if (!message || String(message.conversation_id) !== String(this.activeConversationId)) return;

                                const exists = this.messages.some(m => String(m.id) === String(message.id));
                                if (!exists) {
                                    this.messages = [...this.messages, message];
                                    this.scrollToBottom(150, false); // Only scroll if near bottom
                                    axios.post(config.routes.read, {
                                        conversation_id: this.activeConversationId
                                    });
                                    if (window.dispatchMessageCounterSync) window.dispatchMessageCounterSync('read', {
                                        conversationId: this.activeConversationId
                                    });
                                }
                            });
                    });

                    if (this.activeConversationId) {
                        window.Echo.private(`conversation.${this.activeConversationId}`)
                            .listen('.message.sent', (message) => {
                                if (!message || String(message.conversation_id) !== String(this.activeConversationId)) return;
                                const exists = this.messages.some(m => String(m.id) === String(message.id));
                                if (!exists) {
                                    this.messages = [...this.messages, message];
                                    this.scrollToBottom(150, false); // Only scroll if near bottom
                                    axios.post(config.routes.read, {
                                        conversation_id: this.activeConversationId
                                    });
                                }
                            });
                    }
                }
            },
            async sendHeartbeat() {
                try {
                    await axios.post('/online-heartbeat');
                } catch (e) {
                    console.error('Heartbeat failed', e);
                }
            },
            async loadConversations(force = false) {
                const now = Date.now();
                if (!force && (now - this.lastConversationLoadTime < this.conversationLoadDebounceMs)) return;
                this.lastConversationLoadTime = now;
                const response = await fetch(config.routes.conversations + '?t=' + Date.now(), {
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                this.conversations = data.conversations || [];
                this.syncConversationSnapshots(this.conversations, force);
                this.directConversations = this.conversations.filter((item) => !item.is_group);
                this.groupConversations = this.conversations.filter((item) => item.is_group);
                this.pinnedDirectConversations = this.directConversations.filter((item) => Boolean(item.is_pinned));
                this.otherDirectConversations = this.directConversations.filter((item) => !item.is_pinned);
                this.pinnedGroupConversations = this.groupConversations.filter((item) => Boolean(item.is_pinned));
                this.otherGroupConversations = this.groupConversations.filter((item) => !item.is_pinned);
                if (!this.activeConversationId && this.conversations.length) {
                    await this.selectConversation(this.conversations[0]);
                    return;
                }
                if (this.activeConversationId) {
                    this.activeConversation = this.conversations.find((item) => item.id == this.activeConversationId) || null;
                    if (this.activeConversation && this.messages.length === 0) {
                        await this.loadMessages(false);
                    } else if (force) {
                        this.scrollToBottom(150, true);
                    }
                }
            },
            async loadDirectCandidates() {
                const response = await fetch(`${config.routes.participants}?mode=direct&q=${encodeURIComponent(this.directSearch)}`, {
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                this.directCandidates = (await response.json()).items || [];
            },
            async loadGroupCandidates() {
                const search = this.showManageMembersModal ? this.manageMemberSearch : this.groupMemberSearch;
                const response = await fetch(`${config.routes.participants}?mode=group&q=${encodeURIComponent(search)}`, {
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                this.groupCandidates = (await response.json()).items || [];
            },
            async startDirectChat(item) {
                const response = await axios.get(config.routes.direct, {
                    params: {
                        target_id: item.id,
                        target_type: item.type
                    }
                });
                await this.loadConversations(true);
                const conversation = this.conversations.find((entry) => entry.id == response.data.conversation.id);
                if (conversation) await this.selectConversation(conversation);
                this.showDirectPicker = false;
            },
            async selectConversation(conversation) {
                this.activeConversationId = conversation.id;
                this.isMobileChatOpen = true;
                localStorage.setItem('user_active_conversation_id', conversation.id);
                this.activeConversation = conversation;
                this.messages = []; // Clear messages for the new conversation
                this.openMessageMenuId = null;
                this.cancelInlineEdit();
                this.chatSummary = ''; // Reset summary for the new conversation
                this.showSummary = false; // Hide summary for the new conversation
                this.lastLoadTime = 0; // Force immediate load for the new conversation
                this.groupDetails = {
                    members: []
                };
                await this.loadMessages(false);
                if (conversation.is_group) await this.loadGroupDetails();
            },
            async loadMessages(silent = false) {
                if (!this.activeConversationId) return;
                if (this.loadingMessages) return;
                const now = Date.now();

                // Allow immediate load if messages are empty (i.e. we just switched conversations)
                if (!silent && this.messages.length > 0 && (now - this.lastLoadTime < this.loadDebounceMs)) return;

                if (!silent) {
                    this.loadingMessages = true;
                }

                this.lastLoadTime = now;
                try {
                    const response = await axios.get(`${config.routes.messagesBase}/${this.activeConversationId}?t=${now}`);
                    const newMessages = (response.data.messages || []).filter(m => String(m.conversation_id) === String(this.activeConversationId));
                    const previousCount = this.messages.length;
                    this.messages = newMessages;

                    if (newMessages.length > previousCount) {
                        this.scrollToBottom(100, !silent);
                    }

                    axios.post(config.routes.read, {
                        conversation_id: this.activeConversationId
                    });
                    if (window.dispatchMessageCounterSync) window.dispatchMessageCounterSync('read', {
                        conversationId: this.activeConversationId
                    });
                } finally {
                    if (!silent) {
                        this.loadingMessages = false;
                    }
                }
            },
            async saveInlineEdit(message) {
                if (!message || !this.isMine(message) || this.isUpdatingMessage || !this.isEditingMessage(message.id)) return;

                const trimmedBody = this.editingMessageDraft.trim();
                if (!trimmedBody) {
                    return;
                }

                this.isUpdatingMessage = true;
                try {
                    const response = await axios.patch(this.messageUpdateRoute(message.id), {
                        body: trimmedBody
                    });

                    const updatedMessage = response.data?.message;
                    if (updatedMessage) {
                        this.messages = this.messages.map((item) => String(item.id) === String(updatedMessage.id) ? updatedMessage : item);
                    }

                    this.cancelInlineEdit();
                    await this.loadConversations(true);
                } catch (error) {
                    console.error('Edit message failed', error);
                    alert('Message update failed. Please try again.');
                } finally {
                    this.isUpdatingMessage = false;
                }
            },
            requestDeleteMessage(message) {
                if (!message || !this.isMine(message) || this.isUpdatingMessage) return;
                this.closeMessageMenu(message.id);
                this.openConfirmDialog({
                    title: 'Delete this message?',
                    description: 'This message will be removed from the conversation for everyone in this chat.',
                    confirmText: 'Delete Message',
                    onConfirm: async () => {
                        this.confirmDialog.loading = true;
                        this.isUpdatingMessage = true;
                        try {
                            await axios.delete(this.messageDeleteRoute(message.id));
                            this.messages = this.messages.filter((item) => String(item.id) !== String(message.id));
                            if (this.isEditingMessage(message.id)) {
                                this.cancelInlineEdit();
                            }
                            await this.loadConversations(true);
                            this.confirmDialog.loading = false;
                            this.closeConfirmDialog(true);
                        } catch (error) {
                            console.error('Delete message failed', error);
                            alert('Message delete failed. Please try again.');
                            this.confirmDialog.loading = false;
                        } finally {
                            this.isUpdatingMessage = false;
                        }
                    }
                });
            },
            clearConversation() {
                if (!this.activeConversationId || this.isClearingConversation) return;
                this.openConfirmDialog({
                    title: 'Clear this chat?',
                    description: 'All messages, files, and conversation history in this chat will be permanently deleted.',
                    confirmText: 'Clear Chat',
                    onConfirm: async () => {
                        this.confirmDialog.loading = true;
                        this.isClearingConversation = true;
                        try {
                            await axios.delete(this.clearConversationRoute());
                            this.messages = [];
                            this.cancelInlineEdit();
                            this.chatSummary = '';
                            this.showSummary = false;
                            await this.loadConversations(true);
                            this.confirmDialog.loading = false;
                            this.closeConfirmDialog(true);
                        } catch (error) {
                            console.error('Clear conversation failed', error);
                            alert('Chat clear failed. Please try again.');
                            this.confirmDialog.loading = false;
                        } finally {
                            this.isClearingConversation = false;
                        }
                    }
                });
            },
            async sendMessage() {
                if (!this.activeConversationId) return;
                if (!this.draftMessage.trim() && !this.selectedFile) return;

                const formData = new FormData();
                formData.append('conversation_id', this.activeConversationId);
                if (this.draftMessage.trim()) formData.append('message', this.draftMessage.trim());
                if (this.selectedFile) formData.append('file', this.selectedFile);

                try {
                    const response = await axios.post(config.routes.send, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    });

                    console.log('Message sent successfully!', response.data);

                    // Use spread operator for guaranteed Alpine reactivity
                    this.messages = [...this.messages, response.data];
                    this.draftMessage = '';
                    this.clearFile();

                    // Update polling timer to prevent immediate override
                    this.lastLoadTime = Date.now();

                    await this.loadConversations(true);

                    if (window.dispatchMessageCounterSync) {
                        window.dispatchMessageCounterSync('sent', {
                            conversationId: this.activeConversationId
                        });
                    }

                    this.scrollToBottom(50, true); // Force scroll after sending a message
                    this.$nextTick(() => {
                        this.$refs.messageInput?.focus();
                    });
                } catch (error) {
                    console.error('Send failed', error);
                    alert('Failed to send message. Please try again.');
                }
            },
            pickFile(event) {
                const file = event.target.files[0] || null;
                this.selectedFile = file;
                this.selectedFileName = file ? file.name : '';
                this.isFileTooLarge = file ? (file.size > 10 * 1024 * 1024) : false;

                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => this.selectedFilePreview = e.target.result;
                    reader.readAsDataURL(file);
                } else {
                    this.selectedFilePreview = null;
                }
            },
            clearFile() {
                this.selectedFile = null;
                this.selectedFileName = '';
                this.isFileTooLarge = false;
                this.selectedFilePreview = null;
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            },
            async toggleVoiceRecord() {
                if (this.isRecordingVoice) {
                    if (window._voiceMediaRecorder) {
                        window._voiceMediaRecorder.stop();
                    }
                    this.isRecordingVoice = false;
                    return;
                }
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({
                        audio: true
                    });
                    window._voiceMediaRecorder = new MediaRecorder(stream);
                    window._voiceAudioChunks = [];
                    window._voiceMediaRecorder.ondataavailable = e => {
                        if (e.data.size > 0) window._voiceAudioChunks.push(e.data);
                    };
                    window._voiceMediaRecorder.onstop = () => {
                        stream.getTracks().forEach(track => track.stop());
                        if (window._voiceAudioChunks.length === 0) return;
                        const audioBlob = new Blob(window._voiceAudioChunks, {
                            type: 'audio/webm'
                        });
                        const file = new File([audioBlob], `voice_message_${Date.now()}.webm`, {
                            type: 'audio/webm'
                        });
                        this.selectedFile = file;
                        this.selectedFileName = 'Voice Message (' + new Date().toLocaleTimeString() + ')';
                        this.isFileTooLarge = false;
                        this.selectedFilePreview = null;
                    };
                    window._voiceMediaRecorder.start();
                    this.isRecordingVoice = true;
                } catch (error) {
                    console.error("Error accessing microphone:", error);
                    alert("Please allow microphone access to record voice messages.");
                }
            },
            isMine(message) {
                const senderType = String(message.sender_type || '').split('\\').pop().toLowerCase();
                return String(message.sender_id) === String(config.currentId) && senderType === config.currentType;
            },
            addEmoji(emoji) {
                this.draftMessage += emoji;
                if (this.$refs.messageInput) this.$refs.messageInput.focus();
            },
            formatTime(value) {
                if (!value) return '';
                const date = new Date(value);
                return date.toLocaleDateString([], {
                        day: '2-digit',
                        month: 'short'
                    }) + ', ' +
                    date.toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
            },
            initialFor(value) {
                return value ? String(value).charAt(0).toUpperCase() : '?';
            },
            messagePreview(lastMessage, fallback = 'No message yet') {
                if (!lastMessage) return fallback;

                const body = String(lastMessage.body || '').trim();
                if (body !== '') return body;

                const filePath = lastMessage.file_path || lastMessage.filePath || '';
                if (filePath) return /\.(webm|mp3|wav|ogg|m4a|aac)$/i.test(filePath) ? 'Voice Message' : 'Photo';

                return fallback;
            },
            conversationPreview(conversation, fallback = 'No message yet') {
                if (!conversation) return fallback;

                const directPreview = String(conversation.preview_text || '').trim();
                if (directPreview !== '') return directPreview;

                return this.messagePreview(conversation.last_message || conversation.lastMessage || null, fallback);
            },
            parseSummary(text) {
                if (!text) return '';

                let html = text
                    .replace(/\*\*(.*?)\*\*/g, '<strong class="text-stone-900 font-bold">$1</strong>') // Bold text
                    .replace(/^\s*###\s*(.*$)/gm, '<h5 class="text-[13px] font-bold text-stone-800 mt-3 mb-1">$1</h5>') // H3 headings
                    .replace(/^\s*##\s*(.*$)/gm, '<h4 class="text-[14px] font-bold text-stone-900 mt-4 mb-2 border-b border-stone-100 pb-1">$1</h4>') // H2 headings
                    .replace(/^\s*#\s*(.*$)/gm, '<h3 class="text-[16px] font-extrabold text-stone-900 mt-5 mb-3">$1</h3>') // H1 headings
                    .replace(/^\s*(\d+\.\s.*)$/gm, '<h5 class="text-[13px] font-bold text-stone-800 mt-3 mb-1">$1</h5>') // Numbered headings
                    .replace(/^\s*[-*]\s(.*)$/gm, '<div class="flex items-start gap-2 ml-2 my-0"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-rose-400"></span><span class="text-stone-700">$1</span></div>'); // Bullet points (removed margin)

                // Handle line breaks
                html = html.split('\n').map(line => {
                    const trimmed = line.trim();
                    if (trimmed === '') return ''; // Remove empty lines to reduce space
                    if (line.includes('<div') || line.includes('<h')) return line; // Already formatted
                    return line + '<br>';
                }).join('\n');

                // Remove redundant <br> after block elements and cleanup
                return html.replace(/(<\/div>|<\/h[1-6]>)<br>/g, '$1').replace(/<br>\n<div/g, '\n<div').trim();
            },
            openCreateGroup() {
                this.showCreateGroupModal = true;
                this.groupForm = {
                    name: '',
                    description: '',
                    participants: []
                };
                this.groupMemberSearch = '';
                this.groupNameError = false;
                this.loadGroupCandidates();
            },
            closeCreateGroup() {
                this.showCreateGroupModal = false;
            },
            toggleMember(item) {
                const key = `${item.type}-${item.id}`;
                const exists = this.groupForm.participants.find((entry) => `${entry.type}-${entry.id}` === key);
                this.groupForm.participants = exists ? this.groupForm.participants.filter((entry) => `${entry.type}-${entry.id}` !== key) : [...this.groupForm.participants, item];
            },
            isSelectedMember(item) {
                return this.groupForm.participants.some((entry) => Number(entry.id) === Number(item.id) && entry.type === item.type);
            },
            async createGroup() {
                if (!this.groupForm.name.trim()) {
                    this.groupNameError = true;
                    return;
                }
                const response = await axios.post(config.routes.groupStore, {
                    name: this.groupForm.name,
                    description: this.groupForm.description,
                    participants: this.groupForm.participants.map((item) => ({
                        id: item.id,
                        type: item.type
                    }))
                });
                this.showCreateGroupModal = false;
                await this.loadConversations(true);
                const conversation = this.conversations.find((item) => Number(item.id) === Number(response.data.group_id));
                if (conversation) await this.selectConversation(conversation);
            },
            async loadGroupDetails() {
                if (!this.activeConversation?.is_group) return;
                const response = await axios.get(`${config.routes.groupsBase}/${this.activeConversationId}`);
                this.groupDetails = response.data.group || {
                    members: []
                };
            },
            openManageMembers() {
                this.showManageMembersModal = true;
                this.manageMemberSearch = '';
                this.loadGroupCandidates();
            },
            availableNewMembers() {
                const existing = new Set((this.groupDetails.members || []).map((member) => `${member.type}-${member.id}`));
                return this.groupCandidates.filter((item) => !existing.has(`${item.type}-${item.id}`));
            },
            async addMembers(items) {
                await axios.post(`${config.routes.groupsBase}/${this.activeConversationId}/members`, {
                    participants: items.map((item) => ({
                        id: item.id,
                        type: item.type
                    }))
                });
                await this.loadConversations();
                await this.loadGroupDetails();
            },
            async removeMember(member) {
                await axios.delete(`${config.routes.groupsBase}/${this.activeConversationId}/members/${member.type}/${member.id}`);
                await this.loadConversations();
                await this.loadGroupDetails();
            },
            closeConversationUI() {
                this.activeConversationId = null;
                this.activeConversation = null;
                this.messages = [];
                this.groupDetails = {
                    members: []
                };
            },
            closeWorkspace() {
                window.location.href = this.exitRoute;
            },
            async leaveGroup() {
                if (!this.activeConversation?.is_group) return;
                await axios.post(`${config.routes.groupsBase}/${this.activeConversationId}/leave`);
                this.closeConversationUI();
                await this.loadConversations();
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
                    const response = await axios.get(`${config.routes.summaryBase}/${this.activeConversationId}/summary`);
                    this.chatSummary = response.data.summary;
                    this.showSummary = true;

                    // Scroll to bottom to show the summary panel
                    this.scrollToBottom(150, true);
                } catch (error) {
                    console.error('Fetch summary failed', error);
                    alert('Failed to generate summary. Please check your connection.');
                } finally {
                    this.isFetchingSummary = false;
                }
            },
            startPolling() {
                if (this.pollTimer) clearInterval(this.pollTimer);
                this.pollTimer = setInterval(async () => {
                    const now = Date.now();
                    if (now - this.lastLoadTime < this.loadDebounceMs) return;

                    this.lastLoadTime = now;
                    await this.loadConversations();
                    if (this.activeTab === 'contacts') {
                        await this.loadDirectCandidates();
                    }
                    if (this.activeConversationId) {
                        await this.loadMessages(true); // Call silently in the background
                        if (this.activeConversation?.is_group) await this.loadGroupDetails();
                    }
                }, 5000);
            }
        };
    }
</script>
