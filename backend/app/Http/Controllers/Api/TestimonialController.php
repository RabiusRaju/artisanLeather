<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        $isAr = str_starts_with($lang, 'ar');

        $testimonials = Testimonial::active()->ordered()->get()->map(fn($t) => [
            'id'       => $t->id,
            'quote'    => ($isAr && $t->quote_ar) ? $t->quote_ar : $t->quote,
            'quote_en' => $t->quote,
            'quote_ar' => $t->quote_ar,
            'author'   => ($isAr && $t->author_ar) ? $t->author_ar : $t->author,
            'author_en' => $t->author,
            'author_ar' => $t->author_ar,
            'location' => ($isAr && $t->location_ar) ? $t->location_ar : $t->location,
            'location_en' => $t->location,
            'location_ar' => $t->location_ar,
            'product'  => ($isAr && $t->product_ar) ? $t->product_ar : $t->product,
            'product_en' => $t->product,
            'product_ar' => $t->product_ar,
            'rating'   => $t->rating,
        ]);

        return response()->json(['data' => $testimonials]);
    }
}
