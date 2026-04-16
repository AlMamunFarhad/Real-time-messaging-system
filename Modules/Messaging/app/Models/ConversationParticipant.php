<?php

namespace Modules\Messaging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Messaging\Database\Factories\ConversationParticipantFactory;

class ConversationParticipant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    // protected $fillable = [];

    // protected static function newFactory(): ConversationParticipantFactory
    // {
    //     // return ConversationParticipantFactory::new();
    // }

    protected $fillable = [
        'conversation_id',
        'participant_id',
        'participant_type',
        'role',
        'added_by_id',
        'added_by_type',
        'last_read_at',
        'joined_at',
        'left_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // polymorphic relation
    public function participant()
    {
        return $this->morphTo();
    }

    public function addedBy()
    {
        return $this->morphTo(__FUNCTION__, 'added_by_type', 'added_by_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
