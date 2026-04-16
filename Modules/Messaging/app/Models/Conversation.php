<?php

namespace Modules\Messaging\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Messaging\Models\Message;
// use Modules\Messaging\Database\Factories\ConversationFactory;

class Conversation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'type',
        'name',
        'description',
        'is_group',
        'created_by_id',
        'created_by_type',
    ];

    protected $casts = [
        'is_group' => 'boolean',
    ];

    // protected static function newFactory(): ConversationFactory
    // {
    //     // return ConversationFactory::new();
    // }

    // participants
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
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function creator()
    {
        return $this->morphTo(__FUNCTION__, 'created_by_type', 'created_by_id');
    }

    public function scopeGroup($query)
    {
        return $query->where('is_group', true);
    }

    public function scopeDirect($query)
    {
        return $query->where('is_group', false);
    }

    public function isGroup(): bool
    {
        return (bool) $this->is_group;
    }
}
