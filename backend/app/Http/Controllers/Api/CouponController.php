<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function validateCode(Request $request)
    {
        $data = $request->validate([
            'code'     => ['required', 'string', 'max:50'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $coupon = Coupon::where('code', strtoupper(trim($data['code'])))
            ->where('is_active', true)
            ->first();

        if (! $coupon || $coupon->isExpired()) {
            return response()->json(['message' => 'Invalid or expired coupon code.'], 404);
        }

        $discount = $coupon->calculateDiscount((float) $data['subtotal']);

        return response()->json([
            'data' => [
                'code'             => $coupon->code,
                'type'             => $coupon->type,
                'value'            => (float) $coupon->value,
                'discount_amount'  => $discount,
            ],
        ]);
    }

    // GET /coupons/featured — the single coupon flagged to show as a site-wide popup.
    public function featured()
    {
        $coupon = Coupon::where('is_active', true)
            ->where('show_as_popup', true)
            ->first();

        if (! $coupon || $coupon->isExpired()) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => [
                'code'        => $coupon->code,
                'type'        => $coupon->type,
                'value'       => (float) $coupon->value,
                'title'       => $coupon->popup_title,
                'description' => $coupon->description,
                'image'       => $coupon->popup_image ? asset('storage/' . $coupon->popup_image) : null,
                'expires_at'  => $coupon->expires_at?->toIso8601String(),
            ],
        ]);
    }
}
