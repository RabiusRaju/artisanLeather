<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\ProductShareLink;

class ProductShareLinkController extends Controller
{
    // GET /api/v1/share/{token}
    public function show(string $token)
    {
        $link = ProductShareLink::where('token', $token)->first();

        if (! $link || $link->isExpired()) {
            return response()->json(['message' => 'This link is invalid or has expired.'], 404);
        }

        return response()->json([
            'data' => [
                'name'     => $link->name,
                'products' => ProductResource::collection($link->products()),
            ],
        ]);
    }
}
