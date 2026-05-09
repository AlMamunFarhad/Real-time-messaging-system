<?php

namespace Modules\Messaging\Services;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Messaging\Models\Conversation;
use Modules\Messaging\Models\ConversationParticipant;

class ConversationService
{
    public function buildMessagePreview(?string $body, ?string $filePath, string $fallback = 'No message yet'): string
    {
        $body = trim((string) $body);

        if ($body !== '') {
            return $body;
        }

        if ($filePath) {
            return preg_match('/\.(webm|mp3|wav|ogg|m4a|aac)$/i', $filePath)
                ? 'Voice Message'
                : 'Photo';
        }

        return $fallback;
    }

    public function resolveParticipantType(?string $type): ?string
    {
        if (!$type) return null;

        $type = strtolower($type);
        
        return match ($type) {
            'admin', 'app\\models\\admin' => Admin::class,
            'user', 'app\\models\\user' => User::class,
            default => $type,
        };
    }

    public function participantTypeKey(?string $type): ?string
    {
        $resolved = $this->resolveParticipantType($type);

        return $resolved ? strtolower(class_basename($resolved)) : null;
    }

    public function matchesParticipantType(?string $actualType, ?string $expectedType): bool
    {
        $resolvedActual = $this->resolveParticipantType($actualType);
        $resolvedExpected = $this->resolveParticipantType($expectedType);

        if (!$resolvedActual || !$resolvedExpected) {
            return false;
        }

        return $resolvedActual === $resolvedExpected
            || strtolower(class_basename($resolvedActual)) === strtolower(class_basename($resolvedExpected));
    }

    public function getParticipantModel(int $id, string $type): ?Model
    {
        $resolved = $this->resolveParticipantType($type);

        if (!$resolved || !class_exists($resolved)) {
            return null;
        }

        return $resolved::query()->find($id);
    }

    public function getParticipantDisplayName(int $id, string $type): string
    {
        $model = $this->getParticipantModel($id, $type);

        return $model?->name ?? ($model?->email ?? 'Participant #' . $id);
    }

    public function findParticipantRecord(Conversation $conversation, int $participantId, string $participantType): ?ConversationParticipant
    {
        $conversation->loadMissing('participants');

        return $conversation->participants->first(function ($participant) use ($participantId, $participantType) {
            return (int) $participant->participant_id === (int) $participantId
                && $this->matchesParticipantType($participant->participant_type, $participantType)
                && is_null($participant->left_at);
        });
    }

    public function isParticipant(Conversation $conversation, int $participantId, string $participantType): bool
    {
        return $this->findParticipantRecord($conversation, $participantId, $participantType) !== null;
    }

    public function canManageGroup(Conversation $conversation, int $participantId, string $participantType): bool
    {
        if (!$conversation->isGroup()) {
            return false;
        }

        $membership = $this->findParticipantRecord($conversation, $participantId, $participantType);

        if (!$membership) {
            return false;
        }

        return $membership->role === 'admin'
            || (
                (int) $conversation->created_by_id === (int) $participantId
                && $this->matchesParticipantType($conversation->created_by_type, $participantType)
            );
    }

    public function findOrCreateDirectConversation(int $senderId, string $senderType, int $receiverId, string $receiverType): Conversation
    {
        $senderType = $this->resolveParticipantType($senderType);
        $receiverType = $this->resolveParticipantType($receiverType);

        $conversation = Conversation::query()
            ->direct()
            ->whereHas('participants', function ($query) use ($senderId, $senderType) {
                $query->where('participant_id', $senderId)
                    ->whereIn('participant_type', [$senderType, $this->participantTypeKey($senderType)])
                    ->whereNull('left_at');
            })
            ->whereHas('participants', function ($query) use ($receiverId, $receiverType) {
                $query->where('participant_id', $receiverId)
                    ->whereIn('participant_type', [$receiverType, $this->participantTypeKey($receiverType)])
                    ->whereNull('left_at');
            })
            ->first();

        if ($conversation) {
            return $conversation->load('participants');
        }

        return DB::transaction(function () use ($senderId, $senderType, $receiverId, $receiverType) {
            $conversation = Conversation::create([
                'type' => 'private',
                'is_group' => false,
                'created_by_id' => $senderId,
                'created_by_type' => $senderType,
            ]);

            $this->attachParticipants($conversation, [
                ['id' => $senderId, 'type' => $senderType, 'role' => 'member'],
                ['id' => $receiverId, 'type' => $receiverType, 'role' => 'member'],
            ], $senderId, $senderType);

            return $conversation->load('participants');
        });
    }

