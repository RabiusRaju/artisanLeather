<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $locale = str_starts_with($request->header('Accept-Language', 'en'), 'ar') ? 'ar' : 'en';
        app()->setLocale($locale);

        $productIds = Wishlist::where('user_id', $request->user()->id)->pluck('product_id');

        $products = Product::with(['category', 'brand', 'images', 'colors', 'details', 'stock'])
            ->whereIn('id', $productIds)
            ->get();

        return ProductResource::collection($products);
    }

    public function toggle(Request $request, int $product)
    {
        $product = Product::findOrFail($product);

        $existing = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json(['data' => ['in_wishlist' => false]]);
        }

        Wishlist::create([
            'user_id'    => $request->user()->id,
            'product_id' => $product->id,
        ]);

        return response()->json(['data' => ['in_wishlist' => true]]);
    }

    public function sync(Request $request)
    {
        $data = $request->validate([
            'product_ids'   => ['present', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $userId = $request->user()->id;
        $existingIds = Wishlist::where('user_id', $userId)->pluck('product_id')->all();

        $toAdd = array_diff($data['product_ids'], $existingIds);

        foreach ($toAdd as $productId) {
            Wishlist::create(['user_id' => $userId, 'product_id' => $productId]);
        }

        $allIds = Wishlist::where('user_id', $userId)->pluck('product_id');

        return response()->json(['data' => ['product_ids' => $allIds]]);
    }
}
