<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\TrackOrderController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:120,1')->group(function () {
    // Public — 120 requests/minute per IP
    Route::get('/products',          [ProductController::class, 'index']);
    Route::get('/products/{slug}',   [ProductController::class, 'show']);
    Route::get('/categories',        [CategoryController::class, 'index']);
    Route::get('/currencies',        [CurrencyController::class, 'index']);
    Route::get('/brands',            [BrandController::class, 'index']);
    Route::get('/posts',             [PostController::class, 'index']);
    Route::get('/posts/{slug}',      [PostController::class, 'show']);
    Route::post('/surveys/staff-unlock', [SurveyController::class, 'verifyStaffPin']);
    Route::get('/surveys/{slug}',     [SurveyController::class, 'show']);
    Route::post('/surveys/{slug}/respond', [SurveyController::class, 'respond']);
    Route::get('/settings',              [SettingsController::class, 'index']);
    Route::get('/testimonials',          [TestimonialController::class, 'index']);
    Route::get('/faqs',                  [FaqController::class, 'index']);
    Route::get('/track/{orderNumber}', [TrackOrderController::class, 'show'])->middleware('throttle:20,1');
    Route::post('/orders',           [OrderController::class, 'store'])->middleware('throttle:10,1');
    Route::post('/contact',          [ContactController::class, 'store'])->middleware('throttle:5,1');
    Route::post('/coupons/validate', [CouponController::class, 'validateCode'])->middleware('throttle:20,1');
    Route::get('/products/{product}/reviews', [ReviewController::class, 'index']);

    // Auth
    Route::post('/auth/register',    [AuthController::class, 'register']);
    Route::post('/auth/login',       [AuthController::class, 'login'])->middleware('throttle:5,1');

    // Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout',  [AuthController::class, 'logout']);
        Route::get('/auth/me',       [AuthController::class, 'me']);
        Route::get('/auth/orders',   [AuthController::class, 'orders']);
        Route::get('/auth/last-order', [AuthController::class, 'lastOrder']);

        Route::post('/products/{product}/reviews', [ReviewController::class, 'store']);

        Route::get('/wishlist',           [WishlistController::class, 'index']);
        Route::post('/wishlist/sync',     [WishlistController::class, 'sync']);
        Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->whereNumber('product');
    });
});
