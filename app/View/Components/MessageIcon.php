<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Helpers\AuthParticipant;

class MessageIcon extends Component
{
    public $unreadCount;
    protected $conversationService;
    public $conversations;
    public $currentUserId;
    public $currentUserType;
    public $allUsers;
    public $isAdminDashboard;
    public $userList;

    public function __construct()
    {
        $this->conversationService = app(\Modules\Messaging\Services\ConversationService::class);
        $userId = AuthParticipant::id();
        $userType = AuthParticipant::type();
        $userTypeShort = $userType ? strtolower(class_basename($userType)) : null;

        $this->currentUserId = $userId;
        $this->currentUserType = $userType;
        $this->isAdminDashboard = ($userTypeShort === 'admin');

        if (!$userId || !$userType) {
            $this->unreadCount = 0;
            $this->conversations = collect();
            $this->allUsers = collect();
            $this->userList = [];
            return;
        }

        $query = $this->conversationService->conversationQueryForParticipant($userId, $userType);
        
        // Apply admin/user specific filters if needed (similar to controller)
        if ($userTypeShort === 'user') {
            $query->where(function ($conversationQuery) {
                $adminType = \App\Models\Admin::class;
                $adminTypeShort = strtolower(class_basename($adminType));
                $conversationQuery->where('is_group', true)
                    ->orWhereHas('participants', function ($q) use ($adminType, $adminTypeShort) {
                        $q->whereIn('participant_type', [$adminType, $adminTypeShort])
                            ->whereNull('left_at');
                    });
            });
        }

        $conversations = $query->with('participants')->orderBy('updated_at', 'desc')->get();

        $unreadCount = 0;
        foreach ($conversations as $conversation) {
            $unread = $this->conversationService->getUnreadCountForConversation($conversation, $userId, $userType);
            $unreadCount += $unread;
            $conversation->unread_count = $unread;
        }

        $this->unreadCount = $unreadCount;
        $this->conversations = $conversations->take(10)->values();

        // For admin, get users
        if ($this->isAdminDashboard) {
            $this->userList = \App\Models\User::where('id', '!=', $userId)
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
                ->all();
        } else {
            $this->userList = [];
        }
        $this->allUsers = collect();
    }

    public function render(): View|Closure|string
    {
        return view('components.message-icon');
    }
}
