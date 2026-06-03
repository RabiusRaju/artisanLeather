<?php

namespace App\Observers;

use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProductImageObserver
{
    public function created(ProductImage $image): void
    {
        $this->processImage($image);
    }

    public function updated(ProductImage $image): void
    {
        if ($image->isDirty('url')) {
            $this->processImage($image);
        }
    }

    private function processImage(ProductImage $image): void
    {
        $path = $image->url;

        // Skip if URL is external (Unsplash etc.) or already WebP
        if (str_starts_with($path, 'http') || str_ends_with($path, '.webp')) {
            return;
        }

        $fullPath = Storage::disk('public')->path($path);

        if (!file_exists($fullPath)) return;

        try {
            $img = Image::read($fullPath);

            // Resize to max 1200px on longest side, keep aspect ratio
            $img->scaleDown(1200, 1200);

            // Convert to WebP (quality 85)
            $webpPath  = preg_replace('/\.(jpe?g|png|gif|bmp|tiff?)$/i', '.webp', $path);
            $webpFull  = Storage::disk('public')->path($webpPath);

            // Ensure directory exists
            @mkdir(dirname($webpFull), 0755, true);

            $img->toWebp(85)->save($webpFull);

            // Delete original if different from webp path
            if ($path !== $webpPath && file_exists($fullPath)) {
                @unlink($fullPath);
            }

            // Update the record URL without triggering the observer again
            ProductImage::withoutEvents(fn() =>
                $image->updateQuietly(['url' => $webpPath])
            );

        } catch (\Throwable $e) {
            // Log but don't crash — keep original file
            \Log::error('ProductImage WebP conversion failed: ' . $e->getMessage());
        }
    }
}
