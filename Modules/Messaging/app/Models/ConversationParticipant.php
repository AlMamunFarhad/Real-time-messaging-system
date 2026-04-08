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
        'last_read_at',
        'joined_at',
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
}
