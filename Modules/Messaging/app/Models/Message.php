<?php

namespace Modules\Messaging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Messaging\Database\Factories\MessageFactory;

class Message extends Model
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

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->morphTo();
    }

    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    public function isRead()
    {
        return !is_null($this->read_at);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }
}
