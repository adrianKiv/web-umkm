<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class StorageFile
{
    public static function deleteIfExists(?string $path, array $extraDisks = []): void
    {
        if (!$path || $path === '-' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $disks = array_values(array_unique(array_filter(array_merge([
            config('filesystems.default', 'public'),
            'azure',
            'public',
        ], $extraDisks))));

        foreach ($disks as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    Storage::disk($disk)->delete($path);
                }
            } catch (\Throwable $throwable) {
                // Ignore deletion failures on disks that are not available in the current environment.
            }
        }
    }
}
