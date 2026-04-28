<?php

namespace Modules\Messaging\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Modules\Messaging\Helpers\AuthParticipant;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\ConversationParticipant;
use Modules\Messaging\Models\Message;
use Modules\Messaging\Events\MessageSent;
use Modules\Messaging\Services\ConversationService;

class MessageController extends Controller
{
    public function __construct(
        protected ConversationService $conversationService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('messaging::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('messaging::create');
    }

    public function send(Request $request)
    {
        try {
            $request->validate([
                'conversation_id' => 'required|integer',
                'file' => 'nullable|file|max:10240'
            ]);

            $senderId = AuthParticipant::id();
            $senderType = AuthParticipant::type();
            $guard = AuthParticipant::guard();

            \Illuminate\Support\Facades\Log::info('Auth check', [
                'guard' => $guard,
                'id' => $senderId,
                'type' => $senderType
            ]);

            if (!$senderId || !$senderType) {
                return response()->json(['error' => 'Unauthorized: No authenticated user', 'debug' => ['guard' => AuthParticipant::guard()]], 401);
            }

            $conversationId = $request->conversation_id;
            $senderTypeShort = strtolower(class_basename($senderType));

            $conversation = Conversation::where('id', $conversationId)
                ->whereHas('participants', function ($q) use ($senderId, $senderType, $senderTypeShort) {
                    $q->where('participant_id', $senderId)
                        ->whereIn('participant_type', [$senderType, $senderTypeShort])
                        ->whereNull('left_at');
                })
                ->first();

            if (!$conversation) {
                return response()->json(['error' => 'Conversation not found or you are not a participant'], 404);
            }

            // Handle file upload
            $filePath = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar', 'webm', 'mp3', 'wav', 'ogg', 'm4a', 'aac'];
                $extension = $file->getClientOriginalExtension();

                if (in_array(strtolower($extension), $allowedExtensions)) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('uploads/messages'), $fileName);
                    $filePath = 'uploads/messages/' . $fileName;
                }
            }

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $senderId,
                'sender_type' => $senderType,
                'body' => $request->message ?? '',
                'type' => $filePath ? 'file' : 'text',
                'file_path' => $filePath,
            ]);

            $conversation->touch();

            ConversationParticipant::where('conversation_id', $conversation->id)
                ->where('participant_id', $senderId)
                ->whereIn('participant_type', [$senderType, $senderTypeShort])
                ->update(['last_read_at' => now()]);

            $senderModel = AuthParticipant::model();
            $senderName = $senderModel ? $senderModel->name : 'Unknown';

            try {
                event(new MessageSent($message));
            } catch (\Exception $broadcastException) {
                \Illuminate\Support\Facades\Log::error('Broadcast failed: ' . $broadcastException->getMessage());
            }

            $response = [
                'id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'conversation_name' => $conversation->name,
                'is_group' => (bool) $conversation->is_group,
                'sender_id' => $message->sender_id,
                'sender_type' => $message->sender_type,
                'sender_name' => $senderName,
                'body' => $message->body,
                'type' => $message->type,
                'created_at' => $message->created_at,
                'read_at' => $message->read_at,
                'debug' => [
                    'guard' => AuthParticipant::guard(),
                    'senderTypeShort' => strtolower(class_basename($senderType))
                ]
            ];

            if ($filePath) {
                $response['file_url'] = asset($filePath);
                $response['file_name'] = $request->file('file')->getClientOriginalName();
            }

            return response()->json($response);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Send message error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function markRead(Request $request)
    {
        $conversationId = $request->conversation_id;

        if (!$conversationId) {
            return response()->json(['error' => 'Conversation ID required'], 400);
        }

        $userId = AuthParticipant::id();
        $userType = AuthParticipant::type();

        if (!$userId || !$userType) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userTypeShort = strtolower(class_basename($userType));

        $participant = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('participant_id', $userId)
            ->whereIn('participant_type', [$userType, $userTypeShort])
            ->whereNull('left_at')
            ->first();

        if (!$participant) {
            return response()->json(['error' => 'Conversation not found or you are not a participant'], 404);
        }

        $readAt = now();

        $participant->update(['last_read_at' => $readAt]);

        $updated = Message::where('conversation_id', $conversationId)
            ->whereNull('read_at')
            ->where(function ($query) use ($userId, $userType, $userTypeShort) {
                $query->where('sender_id', '!=', $userId)
                    ->orWhereNotIn('sender_type', [$userType, $userTypeShort]);
            })
            ->update(['read_at' => $readAt]);

        return response()->json([
            'marked_read' => $updated,
            'last_read_at' => $readAt->toISOString(),
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('messaging::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('messaging::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $message = Message::findOrFail($id);
        $conversation = $this->authorizedConversation($message->conversation_id);
        $this->ensureOwnMessage($message);

        $message->body = trim((string) $request->body);
        $message->save();
        $conversation->touch();

        return response()->json([
            'success' => true,
            'message' => $this->transformMessage($message->fresh()),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $message = Message::findOrFail($id);
        $conversation = $this->authorizedConversation($message->conversation_id);
        $this->ensureOwnMessage($message);

        $this->deleteMessageFile($message);
        $message->delete();
        $conversation->touch();

        return response()->json([
            'success' => true,
            'message_id' => (int) $id,
        ]);
    }

    public function clearConversation($conversationId)
    {
        $conversation = $this->authorizedConversation($conversationId);

        DB::transaction(function () use ($conversationId, $conversation) {
            Message::where('conversation_id', $conversationId)
                ->whereNotNull('file_path')
                ->get()
                ->each(function (Message $message) {
                    $this->deleteMessageFile($message);
                });

            Message::where('conversation_id', $conversationId)->delete();
            $conversation->touch();
        });

        return response()->json([
            'success' => true,
        ]);
    }

    protected function authorizedConversation($conversationId): Conversation
    {
        $userId = AuthParticipant::id();
        $userType = AuthParticipant::type();

        abort_unless($userId && $userType, 401, 'Unauthorized');

        $userTypeShort = strtolower(class_basename($userType));

        $conversation = Conversation::where('id', $conversationId)
            ->whereHas('participants', function ($q) use ($userId, $userType, $userTypeShort) {
                $q->where('participant_id', $userId)
                    ->whereIn('participant_type', [$userType, $userTypeShort])
                    ->whereNull('left_at');
            })
            ->first();

        abort_unless($conversation, 403, 'Conversation not found or you are not a participant');

        return $conversation;
    }

    protected function ensureOwnMessage(Message $message): void
    {
        $senderId = AuthParticipant::id();
        $senderType = AuthParticipant::type();
        $senderTypeShort = strtolower(class_basename($senderType));
        $messageTypeShort = strtolower(class_basename((string) $message->sender_type));

        abort_unless(
            (int) $message->sender_id === (int) $senderId
                && in_array($messageTypeShort, [$senderTypeShort], true),
            403,
            'You can only modify your own messages'
        );
    }

    protected function transformMessage(Message $message): array
    {
        $sender = $message->sender;

        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
            'sender_type' => $message->sender_type,
            'sender_name' => $sender ? ($sender->name ?? 'Unknown') : 'Unknown',
            'body' => $message->body,
            'type' => $message->type,
            'file_path' => $message->file_path,
            'file_url' => $message->file_path ? asset($message->file_path) : null,
            'created_at' => $message->created_at,
            'updated_at' => $message->updated_at,
            'read_at' => $message->read_at,
            'file_name' => $message->file_path ? basename($message->file_path) : null,
        ];
    }

    protected function deleteMessageFile(Message $message): void
    {
        if (!$message->file_path) {
            return;
        }

        $absolutePath = public_path($message->file_path);
        if (File::exists($absolutePath)) {
            File::delete($absolutePath);
        }
    }
}
