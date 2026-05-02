<?php

return [
    'features' => [
        'enabled' => true,
        'groups' => true,
        'voice_chat' => true,
        'audio_call' => true,
        'video_call' => true,
        'ai_summary' => true,
        'file_upload' => true,
        'notifications' => true,
        'online_status' => true,
        'pinning' => true,
    ],
    'upload' => [
        'disk' => 'public',
        'base_folder' => 'chat-images',
        'visibility' => 'public',
    ],
];
