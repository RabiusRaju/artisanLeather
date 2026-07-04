<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\ProductShareLink;
use Illuminate\Http\Request;

class ProductShareLinkController extends Controller
{
    // GET /api/v1/share/{token}
    public function show(Request $request, string $token)
    {
        $locale = str_starts_with($request->header('Accept-Language', 'en'), 'ar') ? 'ar' : 'en';
        app()->setLocale($locale);

        $link = ProductShareLink::where('token', $token)->first();

        if (! $link || $link->isExpired()) {
            return response()->json(['message' => 'This link is invalid or has expired.'], 404);
        }

        return response()->json([
            'data' => [
                'name'     => $locale === 'ar' && $link->name_ar ? $link->name_ar : $link->name,
                'name_en'  => $link->name,
                'name_ar'  => $link->name_ar,
                'products' => ProductResource::collection($link->products()),
            ],
        ]);
    }
}
