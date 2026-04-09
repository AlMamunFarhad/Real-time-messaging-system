<?php

namespace Modules\Messaging\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['type'];

    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    public function getUnreadCountForUser($userId, $userType)
    {
        return $this->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $userId)
            ->where('sender_type', '!=', $userType)
            ->count();
    }
}
