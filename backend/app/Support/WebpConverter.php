<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class WebpConverter
{
    /**
     * Convert an uploaded image (on the given disk) to WebP, resizing it down
     * to fit within $maxDimension px. Returns the new relative path, or the
     * original path unchanged if conversion isn't applicable/fails.
     */
    public static function convert(string $path, string $disk = 'public', int $maxDimension = 1600, int $quality = 85): string
    {
        if ($path === '' || str_starts_with($path, 'http') || str_ends_with(strtolower($path), '.webp')) {
            return $path;
        }

        if (!preg_match('/\.(jpe?g|png|gif|bmp|tiff?)$/i', $path)) {
            return $path;
        }

        $storage  = Storage::disk($disk);
        $fullPath = $storage->path($path);

        if (!file_exists($fullPath)) {
            return $path;
        }

        try {
            $img = Image::read($fullPath);
            $img->scaleDown($maxDimension, $maxDimension);

            $webpPath = preg_replace('/\.(jpe?g|png|gif|bmp|tiff?)$/i', '.webp', $path);
            $webpFull = $storage->path($webpPath);

            @mkdir(dirname($webpFull), 0755, true);
            $img->toWebp($quality)->save($webpFull);

            if ($path !== $webpPath && file_exists($fullPath)) {
                @unlink($fullPath);
            }

            return $webpPath;
        } catch (\Throwable $e) {
            \Log::error('WebP conversion failed: ' . $e->getMessage());
            return $path;
        }
    }
}
