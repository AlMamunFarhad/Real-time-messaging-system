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
    public $conversations;
    public $currentUserId;
    public $currentUserType;
    public $allUsers;
    public $isAdminDashboard;
    public $userList;
    public $lastPage;

    public function __construct()
    {
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
            $this->lastPage = 1;
            return;
        }

        $conversations = Conversation::whereHas('participants', function ($q) use ($userId, $userType, $userTypeShort) {
                $q->where('participant_id', $userId)
                  ->whereIn('participant_type', [$userType, $userTypeShort]);
            })
            ->with(['participants', 'lastMessage'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $unreadCount = 0;
        foreach ($conversations as $conversation) {
            $participant = $conversation->participants->first(function ($participant) use ($userId, $userType, $userTypeShort) {
                $participantType = match ($participant->participant_type ?? null) {
                    'admin' => \App\Models\Admin::class,
                    'user' => \App\Models\User::class,
                    default => $participant->participant_type,
                };
                $participantTypeShort = strtolower(class_basename($participantType ?? ''));

                return (int) $participant->participant_id === (int) $userId
                    && ($participantType === $userType || $participantTypeShort === $userTypeShort);
            });

            $unread = $conversation->messages()
                ->when($participant?->last_read_at, fn ($query) => $query->where('created_at', '>', $participant->last_read_at))
                ->where(function ($query) use ($userId, $userType, $userTypeShort) {
                    $query->where('sender_id', '!=', $userId)
                        ->orWhereNotIn('sender_type', [$userType, $userTypeShort]);
                })
                ->count();
            $unreadCount += $unread;
            
            $conversation->unread_count = $unread;
        }

        $this->unreadCount = $unreadCount;
        $this->conversations = $conversations->take(10)->values();

        // For admin, get users (10 per page loaded via AJAX)
        if ($this->isAdminDashboard) {
            $paginator = \App\Models\User::where('id', '!=', $userId)->orderBy('id', 'desc')->paginate(10);
            $this->userList = $paginator->items();
            $this->lastPage = $paginator->lastPage();
        } else {
            $this->userList = [];
            $this->lastPage = 1;
        }
        $this->allUsers = collect();
    }

    public function render(): View|Closure|string
    {
        return view('components.message-icon');
    }
}
