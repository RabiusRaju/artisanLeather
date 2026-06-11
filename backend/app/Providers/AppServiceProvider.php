<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CustomOrder;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Post;
use App\Models\ProductImage;
use App\Models\Setting;
use App\Models\SurveyQuestion;
use App\Observers\ProductImageObserver;
use App\Support\WebpFieldsRegistrar;
use Filament\Support\Facades\FilamentTimezone;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        ProductImage::observe(ProductImageObserver::class);

        WebpFieldsRegistrar::register(Category::class, ['image']);
        WebpFieldsRegistrar::register(Brand::class, ['logo', 'banner']);
        WebpFieldsRegistrar::register(Post::class, ['featured_image']);
        WebpFieldsRegistrar::register(CustomOrder::class, ['reference_images']);
        WebpFieldsRegistrar::register(Expense::class, ['receipt_image']);
        WebpFieldsRegistrar::register(Employee::class, ['photo']);
        WebpFieldsRegistrar::register(SurveyQuestion::class, ['image_path']);

        FilamentTimezone::set(fn () => Setting::get('business.timezone', 'Asia/Muscat'));
    }
}
