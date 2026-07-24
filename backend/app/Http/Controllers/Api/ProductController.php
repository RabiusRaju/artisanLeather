<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $locale = str_starts_with($request->header('Accept-Language', 'en'), 'ar') ? 'ar' : 'en';
        app()->setLocale($locale);

        $query = Product::with(['category', 'brand', 'images', 'colors', 'details', 'specifications', 'faqs', 'stock'])
            ->where('is_active', true);

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        if ($request->filled('brand')) {
            $query->whereHas('brand', fn($q) => $q->where('slug', $request->brand));
        }

        match ($request->get('sort', 'default')) {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc'   => $query->orderBy('name', 'asc'),
            default      => $query->orderBy('sort_order')->orderBy('id'),
        };

        return ProductResource::collection($query->get());
    }

    public function show(Request $request, string $identifier)
    {
        $locale = str_starts_with($request->header('Accept-Language', 'en'), 'ar') ? 'ar' : 'en';
        app()->setLocale($locale);

        $query = Product::with(['category', 'brand', 'images', 'colors', 'details', 'specifications', 'faqs', 'stock', 'approvedReviews.user'])
            ->where('is_active', true);

        is_numeric($identifier)
            ? $query->where('id', $identifier)
            : $query->where('slug', $identifier);

        return new ProductResource($query->firstOrFail());
    }
}
