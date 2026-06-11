<?php

namespace App\Observers;

use App\Models\ProductImage;
use App\Support\WebpConverter;

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
        $webpPath = WebpConverter::convert($image->url, 'public', 1200);

        if ($webpPath !== $image->url) {
            ProductImage::withoutEvents(fn() =>
                $image->updateQuietly(['url' => $webpPath])
            );
        }
    }
}
