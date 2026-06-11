<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        $isAr = str_starts_with($lang, 'ar');

        $faqs = Faq::active()->ordered()->get()->map(fn($f) => [
            'id'       => $f->id,
            'question' => ($isAr && $f->question_ar) ? $f->question_ar : $f->question,
            'answer'   => ($isAr && $f->answer_ar) ? $f->answer_ar : $f->answer,
        ]);

        return response()->json(['data' => $faqs]);
    }
}
