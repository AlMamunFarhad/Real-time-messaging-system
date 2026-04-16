<?php

namespace Modules\Messaging\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Modules\Messaging\Helpers\AuthParticipant;
use Modules\Messaging\Services\ConversationService;

class ChatController extends Controller
{
    public function __construct(
        protected ConversationService $conversationService
    ) {}

    public function dashboard(Request $request)
    {
        $participantId = AuthParticipant::id();
        $participantType = AuthParticipant::type();

        abort_unless($participantId && $participantType, 401, 'Unauthorized');

        return view('messaging::chat.dashboard', [
            'currentParticipantId' => $participantId,
            'currentParticipantType' => $participantType,
            'currentParticipantTypeShort' => $this->conversationService->participantTypeKey($participantType),
            'initialConversationId' => (int) $request->integer('conversation'),
        ]);
    }

    public function directConversation(Request $request)
    {
        $request->validate([
            'target_id' => 'required|integer',
            'target_type' => 'required|string|in:user,admin',
        ]);

        $participantId = AuthParticipant::id();
        $participantType = AuthParticipant::type();

        if (!$participantId || !$participantType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $targetId = (int) $request->integer('target_id');
        $targetType = $this->conversationService->resolveParticipantType($request->string('target_type')->value());
        $currentTypeShort = $this->conversationService->participantTypeKey($participantType);
        $targetTypeShort = $this->conversationService->participantTypeKey($targetType);

        if ($currentTypeShort === 'admin' && $targetTypeShort !== 'user') {
            return response()->json(['error' => 'Admins can only start direct chats with users'], 422);
        }

        if ($currentTypeShort === 'user' && $targetTypeShort !== 'admin') {
            return response()->json(['error' => 'Users can only start direct chats with admins'], 422);
        }

        $conversation = $this->conversationService->findOrCreateDirectConversation(
            $participantId,
            $participantType,
            $targetId,
            $targetType
        );

        $otherParticipant = $conversation->participants->first(function ($participant) use ($participantId, $participantType) {
            return !(
                (int) $participant->participant_id === (int) $participantId
                && $this->conversationService->matchesParticipantType($participant->participant_type, $participantType)
            );
        });

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'is_group' => false,
            ],
            'other_participant' => [
                'id' => $otherParticipant?->participant_id,
                'type' => $this->conversationService->participantTypeKey($otherParticipant?->participant_type),
                'name' => $otherParticipant
                    ? $this->conversationService->getParticipantDisplayName(
                        (int) $otherParticipant->participant_id,
                        $otherParticipant->participant_type
                    )
                    : null,
            ],
        ]);
    }

    public function userConversation()
    {
        $participantId = AuthParticipant::id();
        $participantType = AuthParticipant::type();

        if (!$participantId || !$participantType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $admin = Admin::query()->orderBy('id')->first();

        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
        }

        request()->merge([
            'target_id' => $admin->id,
            'target_type' => 'admin',
        ]);

        return $this->directConversation(request());
    }
}
