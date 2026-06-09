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
            'author'   => $t->author,
            'location' => $t->location,
            'product'  => $t->product,
            'rating'   => $t->rating,
        ]);

        return response()->json(['data' => $testimonials]);
    }
}
