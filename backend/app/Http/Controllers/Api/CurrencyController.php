<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['code', 'symbol', 'name', 'name_ar', 'rate', 'decimals']);

        return response()->json(['data' => $currencies]);
    }
}
