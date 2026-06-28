<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class StorageUrl
{
    public static function resolve(?string $path, string $defaultAsset): string
    {
        if (!$path || $path === '-') {
            return asset($defaultAsset);
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $disk = env('FILESYSTEM_DISK', 'public');

        if ($disk === 'azure') {
            $baseUrl = rtrim((string) config('filesystems.disks.azure.url', ''), '/');

            if ($baseUrl !== '') {
                return $baseUrl . '/' . ltrim($path, '/');
            }

            return asset($defaultAsset);
        }

        return Storage::disk($disk)->url($path);
    }
}
