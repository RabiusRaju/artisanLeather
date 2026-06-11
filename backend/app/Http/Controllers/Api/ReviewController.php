<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request, string $product)
    {
        $product = is_numeric($product)
            ? Product::findOrFail($product)
            : Product::where('slug', $product)->firstOrFail();

        $reviews = $product->approvedReviews()
            ->with('user:id,name')
            ->latest()
            ->paginate(10);

        return response()->json([
            'data' => $reviews->getCollection()->map(fn (Review $r) => [
                'id'         => $r->id,
                'rating'     => $r->rating,
                'title'      => $r->title,
                'comment'    => $r->comment,
                'user_name'  => $r->user->name,
                'created_at' => $r->created_at->toDateString(),
            ]),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
                'total'        => $reviews->total(),
            ],
            'summary' => [
                'average_rating' => $product->average_rating,
                'review_count'   => $product->review_count,
            ],
        ]);
    }

    public function store(Request $request, string $product)
    {
        $product = is_numeric($product)
            ? Product::findOrFail($product)
            : Product::where('slug', $product)->firstOrFail();

        $data = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'title'   => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $existing = Review::where('product_id', $product->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'You have already reviewed this product.'], 422);
        }

        $review = Review::create([
            'product_id'  => $product->id,
            'user_id'     => $request->user()->id,
            'rating'      => $data['rating'],
            'title'       => $data['title'] ?? null,
            'comment'     => $data['comment'] ?? null,
            'is_approved' => false,
        ]);

        return response()->json([
            'message' => 'Thank you! Your review has been submitted and is pending approval.',
            'data'    => [
                'id'         => $review->id,
                'rating'     => $review->rating,
                'title'      => $review->title,
                'comment'    => $review->comment,
                'is_approved' => $review->is_approved,
                'created_at' => $review->created_at->toDateString(),
            ],
        ], 201);
    }
}
