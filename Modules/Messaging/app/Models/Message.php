<?php

namespace Modules\Messaging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Messaging\Database\Factories\MessageFactory;

class message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'sender_type',
        'body',
        'type',
        'file_path',
        'read_at',
        'is_deleted',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // polymorphic sender
    public function sender()
    {
        return $this->morphTo();
    }
}
