<?php

namespace App\Providers;

use App\Models\ProductImage;
use App\Models\Setting;
use App\Observers\ProductImageObserver;
use Filament\Support\Facades\FilamentTimezone;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        ProductImage::observe(ProductImageObserver::class);

        FilamentTimezone::set(fn () => Setting::get('business.timezone', 'Asia/Muscat'));
    }
}
