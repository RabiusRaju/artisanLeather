<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterSubscriberController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email'       => ['required', 'email:rfc', 'max:255'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'source'      => ['nullable', 'string', 'max:80'],
            'utm'         => ['nullable', 'array'],
        ]);

        $subscriber = NewsletterSubscriber::updateOrCreate(
            ['email' => strtolower(trim($data['email']))],
            [
                'coupon_code'   => $data['coupon_code'] ?? null,
                'source'        => $data['source'] ?? 'coupon_popup',
                'utm'           => $data['utm'] ?? null,
                'subscribed_at' => now(),
            ]
        );

        return response()->json([
            'data' => [
                'email' => $subscriber->email,
                'coupon_code' => $subscriber->coupon_code,
            ],
        ], 201);
    }
}
