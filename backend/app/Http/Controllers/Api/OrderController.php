<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmed;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'email'          => 'required|email',
            'phone'          => 'required|string|max:20',
            'governorate'    => 'required|string|max:100',
            'city'           => 'required|string|max:100',
            'address'        => 'required|string|max:500',
            'notes'          => 'nullable|string|max:1000',
            'payment_method' => ['required', Rule::in(['cod', 'bank', 'whatsapp'])],
            'currency_code'  => 'required|string|size:3',
            'currency_rate'  => 'required|numeric|min:0.01',
            'items'          => 'required|array|min:1',
            'items.*.product_id'   => 'required|integer|exists:products,id',
            'items.*.product_name' => 'required|string',
            'items.*.color_name'   => 'nullable|string',
            'items.*.color_hex'    => 'nullable|string',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.unit_price'   => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated) {
            $subtotal = collect($validated['items'])
                ->sum(fn($item) => $item['unit_price'] * $item['quantity']);

            $order = Order::create([
                'order_number'  => Order::generateOrderNumber(),
                'first_name'    => $validated['first_name'],
                'last_name'     => $validated['last_name'],
                'email'         => $validated['email'],
                'phone'         => $validated['phone'],
                'governorate'   => $validated['governorate'],
                'city'          => $validated['city'],
                'address'       => $validated['address'],
                'notes'         => $validated['notes'] ?? null,
                'payment_method'=> $validated['payment_method'],
                'currency_code' => $validated['currency_code'],
                'currency_rate' => $validated['currency_rate'],
                'subtotal_omr'  => $subtotal,
                'total_omr'     => $subtotal,
                'status'        => 'pending',
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                OrderItem::create([
                    'order_id'        => $order->id,
                    'product_id'      => $item['product_id'],
                    'product_name'    => $item['product_name'],
                    'product_name_ar' => $product?->name_ar,
                    'color_name'      => $item['color_name'] ?? null,
                    'color_hex'       => $item['color_hex'] ?? null,
                    'quantity'        => $item['quantity'],
                    'unit_price_omr'  => $item['unit_price'],
                    'total_price_omr' => $item['unit_price'] * $item['quantity'],
                ]);
            }

            // Send confirmation email to customer (non-blocking)
            try {
                $order->load('items');
                Mail::to($order->email)->send(new OrderConfirmed($order));
                // Alert admin
                Mail::to(config('mail.from.address'))->send(new OrderConfirmed($order));
            } catch (\Throwable) {
                // Email failure should not fail the order
            }

            return response()->json([
                'success'      => true,
                'order_number' => $order->order_number,
                'message'      => 'Order placed successfully',
            ], 201);
        });
    }
}