    public function createGroupConversation(
        string $name,
        ?string $description,
        int $creatorId,
        string $creatorType,
        array $participants
    ): Conversation {
        $creatorType = $this->resolveParticipantType($creatorType);

        return DB::transaction(function () use ($name, $description, $creatorId, $creatorType, $participants) {
            $conversation = Conversation::create([
                'type' => 'group',
                'name' => $name,
                'description' => $description,
                'is_group' => true,
                'created_by_id' => $creatorId,
                'created_by_type' => $creatorType,
            ]);

            $allParticipants = collect($participants)
                ->prepend([
                    'id' => $creatorId,
                    'type' => $creatorType,
                    'role' => 'admin',
                ])
                ->unique(fn ($participant) => $participant['id'] . '|' . $this->participantTypeKey($participant['type']))
                ->values()
                ->all();

            $this->attachParticipants($conversation, $allParticipants, $creatorId, $creatorType);

            return $conversation->load('participants');
        });
    }

    public function attachParticipants(
        Conversation $conversation,
        array $participants,
        int $actorId,
        string $actorType
    ): Collection {
        $actorType = $this->resolveParticipantType($actorType);
        $attached = collect();

        foreach ($participants as $participant) {
            $participantId = (int) ($participant['id'] ?? 0);
            $participantType = $this->resolveParticipantType($participant['type'] ?? null);

            if (!$participantId || !$participantType) {
                continue;
            }

            $record = ConversationParticipant::query()->firstOrNew([
                'conversation_id' => $conversation->id,
                'participant_id' => $participantId,
                'participant_type' => $participantType,
            ]);

            $record->fill([
                'role' => $participant['role'] ?? ($conversation->isGroup() ? 'member' : 'member'),
                'added_by_id' => $actorId,
                'added_by_type' => $actorType,
                'joined_at' => $record->joined_at ?? now(),
                'left_at' => null,
            ]);
            $record->save();

            $attached->push($record);
        }

        return $attached;
    }

    public function removeParticipant(Conversation $conversation, int $participantId, string $participantType): bool
    {
        $participant = $this->findParticipantRecord($conversation, $participantId, $participantType);

        if (!$participant) {
            return false;
        }

        return DB::transaction(function () use ($conversation, $participant) {
            $participant->update([
                'left_at' => now(),
            ]);

            $remainingParticipants = $conversation->participants()
                ->whereNull('left_at')
                ->count();

            $remainingAdmins = $conversation->participants()
                ->whereNull('left_at')
                ->where('role', 'admin')
                ->count();

            if ($remainingParticipants === 0 || ($participant->isAdmin() && $remainingAdmins === 0)) {
                $conversation->delete();
            }

            return true;
        });
    }

    public function getConversationForParticipant(int $conversationId, int $participantId, string $participantType): ?Conversation
    {
        $participantType = $this->resolveParticipantType($participantType);

        return Conversation::query()
            ->whereKey($conversationId)
            ->whereHas('participants', function ($query) use ($participantId, $participantType) {
                $query->where('participant_id', $participantId)
                    ->whereIn('participant_type', [$participantType, $this->participantTypeKey($participantType)])
                    ->whereNull('left_at');
            })
            ->with([
                'participants' => function ($query) {
                    $query->whereNull('left_at');
                },
                'lastMessage',
            ])
            ->first();
    }

    public function conversationQueryForParticipant(int $participantId, string $participantType)
    {
        $participantType = $this->resolveParticipantType($participantType);
        $typeShort = strtolower(class_basename($participantType));

        return Conversation::query()
            ->whereHas('participants', function ($query) use ($participantId, $participantType, $typeShort) {
                $query->where('participant_id', $participantId)
                    ->whereIn('participant_type', [$participantType, $typeShort])
                    ->whereNull('left_at');
            });
    }

    public function getUnreadCountForParticipant(int $participantId, string $participantType): int
    {
        $query = $this->conversationQueryForParticipant($participantId, $participantType);
        $conversations = $query->with('participants')->get();
        
        $total = 0;
        foreach ($conversations as $conversation) {
            $total += $this->getUnreadCountForConversation($conversation, $participantId, $participantType);
        }
        return $total;
    }

    public function getUnreadCountForConversation(Conversation $conversation, int $participantId, string $participantType): int
    {
        $participantType = $this->resolveParticipantType($participantType);
        $typeShort = strtolower(class_basename($participantType));
        
        $participant = $conversation->participants->first(function ($p) use ($participantId, $participantType, $typeShort) {
            return (int) $p->participant_id === (int) $participantId
                && in_array($p->participant_type, [$participantType, $typeShort], true);
        });

        if (!$participant) {
            return 0;
        }

        return $conversation->messages()
            ->whereNull('read_at')
            ->where(function ($query) use ($participantId, $participantType, $typeShort) {
                $query->where('sender_id', '!=', $participantId)
                    ->orWhereNotIn('sender_type', [$participantType, $typeShort]);
            })
            ->count();
    }

