<div x-data="messagingDashboard({
    currentId: {{ (int) $currentParticipantId }},
    currentType: @js($currentParticipantTypeShort),
    initialConversationId: {{ (int) $initialConversationId }},
    routes: {
        conversations: @js(route('messages.conversations')),
        messagesBase: @js(url('/messages')),
        send: @js(route('messages.send')),
        read: @js(route('messages.markRead')),
        direct: @js(route('messages.direct')),
        participants: @js(route('messages.participants')),
        groupStore: @js(route('messages.groups.store')),
        groupsBase: @js(url('/messages/groups')),
        exitRoute: @js(Auth::guard('admin')->check() ? route('admin.dashboard') : route('dashboard'))
    }
})" x-init="init()" class="-m-6 mt-6 mx-auto max-w-7xl overflow-hidden">
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
                        <p class="text-[10.5px] font-bold uppercase tracking-[0.2em] text-stone-400 mb-4 px-1">Recent Conversations</p>
                        
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
                            <template x-for="conversation in directConversations" :key="conversation.id">
                                <button type="button" @click="selectConversation(conversation)" 
                                    class="group block w-full rounded-[20px] px-4 py-3.5 text-left transition-all duration-300" 
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
                                        </div>
                                        <div class="mt-0.5 truncate text-[12px] font-medium transition-colors" :class="activeConversationId === conversation.id ? 'text-rose-500' : 'text-stone-400'" x-text="conversation.last_message?.body || (conversation.last_message?.file_path ? 'Photo' : 'No message yet')"></div>
                                    </div>
                                </button>
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
                            <template x-for="conversation in groupConversations" :key="conversation.id">
                                <button type="button" @click="selectConversation(conversation)" 
                                    class="group block w-full rounded-[20px] px-4 py-3.5 text-left transition-all duration-300" 
                                    :class="activeConversationId === conversation.id ? 'bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] ring-1 ring-stone-200/60 scale-[1.01] z-10 relative' : 'bg-transparent text-stone-800 hover:bg-white hover:shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)]'">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="truncate text-[14.5px] font-bold tracking-tight text-stone-800" x-text="conversation.title"></div>
                                            <template x-if="Number(conversation.unread_count || 0) > 0">
                                                <span class="inline-flex h-5 min-w-[1.25rem] shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-rose-500 to-orange-400 px-1.5 text-[10px] font-black text-white shadow-sm transition-transform group-hover:scale-110" x-text="conversation.unread_count"></span>
                                            </template>
                                        </div>
                                        <div class="mt-0.5 truncate text-[12px] font-medium transition-colors" :class="activeConversationId === conversation.id ? 'text-rose-500' : 'text-stone-400'" x-text="conversation.last_message?.body || (conversation.last_message?.file_path ? 'Photo' : 'Start a discussion')"></div>
                                    </div>
                                </button>
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
                                        <div class="mt-1 truncate text-[10px] uppercase font-bold tracking-widest text-stone-400" x-text="item.subtitle"></div>
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
                                    <template x-if="activeConversation.is_group && groupDetails.description">
                                        <p class="text-[13px] font-medium leading-6 text-stone-500 bg-stone-50 p-2.5 rounded-[14px]" x-text="groupDetails.description"></p>
                                    </template>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 justify-end w-full sm:w-auto">
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
                            <template x-if="!loadingMessages && !messages.length"><div class="rounded-[28px] border border-dashed border-slate-300 bg-white/80 px-6 py-12 text-center text-slate-500"><p class="text-base font-semibold text-slate-700">Conversation is empty</p><p class="mt-2 text-sm">Send the first message and start the discussion.</p></div></template>
                            <div id="messages-list" class="space-y-4 opacity-0 transition-opacity duration-300" x-show="!loadingMessages">
                                <template x-for="message in messages" :key="message.id">
                                    <div class="flex" :class="isMine(message) ? 'justify-end' : 'justify-start'">
                                        <div class="max-w-[80%]">
                                            <template x-if="!isMine(message)">
                                                <p class="mb-1 px-3 text-[11px] uppercase tracking-wider font-bold text-rose-400" x-text="message.sender_name"></p>
                                            </template>
                                            <div class="rounded-[24px] px-5 py-3.5 shadow-sm transform transition-all duration-300 hover:-translate-y-0.5" 
                                                 :class="isMine(message) 
                                                    ? 'bg-gradient-to-br from-rose-500 to-orange-400 text-white shadow-[0_4px_14px_0_rgba(251,113,133,0.39)] rounded-br-md border border-rose-400/20' 
                                                    : 'bg-white text-stone-800 shadow-[0_4px_20px_-4px_rgba(251,146,60,0.08)] rounded-bl-md border border-orange-50'">
                                                <template x-if="message.body">
                                                    <p class="whitespace-pre-wrap text-[14.5px] leading-relaxed font-medium" x-text="message.body"></p>
                                                </template>
                                                <template x-if="message.file_url">
                                                    <div class="mt-2 text-left">
                                                        <template x-if="message.file_url && (message.file_url.match(/\.(jpg|jpeg|png|gif|webp)$/i))">
                                                            <div class="group relative mt-2 inline-block overflow-hidden rounded-[20px] shadow-sm transition-all duration-300 hover:shadow-md" :class="isMine(message) ? 'border border-white/20' : 'border border-rose-100'">
                                                                <img :src="message.file_url" @load="const cb = $el.closest('.flex-1'); if(cb) cb.scrollTop = cb.scrollHeight;" class="max-h-52 max-w-[280px] w-full object-cover cursor-zoom-in transition-transform duration-500 group-hover:scale-105" @click="window.open(message.file_url, '_blank')" alt="Attachment">
                                                                <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                                                            </div>
                                                        </template>
                                                        <template x-if="!(message.file_url && (message.file_url.match(/\.(jpg|jpeg|png|gif|webp)$/i)))">
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
                                            <p class="mt-1.5 px-2 text-[11px] font-medium text-rose-300" :class="isMine(message) ? 'text-right' : 'text-left'" x-text="formatTime(message.created_at)"></p>
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

    <div x-cloak x-show="showCreateGroupModal" class="fixed inset-0 z-50 bg-slate-950/40 p-4 backdrop-blur-sm">
        <div class="mx-auto mt-10 max-w-2xl rounded-[28px] bg-white p-6 shadow-2xl">
            <div class="flex items-center justify-between gap-4"><div><h3 class="text-xl font-semibold text-slate-900">Create Group</h3><p class="mt-1 text-sm text-slate-500">Admins and users can join the same group.</p></div><button type="button" @click="closeCreateGroup()" class="text-2xl text-slate-400 hover:text-slate-700">&times;</button></div>
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
            <div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px]"><div class="max-h-72 space-y-2 overflow-y-auto rounded-3xl border border-slate-200 bg-slate-50 p-3"><template x-for="item in groupCandidates" :key="item.type + '-' + item.id"><button type="button" @click="toggleMember(item)" class="flex w-full items-center justify-between rounded-2xl bg-white px-4 py-3 text-left transition hover:bg-slate-100"><div><div class="text-sm font-semibold text-slate-900" x-text="item.name"></div><div class="text-xs text-slate-500" x-text="item.subtitle + '  ' + item.email"></div></div><span class="rounded-full px-3 py-1 text-xs font-medium" :class="isSelectedMember(item) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-500'" x-text="isSelectedMember(item) ? 'Selected' : 'Add'"></span></button></template></div><div class="rounded-3xl border border-slate-200 bg-white p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Selected</p><div class="mt-3 space-y-2"><template x-if="!groupForm.participants.length"><p class="text-sm text-slate-500">Choose at least one member besides you.</p></template><template x-for="item in groupForm.participants" :key="item.type + '-' + item.id"><div class="flex items-center justify-between rounded-2xl bg-slate-50 px-3 py-2"><div><div class="text-sm font-medium text-slate-800" x-text="item.name"></div><div class="text-xs text-slate-500" x-text="item.subtitle"></div></div><button type="button" @click="toggleMember(item)" class="text-sm text-rose-500">Remove</button></div></template></div></div></div>
            <div class="mt-6 flex justify-end gap-3"><button type="button" @click="closeCreateGroup()" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600">Cancel</button><button type="button" @click="createGroup()" class="rounded-2xl bg-slate-900 px-5 py-2 text-sm font-semibold text-white">Create group</button></div>
        </div>
    </div>

    <div x-cloak x-show="showManageMembersModal && activeConversation?.is_group" class="fixed inset-0 z-50 bg-slate-950/40 p-4 backdrop-blur-sm">
        <div class="mx-auto mt-10 max-w-2xl rounded-[28px] bg-white p-6 shadow-2xl">
            <div class="flex items-center justify-between gap-4"><div><h3 class="text-xl font-semibold text-slate-900">Manage Members</h3><p class="mt-1 text-sm text-slate-500" x-text="activeConversation?.title"></p></div><button type="button" @click="showManageMembersModal = false" class="text-2xl text-slate-400 hover:text-slate-700">&times;</button></div>
            <div class="mt-5"><input x-model="manageMemberSearch" @input.debounce.250ms="loadGroupCandidates()" type="text" placeholder="Search users or admins to add" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-slate-400 focus:ring-4 focus:ring-slate-200"></div>
            <div class="mt-4 grid gap-4 lg:grid-cols-2"><div class="max-h-80 space-y-2 overflow-y-auto rounded-3xl border border-slate-200 bg-slate-50 p-3"><template x-for="member in groupDetails.members || []" :key="member.type + '-' + member.id"><div class="flex items-center justify-between rounded-2xl bg-white px-4 py-3"><div><div class="text-sm font-semibold text-slate-900" x-text="member.name"></div><div class="text-xs text-slate-500" x-text="member.type + '  ' + member.role"></div></div><button type="button" @click="removeMember(member)" class="text-sm text-rose-500">Remove</button></div></template></div><div class="max-h-80 space-y-2 overflow-y-auto rounded-3xl border border-slate-200 bg-white p-3"><template x-for="item in availableNewMembers()" :key="item.type + '-' + item.id"><button type="button" @click="addMembers([item])" class="flex w-full items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 text-left transition hover:bg-slate-100"><div><div class="text-sm font-semibold text-slate-900" x-text="item.name"></div><div class="text-xs text-slate-500" x-text="item.subtitle + '  ' + item.email"></div></div><span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-500">Add</span></button></template></div></div>
        </div>
    </div>
