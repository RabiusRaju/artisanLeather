<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\TrackOrderController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public
    Route::get('/products',          [ProductController::class, 'index']);
    Route::get('/products/{slug}',   [ProductController::class, 'show']);
    Route::get('/categories',        [CategoryController::class, 'index']);
    Route::get('/currencies',        [CurrencyController::class, 'index']);
    Route::get('/brands',            [BrandController::class, 'index']);
    Route::get('/track/{orderNumber}', [TrackOrderController::class, 'show']);
    Route::post('/orders',           [OrderController::class, 'store']);
    Route::post('/contact',          [ContactController::class, 'store']);

    // Auth
    Route::post('/auth/register',    [AuthController::class, 'register']);
    Route::post('/auth/login',       [AuthController::class, 'login']);

    // Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout',  [AuthController::class, 'logout']);
        Route::get('/auth/me',       [AuthController::class, 'me']);
        Route::get('/auth/orders',   [AuthController::class, 'orders']);
    });
});
