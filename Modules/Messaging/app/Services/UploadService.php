<?php

namespace Modules\Messaging\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class UploadService
{
    /**
     * Upload a file using the configuration settings.
     *
     * @param UploadedFile $file
     * @param string $subFolder
     * @return string|null
     * @throws \Exception
     */
    public function upload(UploadedFile $file, string $subFolder = ''): ?string
    {
        // 4. Upload Flow - Check if features are enabled
        if (!config('messaging.features.enabled') || !config('messaging.features.file_upload')) {
            throw new \Exception('File upload feature is disabled.');
        }

        // 2. Extend Existing Config - Get settings from messaging config
        $disk = config('messaging.upload.disk', 'public');
        $baseFolder = config('messaging.upload.base_folder', 'chat-images');
        $visibility = config('messaging.upload.visibility', 'public');

        // 7 & 8. Folder Flexibility & Security - Only use config-defined base folder
        $path = trim($baseFolder, '/');
        if ($subFolder) {
            $path .= '/' . trim($subFolder, '/');
        }
        
        // Generate a unique filename to prevent overwriting
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();

        // 3. Storage System - Use Laravel Storage facade and disk from config
        $storedPath = Storage::disk($disk)->putFileAs($path, $file, $filename, $visibility);

        // 5. Path Handling - Save only relative file path (handled by Laravel Storage)
        return $storedPath;
    }

    /**
     * Generate the full URL for a given relative path.
     *
     * @param string|null $path
     * @return string|null
     */
    public function getUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        
        $disk = config('messaging.upload.disk', 'public');
        
        // 6. URL Generation - Always generate file URL using Storage disk
        return Storage::disk($disk)->url($path);
    }
}
