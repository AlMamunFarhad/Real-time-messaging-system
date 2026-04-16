<?php

namespace Modules\Messaging\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Messaging\Helpers\AuthParticipant;
use Modules\Messaging\Services\ConversationService;

class GroupController extends Controller
{
    public function __construct(
        protected ConversationService $conversationService
    ) {}

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'participants' => 'nullable|array',
            'participants.*.id' => 'required|integer',
            'participants.*.type' => 'required|string|in:user,admin',
        ]);

        $participantId = AuthParticipant::id();
        $participantType = AuthParticipant::type();

        if (!$participantId || !$participantType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $group = $this->conversationService->createGroupConversation(
            $request->string('name')->trim()->value(),
            $request->string('description')->trim()->value() ?: null,
            $participantId,
            $participantType,
            $request->input('participants', [])
        );

        return response()->json([
            'message' => 'Group created successfully',
            'group_id' => $group->id,
        ], 201);
    }

    public function show(int $conversationId)
    {
        $participantId = AuthParticipant::id();
        $participantType = AuthParticipant::type();

        if (!$participantId || !$participantType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $conversation = $this->conversationService->getConversationForParticipant($conversationId, $participantId, $participantType);

        if (!$conversation || !$conversation->isGroup()) {
            return response()->json(['error' => 'Group not found'], 404);
        }

        $members = $conversation->participants->map(function ($participant) {
            return [
                'id' => $participant->participant_id,
                'type' => $this->conversationService->participantTypeKey($participant->participant_type),
                'name' => $this->conversationService->getParticipantDisplayName(
                    (int) $participant->participant_id,
                    $participant->participant_type
                ),
                'role' => $participant->role,
                'joined_at' => optional($participant->joined_at)?->toISOString(),
            ];
        })->values();

        return response()->json([
            'group' => [
                'id' => $conversation->id,
                'name' => $conversation->name,
                'description' => $conversation->description,
                'is_group' => true,
                'members_count' => $members->count(),
                'can_manage' => $this->conversationService->canManageGroup($conversation, $participantId, $participantType),
                'members' => $members,
            ],
        ]);
    }

    public function addMembers(Request $request, int $conversationId)
    {
        $request->validate([
            'participants' => 'required|array|min:1',
            'participants.*.id' => 'required|integer',
            'participants.*.type' => 'required|string|in:user,admin',
        ]);

        $participantId = AuthParticipant::id();
        $participantType = AuthParticipant::type();

        if (!$participantId || !$participantType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $conversation = $this->conversationService->getConversationForParticipant($conversationId, $participantId, $participantType);

        if (!$conversation || !$conversation->isGroup()) {
            return response()->json(['error' => 'Group not found'], 404);
        }

        if (!$this->conversationService->canManageGroup($conversation, $participantId, $participantType)) {
            return response()->json(['error' => 'Only group admins can add members'], 403);
        }

        $this->conversationService->attachParticipants(
            $conversation,
            $request->input('participants', []),
            $participantId,
            $participantType
        );

        return response()->json(['message' => 'Members added successfully']);
    }

    public function removeMember(int $conversationId, string $memberType, int $memberId)
    {
        $participantId = AuthParticipant::id();
        $participantType = AuthParticipant::type();

        if (!$participantId || !$participantType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $conversation = $this->conversationService->getConversationForParticipant($conversationId, $participantId, $participantType);

        if (!$conversation || !$conversation->isGroup()) {
            return response()->json(['error' => 'Group not found'], 404);
        }

        if (!$this->conversationService->canManageGroup($conversation, $participantId, $participantType)) {
            return response()->json(['error' => 'Only group admins can remove members'], 403);
        }

        $removed = $this->conversationService->removeParticipant($conversation, $memberId, $memberType);

        if (!$removed) {
            return response()->json(['error' => 'Member not found'], 404);
        }

        return response()->json(['message' => 'Member removed successfully']);
    }

    public function leave(int $conversationId)
    {
        $participantId = AuthParticipant::id();
        $participantType = AuthParticipant::type();

        if (!$participantId || !$participantType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $conversation = $this->conversationService->getConversationForParticipant($conversationId, $participantId, $participantType);

        if (!$conversation || !$conversation->isGroup()) {
            return response()->json(['error' => 'Group not found'], 404);
        }

        $removed = $this->conversationService->removeParticipant($conversation, $participantId, $participantType);

        if (!$removed) {
            return response()->json(['error' => 'Unable to leave the group'], 422);
        }

        return response()->json(['message' => 'You left the group']);
    }
}
