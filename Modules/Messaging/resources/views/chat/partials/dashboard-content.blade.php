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
})" x-init="init()" class="mx-auto max-w-7xl px-4 pb-8 sm:px-6 lg:px-8">
    <div x-show="isWorkspaceVisible" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-[0_24px_80px_-28px_rgba(15,23,42,0.35)]">
        <div class="grid min-h-[78vh] lg:grid-cols-[320px_minmax(0,1fr)]">
            <aside class="border-b border-slate-200 bg-slate-50 lg:border-b-0 lg:border-r">
                <div class="border-b border-slate-200 bg-white px-5 py-5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Workspace</p>
                            <h3 class="mt-1 text-lg font-semibold text-slate-900">Chats & Groups</h3>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex items-center gap-1 rounded-[20px] bg-slate-100 p-1.5">
                        <button type="button" 
                            @click="activeTab = 'direct'" 
                            :class="activeTab === 'direct' ? 'bg-white text-slate-900 shadow-[0_2px_8px_-2px_rgba(15,23,42,0.12)]' : 'text-slate-500 hover:text-slate-900 hover:bg-white/50'"
                            class="flex flex-1 items-center justify-center gap-2 rounded-[14px] py-2.5 text-xs font-bold transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Direct
                        </button>
                        <button type="button" 
                            @click="activeTab = 'groups'" 
                            :class="activeTab === 'groups' ? 'bg-white text-slate-900 shadow-[0_2px_8px_-2px_rgba(15,23,42,0.12)]' : 'text-slate-500 hover:text-slate-900 hover:bg-white/50'"
                            class="flex flex-1 items-center justify-center gap-2 rounded-[14px] py-2.5 text-xs font-bold transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Groups
                        </button>
                    </div>
                </div>

                <div class="px-5 py-5">
                    <div x-show="activeTab === 'direct'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Direct</p>
                            <button type="button" @click="showDirectPicker = !showDirectPicker" class="text-xs font-medium text-slate-500 hover:text-slate-900">Start chat</button>
                        </div>
                        <div x-show="showDirectPicker" x-cloak class="mt-3 rounded-2xl border border-slate-200 bg-white p-3">
                            <input x-model="directSearch" @input.debounce.250ms="loadDirectCandidates()" type="text" placeholder="Search people" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-slate-400 focus:ring-4 focus:ring-slate-200">
                            <div class="mt-3 max-h-48 space-y-2 overflow-y-auto">
                                <template x-for="item in directCandidates" :key="item.type + '-' + item.id">
                                    <button type="button" @click="startDirectChat(item)" class="flex w-full items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 text-left transition hover:bg-slate-100">
                                        <div>
                                            <div class="text-sm font-semibold text-slate-900" x-text="item.name"></div>
                                            <div class="text-xs text-slate-500" x-text="item.subtitle + ' � ' + item.email"></div>
                                        </div>
                                        <span class="rounded-full bg-white px-2 py-1 text-xs font-medium text-slate-500">Open</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                        <div class="mt-3 max-h-[24vh] space-y-2 overflow-y-auto pr-1">
                            <template x-if="!directConversations.length"><div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-6 text-center text-sm text-slate-500">No direct conversation yet.</div></template>
                            <template x-for="conversation in directConversations" :key="conversation.id">
                                <button type="button" @click="selectConversation(conversation)" class="block w-full rounded-2xl border px-4 py-3 text-left transition" :class="activeConversationId === conversation.id ? 'border-slate-900 bg-slate-900 text-white' : 'border-transparent bg-white text-slate-800 hover:border-slate-200 hover:bg-slate-100'">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0"><div class="truncate text-sm font-semibold" x-text="conversation.title"></div><div class="truncate text-xs" :class="activeConversationId === conversation.id ? 'text-slate-300' : 'text-slate-500'" x-text="conversation.last_message?.body || 'No message yet'"></div></div>
                                        <template x-if="Number(conversation.unread_count || 0) > 0"><span class="inline-flex h-6 min-w-[1.5rem] items-center justify-center rounded-full bg-rose-500 px-2 text-xs font-bold text-white" x-text="conversation.unread_count"></span></template>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                    
                    <div x-show="activeTab === 'groups'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0">
                        <div class="mb-5 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Your Groups</p>
                                <p class="mt-0.5 text-[10px] text-slate-500" x-text="groupConversations.length + ' active groups'"></p>
                            </div>
                            <button type="button" @click="openCreateGroup()" class="inline-flex h-9 items-center rounded-xl bg-slate-900 px-3 text-xs font-bold text-white transition hover:bg-slate-800">
                                <span>+ New</span>
                            </button>
                        </div>
                        
                        <div class="max-h-[40vh] space-y-2 overflow-y-auto pr-1">
                            <template x-if="!groupConversations.length">
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-8 text-center text-sm text-slate-500">
                                    Create your first group to start team messaging.
                                </div>
                            </template>
                            <template x-for="conversation in groupConversations" :key="conversation.id">
                                <button type="button" @click="selectConversation(conversation)" 
                                    class="block w-full rounded-2xl border px-4 py-3 text-left transition" 
                                    :class="activeConversationId === conversation.id ? 'border-amber-300 bg-amber-50 text-slate-900' : 'border-transparent bg-white text-slate-800 hover:border-slate-200 hover:bg-slate-100'">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-semibold" x-text="conversation.title"></div>
                                            <div class="truncate text-xs text-slate-500" x-text="(conversation.members_count || 0) + ' members • ' + (conversation.last_message?.body || 'No message yet')"></div>
                                        </div>
                                        <template x-if="Number(conversation.unread_count || 0) > 0">
                                            <span class="inline-flex h-6 min-w-[1.5rem] items-center justify-center rounded-full bg-slate-900 px-2 text-xs font-bold text-white" x-text="conversation.unread_count"></span>
                                        </template>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </aside>
            <section class="flex min-h-[78vh] flex-col bg-[radial-gradient(circle_at_top,#f8fafc_0%,#ffffff_42%,#f8fafc_100%)]">
                <template x-if="activeConversation">
                    <div class="flex h-full flex-col">
                        <div class="border-b border-slate-200 bg-white px-6 py-5">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-3"><div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-300 text-slate-900"><span class="text-base font-semibold" x-text="initialFor(activeConversation.title)"></span></div><div><h3 class="text-lg font-semibold text-slate-900" x-text="activeConversation.title"></h3><p class="text-sm text-slate-500" x-text="activeConversation.is_group ? ((groupDetails.members?.length || activeConversation.members_count || 0) + ' members in this group') : 'Direct conversation'"></p></div></div>
                                    <template x-if="activeConversation.is_group && groupDetails.description"><p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500" x-text="groupDetails.description"></p></template>
                                </div>
