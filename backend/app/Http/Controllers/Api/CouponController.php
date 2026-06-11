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

        if (! $coupon) {
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
}
