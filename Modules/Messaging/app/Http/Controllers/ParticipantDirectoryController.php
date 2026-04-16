<?php

namespace Modules\Messaging\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Messaging\Helpers\AuthParticipant;
use Modules\Messaging\Services\ConversationService;

class ParticipantDirectoryController extends Controller
{
    public function __construct(
        protected ConversationService $conversationService
    ) {}

    public function index(Request $request)
    {
        $participantId = AuthParticipant::id();
        $participantType = AuthParticipant::type();

        if (!$participantId || !$participantType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $items = $this->conversationService->availableParticipants(
            $participantId,
            $participantType,
            $request->string('mode')->value() ?: 'group',
            $request->string('q')->value()
        );

        return response()->json([
            'items' => $items,
            'total' => $items->count(),
        ]);
    }

    public function adminUsers(Request $request)
    {
        $request->merge(['mode' => 'direct']);

        $response = $this->index($request);
        $payload = $response->getData(true);

        return response()->json([
            'users' => collect($payload['items'] ?? [])
                ->where('type', 'user')
                ->values(),
            'total' => collect($payload['items'] ?? [])->where('type', 'user')->count(),
        ]);
    }
}
