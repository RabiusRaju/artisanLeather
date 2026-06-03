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
                'image' => $c->image,
            ]);

        return response()->json(['data' => $categories]);
    }
}
