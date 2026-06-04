<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $locale = str_starts_with($request->header('Accept-Language', 'en'), 'ar') ? 'ar' : 'en';

        $brands = Brand::where('is_active', true)
            ->orderBy('sort_order')
            ->withCount('products')
            ->get()
            ->map(fn($b) => [
                'id'          => $b->id,
                'name'        => $locale === 'ar' && $b->name_ar ? $b->name_ar : $b->name,
                'name_en'     => $b->name,
                'name_ar'     => $b->name_ar,
                'slug'        => $b->slug,
                'tagline'     => $locale === 'ar' && $b->tagline_ar ? $b->tagline_ar : $b->tagline,
                'description' => $locale === 'ar' && $b->description_ar ? $b->description_ar : $b->description,
                // H-1 FIX: Handle both external URLs (seeders) and local storage paths
                'logo'        => $b->logo
                    ? (str_starts_with($b->logo, 'http') ? $b->logo : asset('storage/' . $b->logo))
                    : null,
                'banner'      => $b->banner
                    ? (str_starts_with($b->banner, 'http') ? $b->banner : asset('storage/' . $b->banner))
                    : null,
                'is_featured' => $b->is_featured,
                'products_count' => $b->products_count,
            ]);

        return response()->json(['data' => $brands]);
    }
}
