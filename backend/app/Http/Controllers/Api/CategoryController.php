<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->header('Accept-Language', 'en') === 'ar' ? 'ar' : 'en';

        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($c) => [
                'id'    => $c->id,
                'name'  => $locale === 'ar' && $c->name_ar ? $c->name_ar : $c->name,
                'name_en' => $c->name,
                'name_ar' => $c->name_ar,
                'slug'  => $c->slug,
                // M-3 FIX: Wrap local path with asset() so images actually work
                'image' => $c->image
                    ? (str_starts_with($c->image, 'http') ? $c->image : asset('storage/' . $c->image))
                    : null,
                'image_alt' => $c->image_alt ?: (($locale === 'ar' && $c->name_ar ? $c->name_ar : $c->name) . ' | Artisan Leather Oman'),
            ]);

        return response()->json(['data' => $categories]);
    }
}
