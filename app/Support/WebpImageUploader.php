<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

class WebpImageUploader
{
    public static function store(UploadedFile $file, string $directory, string $prefix, int $quality = 78, ?string $disk = null): string
    {
        $manager = ImageManager::gd();
        $image = $manager->read($file->getRealPath());
        $disk ??= config('filesystems.default', 'public');

        $safePrefix = trim($prefix, "-_ ");
        $filename = sprintf('%s-%s.webp', $safePrefix !== '' ? $safePrefix : 'image', Str::uuid()->toString());
        $path = trim($directory, '/');
        $relativePath = $path === '' ? $filename : $path . '/' . $filename;

        Storage::disk($disk)->put($relativePath, $image->toWebp($quality)->toString());

        return $relativePath;
    }
}