    public function availableParticipants(int $currentId, string $currentType, string $mode = 'group', ?string $search = null): Collection
    {
        $currentType = $this->resolveParticipantType($currentType);
        $search = trim((string) $search);

        $queryUsers = User::query()->orderBy('name');
        $queryAdmins = Admin::query()->orderBy('name');

        if ($search !== '') {
            $filter = function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            };
            $queryUsers->where($filter);
            $queryAdmins->where($filter);
        }

        if ($mode === 'direct') {
            $currentIdInt = (int) $currentId;
            $currentTypeKey = $this->participantTypeKey($currentType);

            if ($currentTypeKey === 'admin') {
                return $queryUsers->get(['id', 'name', 'email'])
                    ->map(fn ($user) => $this->formatDirectoryItem($user, 'user', $currentIdInt, $currentTypeKey))
                    ->sort(function ($a, $b) {
                        if ($a['is_pinned'] === $b['is_pinned']) {
                            return strcasecmp($a['name'], $b['name']);
                        }
                        return $b['is_pinned'] <=> $a['is_pinned'];
                    })
                    ->values();
            }

            return $queryAdmins->get(['id', 'name', 'email'])
                ->map(fn ($admin) => $this->formatDirectoryItem($admin, 'admin', $currentIdInt, $currentTypeKey))
                ->sort(function ($a, $b) {
                    if ($a['is_pinned'] === $b['is_pinned']) {
                        return strcasecmp($a['name'], $b['name']);
                    }
                    return $b['is_pinned'] <=> $a['is_pinned'];
                })
                ->values();
        }

        $users = $queryUsers->get(['id', 'name', 'email']);
        $admins = $queryAdmins->get(['id', 'name', 'email']);

        $currentIdInt = (int) $currentId;
        $currentTypeKey = $this->participantTypeKey($currentType);

        return collect()
            ->merge($users->map(fn ($user) => $this->formatDirectoryItem($user, 'user', $currentIdInt, $currentTypeKey)))
            ->merge($admins->map(fn ($admin) => $this->formatDirectoryItem($admin, 'admin', $currentIdInt, $currentTypeKey)))
            ->reject(function ($item) use ($currentIdInt, $currentTypeKey) {
                return (int) $item['id'] === $currentIdInt
                    && $item['type'] === $currentTypeKey;
            })
            ->sort(function ($a, $b) {
                if ($a['is_pinned'] === $b['is_pinned']) {
                    return strcasecmp($a['name'], $b['name']);
                }
                return $b['is_pinned'] <=> $a['is_pinned'];
            })
            ->values();
    }

    protected function formatDirectoryItem(Model $model, string $type, ?int $currentId = null, ?string $currentType = null): array
    {
        $lastMessage = null;
        $isPinned = false;
        $conversationId = null;
        if ($currentId && $currentType) {
            $currentTypeFull = 'App\\Models\\' . ucfirst($currentType);
            $typeFull = 'App\\Models\\' . ucfirst($type);

            $conversation = \Modules\Messaging\Models\Conversation::query()
                ->direct()
                ->whereHas('participants', fn($q) => $q->where('participant_id', $currentId)->whereIn('participant_type', [$currentType, $currentTypeFull]))
                ->whereHas('participants', fn($q) => $q->where('participant_id', $model->id)->whereIn('participant_type', [$type, $typeFull]))
                ->with(['lastMessage', 'participants' => function($q) use ($currentId, $currentType, $currentTypeFull) {
                    $q->where('participant_id', $currentId)->whereIn('participant_type', [$currentType, $currentTypeFull]);
                }])
                ->first();
            
            if ($conversation) {
                $conversationId = $conversation->id;
                if ($conversation->lastMessage) {
                    $lastMessage = [
                        'body' => $conversation->lastMessage->body,
                        'file_path' => $conversation->lastMessage->file_path,
                        'created_at' => $conversation->lastMessage->created_at,
                    ];
                }
                
                $currentUserParticipant = $conversation->participants->first();
                if ($currentUserParticipant) {
                    $isPinned = (bool) $currentUserParticipant->is_pinned;
                }
            }
        }

        return [
            'id' => $model->id,
            'type' => $type,
            'name' => $model->name ?? ($model->email ?? ucfirst($type) . ' #' . $model->id),
            'email' => $model->email,
            'subtitle' => $type === 'admin' ? 'Admin' : 'User',
            'is_online' => cache()->has('online_' . $type . '_' . $model->id),
            'last_message' => $lastMessage,
            'preview_text' => $this->buildMessagePreview(
                $lastMessage['body'] ?? null,
                $lastMessage['file_path'] ?? null,
                $type === 'admin' ? 'Admin' : 'User'
            ),
            'is_pinned' => $isPinned,
            'conversation_id' => $conversationId,
            'unseen_count' => $conversation ? $this->getUnreadCountForConversation($conversation, $currentId, $currentType) : 0,
        ];
    }
}