</div>

<script>
function messagingDashboard(config) {
    return {
        conversations: [], directConversations: [], groupConversations: [], directCandidates: [], groupCandidates: [], messages: [],
        activeConversationId: Number(localStorage.getItem('user_active_conversation_id')) || config.initialConversationId || null, activeConversation: null, groupDetails: { members: [] }, loadingMessages: false, isMobileChatOpen: false,
        activeTab: localStorage.getItem('messaging_active_tab') || 'direct', isWorkspaceVisible: true, exitRoute: config.routes.exitRoute || '/', showDirectPicker: (localStorage.getItem('messaging_active_tab') === 'contacts'), directSearch: '', draftMessage: '', selectedFile: null, selectedFileName: '', isFileTooLarge: false, selectedFilePreview: null,
        showCreateGroupModal: false, showManageMembersModal: false, groupMemberSearch: '', manageMemberSearch: '', groupNameError: false,
        groupForm: { name: '', description: '', participants: [] }, pollTimer: null, lastLoadTime: 0, loadDebounceMs: 2500, lastConversationLoadTime: 0, conversationLoadDebounceMs: 3000,
        scrollToBottom() {
            this.$nextTick(() => {
                setTimeout(() => {
                    const panel = document.getElementById('messages-panel');
                    if (panel) { panel.scrollTop = panel.scrollHeight; }
                    const list = document.getElementById('messages-list');
                    if (list) list.classList.remove('opacity-0');
                    if (list) list.style.opacity = '1';
                }, 60);
            });
        },
        init() { 
            this.loadConversations(); 
            this.loadDirectCandidates(); 
            this.loadGroupCandidates(); 
            this.startPolling();
            this.$watch('activeTab', value => localStorage.setItem('messaging_active_tab', value));
            this.$watch('messages', () => this.scrollToBottom());
            this.sendHeartbeat();
            setInterval(() => this.sendHeartbeat(), 30000);
        },
        async sendHeartbeat() {
            try { await axios.post('/online-heartbeat'); } catch (e) { console.error('Heartbeat failed', e); }
        },
        async loadConversations(force = false) {
            const now = Date.now();
            if (!force && (now - this.lastConversationLoadTime < this.conversationLoadDebounceMs)) return;
            this.lastConversationLoadTime = now;
            const response = await fetch(config.routes.conversations + '?t=' + Date.now(), { credentials: 'include', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await response.json();
            this.conversations = data.conversations || [];
            this.directConversations = this.conversations.filter((item) => !item.is_group);
            this.groupConversations = this.conversations.filter((item) => item.is_group);
            if (!this.activeConversationId && this.conversations.length) { await this.selectConversation(this.conversations[0]); return; }
            if (this.activeConversationId) {
                this.activeConversation = this.conversations.find((item) => Number(item.id) === Number(this.activeConversationId)) || null;
                if (this.activeConversation && this.messages.length === 0) {
                    await this.loadMessages();
                } else {
                    this.scrollToBottom(150);
                }
            }
        },
        async loadDirectCandidates() {
            const response = await fetch(`${config.routes.participants}?mode=direct&q=${encodeURIComponent(this.directSearch)}`, { credentials: 'include', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            this.directCandidates = (await response.json()).items || [];
        },
        async loadGroupCandidates() {
            const search = this.showManageMembersModal ? this.manageMemberSearch : this.groupMemberSearch;
            const response = await fetch(`${config.routes.participants}?mode=group&q=${encodeURIComponent(search)}`, { credentials: 'include', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            this.groupCandidates = (await response.json()).items || [];
        },
        async startDirectChat(item) {
            const response = await axios.get(config.routes.direct, { params: { target_id: item.id, target_type: item.type } });
            await this.loadConversations(true);
            const conversation = this.conversations.find((entry) => Number(entry.id) === Number(response.data.conversation.id));
            if (conversation) await this.selectConversation(conversation);
            this.showDirectPicker = false;
        },
        async selectConversation(conversation) {
            this.activeConversationId = conversation.id;
            this.isMobileChatOpen = true;
            localStorage.setItem('user_active_conversation_id', conversation.id);
            this.activeConversation = conversation;
            this.messages = []; // Clear messages for the new conversation
            const list = document.getElementById('messages-list');
            if (list) list.style.opacity = '0';
            this.lastLoadTime = 0; // Force immediate load for the new conversation
            this.groupDetails = { members: [] };
            await this.loadMessages();
            if (conversation.is_group) await this.loadGroupDetails();
        },
        async loadMessages() {
            if (!this.activeConversationId) return;
            if (this.loadingMessages) return;
            const now = Date.now();
            // Allow immediate load if messages are empty (i.e. we just switched conversations)
            if (this.messages.length > 0 && (now - this.lastLoadTime < this.loadDebounceMs)) return;
            this.lastLoadTime = now;
            this.loadingMessages = true;
            try {
                const response = await axios.get(`${config.routes.messagesBase}/${this.activeConversationId}?t=${now}`);
                const newMessages = (response.data.messages || []).filter(m => Number(m.conversation_id) === Number(this.activeConversationId));
                const existingIds = new Set(this.messages.map(m => m.id));
                const uniqueNewMessages = newMessages.filter(m => !existingIds.has(m.id));
                let shouldScroll = uniqueNewMessages.length > 0;
                if (uniqueNewMessages.length > 0) {
                    this.messages = [...this.messages, ...uniqueNewMessages];
                }
                await axios.post(config.routes.read, { conversation_id: this.activeConversationId });
                if (window.dispatchMessageCounterSync) window.dispatchMessageCounterSync('read', { conversationId: this.activeConversationId });
                if (shouldScroll) {
                    this.scrollToBottom(100);
                }
            } finally { this.loadingMessages = false; }
        },
        async sendMessage() {
            if (!this.activeConversationId) return;
            if (!this.draftMessage.trim() && !this.selectedFile) return;
            const formData = new FormData();
            formData.append('conversation_id', this.activeConversationId);
            if (this.draftMessage.trim()) formData.append('message', this.draftMessage.trim());
            if (this.selectedFile) formData.append('file', this.selectedFile);
            const response = await axios.post(config.routes.send, formData, { headers: { 'Content-Type': 'multipart/form-data' } });
            this.messages.push(response.data);
            this.draftMessage = '';
            this.clearFile();
            await this.loadConversations(true);
            if (window.dispatchMessageCounterSync) window.dispatchMessageCounterSync('sent', { conversationId: this.activeConversationId });
            this.scrollToBottom(50);
            this.$nextTick(() => { this.$refs.messageInput?.focus(); });
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
        isMine(message) { const senderType = String(message.sender_type || '').split('\\').pop().toLowerCase(); return Number(message.sender_id) === Number(config.currentId) && senderType === config.currentType; },
        addEmoji(emoji) { this.draftMessage += emoji; if (this.$refs.messageInput) this.$refs.messageInput.focus(); },
        formatTime(value) { return value ? new Date(value).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : ''; },
        initialFor(value) { return value ? String(value).charAt(0).toUpperCase() : '?'; },
        openCreateGroup() { this.showCreateGroupModal = true; this.groupForm = { name: '', description: '', participants: [] }; this.groupMemberSearch = ''; this.groupNameError = false; this.loadGroupCandidates(); },
        closeCreateGroup() { this.showCreateGroupModal = false; },
        toggleMember(item) {
            const key = `${item.type}-${item.id}`;
            const exists = this.groupForm.participants.find((entry) => `${entry.type}-${entry.id}` === key);
            this.groupForm.participants = exists ? this.groupForm.participants.filter((entry) => `${entry.type}-${entry.id}` !== key) : [...this.groupForm.participants, item];
        },
        isSelectedMember(item) { return this.groupForm.participants.some((entry) => Number(entry.id) === Number(item.id) && entry.type === item.type); },
        async createGroup() {
            if (!this.groupForm.name.trim()) {
                this.groupNameError = true;
                return;
            }
            const response = await axios.post(config.routes.groupStore, { name: this.groupForm.name, description: this.groupForm.description, participants: this.groupForm.participants.map((item) => ({ id: item.id, type: item.type })) });
            this.showCreateGroupModal = false;
            await this.loadConversations(true);
            const conversation = this.conversations.find((item) => Number(item.id) === Number(response.data.group_id));
            if (conversation) await this.selectConversation(conversation);
        },
        async loadGroupDetails() {
            if (!this.activeConversation?.is_group) return;
            const response = await axios.get(`${config.routes.groupsBase}/${this.activeConversationId}`);
            this.groupDetails = response.data.group || { members: [] };
        },
        openManageMembers() { this.showManageMembersModal = true; this.manageMemberSearch = ''; this.loadGroupCandidates(); },
        availableNewMembers() {
            const existing = new Set((this.groupDetails.members || []).map((member) => `${member.type}-${member.id}`));
            return this.groupCandidates.filter((item) => !existing.has(`${item.type}-${item.id}`));
        },
        async addMembers(items) { await axios.post(`${config.routes.groupsBase}/${this.activeConversationId}/members`, { participants: items.map((item) => ({ id: item.id, type: item.type })) }); await this.loadConversations(); await this.loadGroupDetails(); },
        async removeMember(member) { await axios.delete(`${config.routes.groupsBase}/${this.activeConversationId}/members/${member.type}/${member.id}`); await this.loadConversations(); await this.loadGroupDetails(); },
        closeConversationUI() { this.activeConversationId = null; this.activeConversation = null; this.messages = []; this.groupDetails = { members: [] }; },
        closeWorkspace() { window.location.href = this.exitRoute; },
        async leaveGroup() { if (!this.activeConversation?.is_group) return; await axios.post(`${config.routes.groupsBase}/${this.activeConversationId}/leave`); this.closeConversationUI(); await this.loadConversations(); },
        startPolling() {
            if (this.pollTimer) clearInterval(this.pollTimer);
            this.pollTimer = setInterval(async () => { 
                if (this.loadingMessages) return;
                const now = Date.now();
                if (now - this.lastLoadTime < this.loadDebounceMs) return;
                this.lastLoadTime = now;
                await this.loadConversations(); 
                if (this.activeTab === 'contacts') {
                    await this.loadDirectCandidates();
                }
                if (this.activeConversationId) { 
                    await this.loadMessages(); 
                    if (this.activeConversation?.is_group) await this.loadGroupDetails(); 
                } 
            }, 5000);
        }
    };
}
</script>
