<?php

namespace Modules\AIChat\Models;

use Illuminate\Database\Eloquent\Model;

class AIBot extends Model
{
    // Dummy model for AI Bot to satisfy morphTo relationships
    public $name = 'AI Assistant';
    public $avatar = null;

    public function getAttribute($key)
    {
        if ($key === 'name') return config('aichat.settings.ai_name', 'AI Assistant');
        return parent::getAttribute($key);
    }
}
