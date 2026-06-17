<?php

namespace App\Observers;

use App\Models\ProductImage;
use App\Support\WebpConverter;

class ProductImageObserver
{
    public function created(ProductImage $image): void
    {
        $id = $image->id;
        app()->terminating(fn() => $this->deferredProcess($id));
    }

    public function updated(ProductImage $image): void
    {
        if ($image->isDirty('url')) {
            $id = $image->id;
            app()->terminating(fn() => $this->deferredProcess($id));
        }
    }

    private function deferredProcess(int $id): void
    {
        $image = ProductImage::find($id);
        if (!$image) return;

        set_time_limit(120);
        ini_set('memory_limit', '512M');

        $webpPath = WebpConverter::convert($image->url, 'public', 1200);

        if ($webpPath !== $image->url) {
            ProductImage::withoutEvents(fn() =>
                $image->updateQuietly(['url' => $webpPath])
            );
        }
    }
}