<div class="flex flex-wrap items-center gap-2">
                                    <button type="button" @click="closeWorkspace()" class="rounded-2xl border border-slate-200 bg-white p-2 text-slate-600 transition hover:bg-slate-50 hover:text-slate-900" title="Close Workspace">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                    <template x-if="activeConversation.is_group && groupDetails.can_manage"><button type="button" @click="openManageMembers()" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">Manage Members</button></template>
                                    <template x-if="activeConversation.is_group"><button type="button" @click="leaveGroup()" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-100">Leave Group</button></template>
                                </div>
                            </div>
                        </div>
                        <div id="messages-panel" class="h-[calc(78vh-220px)] min-h-[300px] overflow-y-auto px-6 py-6 scroll-smooth">
                            <template x-if="loadingMessages"><div class="space-y-3"><div class="h-14 w-1/2 animate-pulse rounded-3xl bg-white"></div><div class="ml-auto h-14 w-2/5 animate-pulse rounded-3xl bg-slate-200"></div></div></template>
                            <template x-if="!loadingMessages && !messages.length"><div class="rounded-[28px] border border-dashed border-slate-300 bg-white/80 px-6 py-12 text-center text-slate-500"><p class="text-base font-semibold text-slate-700">Conversation is empty</p><p class="mt-2 text-sm">Send the first message and start the discussion.</p></div></template>
                            <div class="space-y-4">
                                <template x-for="message in messages" :key="message.id">
                                    <div class="flex" :class="isMine(message) ? 'justify-end' : 'justify-start'"><div class="max-w-[80%]"><template x-if="!isMine(message) && activeConversation.is_group"><p class="mb-1 px-3 text-xs font-semibold text-slate-500" x-text="message.sender_name"></p></template><div class="rounded-[22px] px-4 py-3 shadow-sm" :class="isMine(message) ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-800'"><template x-if="message.body"><p class="whitespace-pre-wrap text-sm leading-6" x-text="message.body"></p></template><template x-if="message.file_url"><a :href="message.file_url" target="_blank" class="mt-3 inline-flex rounded-2xl px-3 py-2 text-xs font-medium" :class="isMine(message) ? 'bg-white/10 text-white' : 'bg-slate-100 text-slate-700'">Open attachment</a></template></div><p class="mt-1 px-2 text-xs text-slate-400" x-text="formatTime(message.created_at)"></p></div></div>
                                </template>
                            </div>
                        </div>
                        <div class="border-t border-slate-200 bg-white px-6 py-5">
                            <div x-show="selectedFileName" x-cloak class="mb-3 flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"><span class="text-sm text-slate-600" x-text="selectedFileName"></span><button type="button" @click="clearFile()" class="text-slate-400 hover:text-slate-700">&times;</button></div>
                            <div class="flex items-end gap-3"><input type="file" x-ref="fileInput" class="hidden" @change="pickFile"><button type="button" @click="$refs.fileInput.click()" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-slate-100">+</button><textarea x-model="draftMessage" rows="1" class="min-h-[3rem] flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-slate-400 focus:ring-4 focus:ring-slate-200" placeholder="Write a message..."></textarea><button type="button" @click="sendMessage()" class="inline-flex h-12 shrink-0 items-center rounded-2xl bg-slate-900 px-5 text-sm font-semibold text-white transition hover:bg-slate-800">Send</button></div>
                        </div>
                    </div>
                </template>
                <template x-if="!activeConversation"><div class="flex flex-1 items-center justify-center px-8 py-12"><div class="max-w-lg text-center"><div class="mx-auto flex h-24 w-24 items-center justify-center rounded-[30px] bg-slate-100 text-4xl text-slate-500">#</div><h3 class="mt-6 text-2xl font-semibold text-slate-900">Pick a conversation</h3><p class="mt-3 text-sm leading-7 text-slate-500">Open a direct message, create a group, add admins and users together, and manage everything from this page.</p></div></div></template>
            </section>
        </div>
    </div>

    <div x-cloak x-show="showCreateGroupModal" class="fixed inset-0 z-50 bg-slate-950/40 p-4 backdrop-blur-sm">
        <div class="mx-auto mt-10 max-w-2xl rounded-[28px] bg-white p-6 shadow-2xl">
            <div class="flex items-center justify-between gap-4"><div><h3 class="text-xl font-semibold text-slate-900">Create Group</h3><p class="mt-1 text-sm text-slate-500">Admins and users can join the same group.</p></div><button type="button" @click="closeCreateGroup()" class="text-2xl text-slate-400 hover:text-slate-700">&times;</button></div>
            <div class="mt-6 grid gap-4"><input x-model="groupForm.name" type="text" placeholder="Group name" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-slate-400 focus:ring-4 focus:ring-slate-200"><textarea x-model="groupForm.description" rows="3" placeholder="Short group description" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-slate-400 focus:ring-4 focus:ring-slate-200"></textarea><input x-model="groupMemberSearch" @input.debounce.250ms="loadGroupCandidates()" type="text" placeholder="Search users or admins" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-slate-400 focus:ring-4 focus:ring-slate-200"></div>
            <div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px]"><div class="max-h-72 space-y-2 overflow-y-auto rounded-3xl border border-slate-200 bg-slate-50 p-3"><template x-for="item in groupCandidates" :key="item.type + '-' + item.id"><button type="button" @click="toggleMember(item)" class="flex w-full items-center justify-between rounded-2xl bg-white px-4 py-3 text-left transition hover:bg-slate-100"><div><div class="text-sm font-semibold text-slate-900" x-text="item.name"></div><div class="text-xs text-slate-500" x-text="item.subtitle + ' � ' + item.email"></div></div><span class="rounded-full px-3 py-1 text-xs font-medium" :class="isSelectedMember(item) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-500'" x-text="isSelectedMember(item) ? 'Selected' : 'Add'"></span></button></template></div><div class="rounded-3xl border border-slate-200 bg-white p-4"><p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Selected</p><div class="mt-3 space-y-2"><template x-if="!groupForm.participants.length"><p class="text-sm text-slate-500">Choose at least one member besides you.</p></template><template x-for="item in groupForm.participants" :key="item.type + '-' + item.id"><div class="flex items-center justify-between rounded-2xl bg-slate-50 px-3 py-2"><div><div class="text-sm font-medium text-slate-800" x-text="item.name"></div><div class="text-xs text-slate-500" x-text="item.subtitle"></div></div><button type="button" @click="toggleMember(item)" class="text-sm text-rose-500">Remove</button></div></template></div></div></div>
            <div class="mt-6 flex justify-end gap-3"><button type="button" @click="closeCreateGroup()" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600">Cancel</button><button type="button" @click="createGroup()" class="rounded-2xl bg-slate-900 px-5 py-2 text-sm font-semibold text-white">Create group</button></div>
        </div>
    </div>

    <div x-cloak x-show="showManageMembersModal && activeConversation?.is_group" class="fixed inset-0 z-50 bg-slate-950/40 p-4 backdrop-blur-sm">
        <div class="mx-auto mt-10 max-w-2xl rounded-[28px] bg-white p-6 shadow-2xl">
            <div class="flex items-center justify-between gap-4"><div><h3 class="text-xl font-semibold text-slate-900">Manage Members</h3><p class="mt-1 text-sm text-slate-500" x-text="activeConversation?.title"></p></div><button type="button" @click="showManageMembersModal = false" class="text-2xl text-slate-400 hover:text-slate-700">&times;</button></div>
            <div class="mt-5"><input x-model="manageMemberSearch" @input.debounce.250ms="loadGroupCandidates()" type="text" placeholder="Search users or admins to add" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-slate-400 focus:ring-4 focus:ring-slate-200"></div>
            <div class="mt-4 grid gap-4 lg:grid-cols-2"><div class="max-h-80 space-y-2 overflow-y-auto rounded-3xl border border-slate-200 bg-slate-50 p-3"><template x-for="member in groupDetails.members || []" :key="member.type + '-' + member.id"><div class="flex items-center justify-between rounded-2xl bg-white px-4 py-3"><div><div class="text-sm font-semibold text-slate-900" x-text="member.name"></div><div class="text-xs text-slate-500" x-text="member.type + ' � ' + member.role"></div></div><button type="button" @click="removeMember(member)" class="text-sm text-rose-500">Remove</button></div></template></div><div class="max-h-80 space-y-2 overflow-y-auto rounded-3xl border border-slate-200 bg-white p-3"><template x-for="item in availableNewMembers()" :key="item.type + '-' + item.id"><button type="button" @click="addMembers([item])" class="flex w-full items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 text-left transition hover:bg-slate-100"><div><div class="text-sm font-semibold text-slate-900" x-text="item.name"></div><div class="text-xs text-slate-500" x-text="item.subtitle + ' � ' + item.email"></div></div><span class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-500">Add</span></button></template></div></div>
        </div>
    </div>
</div>

<script>
function messagingDashboard(config) {
    return {
conversations: [], directConversations: [], groupConversations: [], directCandidates: [], groupCandidates: [], messages: [],
        activeConversationId: config.initialConversationId || null, activeConversation: null, groupDetails: { members: [] }, loadingMessages: false,
        activeTab: 'direct', isWorkspaceVisible: true, exitRoute: config.routes.exitRoute || '/', showDirectPicker: config.currentType === 'admin', directSearch: '', draftMessage: '', selectedFile: null, selectedFileName: '',
        showCreateGroupModal: false, showManageMembersModal: false, groupMemberSearch: '', manageMemberSearch: '',
groupForm: { name: '', description: '', participants: [] }, pollTimer: null, lastLoadTime: 0, loadDebounceMs: 2500, lastConversationLoadTime: 0, conversationLoadDebounceMs: 3000,
        init() { this.loadConversations(); this.loadDirectCandidates(); this.loadGroupCandidates(); this.startPolling(); },
        async loadConversations() {
            const now = Date.now();
            if (now - this.lastConversationLoadTime < this.conversationLoadDebounceMs) return;
            this.lastConversationLoadTime = now;
            const response = await fetch(config.routes.conversations + '?t=' + Date.now(), { credentials: 'include', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await response.json();
            this.conversations = data.conversations || [];
            this.directConversations = this.conversations.filter((item) => !item.is_group);
            this.groupConversations = this.conversations.filter((item) => item.is_group);
            if (!this.activeConversationId && this.conversations.length) { await this.selectConversation(this.conversations[0]); return; }
            if (this.activeConversationId) { this.activeConversation = this.conversations.find((item) => Number(item.id) === Number(this.activeConversationId)) || null; }
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
            await this.loadConversations();
            const conversation = this.conversations.find((entry) => Number(entry.id) === Number(response.data.conversation.id));
            if (conversation) await this.selectConversation(conversation);
            this.showDirectPicker = false;
        },
        async selectConversation(conversation) {
            this.activeConversationId = conversation.id;
            this.activeConversation = conversation;
            await this.loadMessages();
            if (conversation.is_group) await this.loadGroupDetails(); else this.groupDetails = { members: [] };
        },
async loadMessages() {
            if (!this.activeConversationId) return;
            if (this.loadingMessages) return;
            const now = Date.now();
            if (now - this.lastLoadTime < this.loadDebounceMs) return;
            this.lastLoadTime = now;
            this.loadingMessages = true;
            try {
                const response = await axios.get(`${config.routes.messagesBase}/${this.activeConversationId}?t=${now}`);
                const newMessages = response.data.messages || [];
                const existingIds = new Set(this.messages.map(m => m.id));
                const uniqueNewMessages = newMessages.filter(m => !existingIds.has(m.id));
                if (uniqueNewMessages.length > 0) {
                    this.messages = [...this.messages, ...uniqueNewMessages];
                }
                await axios.post(config.routes.read, { conversation_id: this.activeConversationId });
                if (window.dispatchMessageCounterSync) window.dispatchMessageCounterSync('read', { conversationId: this.activeConversationId });
                this.$nextTick(() => { const panel = document.getElementById('messages-panel'); if (panel) panel.scrollTop = panel.scrollHeight; });
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
            await this.loadConversations();
            if (window.dispatchMessageCounterSync) window.dispatchMessageCounterSync('sent', { conversationId: this.activeConversationId });
            this.$nextTick(() => { const panel = document.getElementById('messages-panel'); if (panel) panel.scrollTop = panel.scrollHeight; });
        },
        pickFile(event) { this.selectedFile = event.target.files[0] || null; this.selectedFileName = this.selectedFile ? this.selectedFile.name : ''; },
        clearFile() { this.selectedFile = null; this.selectedFileName = ''; if (this.$refs.fileInput) this.$refs.fileInput.value = ''; },
        isMine(message) { const senderType = String(message.sender_type || '').split('\\').pop().toLowerCase(); return Number(message.sender_id) === Number(config.currentId) && senderType === config.currentType; },
        formatTime(value) { return value ? new Date(value).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : ''; },
        initialFor(value) { return value ? String(value).charAt(0).toUpperCase() : '?'; },
        openCreateGroup() { this.showCreateGroupModal = true; this.groupForm = { name: '', description: '', participants: [] }; this.groupMemberSearch = ''; this.loadGroupCandidates(); },
        closeCreateGroup() { this.showCreateGroupModal = false; },
        toggleMember(item) {
            const key = `${item.type}-${item.id}`;
            const exists = this.groupForm.participants.find((entry) => `${entry.type}-${entry.id}` === key);
            this.groupForm.participants = exists ? this.groupForm.participants.filter((entry) => `${entry.type}-${entry.id}` !== key) : [...this.groupForm.participants, item];
        },
        isSelectedMember(item) { return this.groupForm.participants.some((entry) => Number(entry.id) === Number(item.id) && entry.type === item.type); },
        async createGroup() {
            if (!this.groupForm.name.trim()) return;
            const response = await axios.post(config.routes.groupStore, { name: this.groupForm.name, description: this.groupForm.description, participants: this.groupForm.participants.map((item) => ({ id: item.id, type: item.type })) });
            this.showCreateGroupModal = false;
            await this.loadConversations();
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
                if (this.activeConversationId) { 
                    await this.loadMessages(); 
                    if (this.activeConversation?.is_group) await this.loadGroupDetails(); 
                } 
            }, 5000);
        }
    };
}
</script>
