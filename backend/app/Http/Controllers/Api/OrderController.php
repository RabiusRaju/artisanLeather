<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmed;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
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
            'items.*.color_name'   => 'nullable|string',
            'items.*.color_hex'    => 'nullable|string',
            'items.*.quantity'     => 'required|integer|min:1|max:999',
            // unit_price is intentionally NOT validated from client — we use DB price below
        ]);

        return DB::transaction(function () use ($validated) {
            // C-1 FIX: Load prices from DB — never trust client-submitted prices
            $products = Product::whereIn('id', collect($validated['items'])->pluck('product_id'))
                ->get()->keyBy('id');

            // Validate all products exist and have a price
            foreach ($validated['items'] as $item) {
                if (!$products->has($item['product_id'])) {
                    abort(422, 'Product not found: ' . $item['product_id']);
                }
            }

            // C-6 FIX: Check stock before creating order
            foreach ($validated['items'] as $item) {
                $stock = ProductStock::where('product_id', $item['product_id'])->first();
                if ($stock && $stock->quantity < $item['quantity']) {
                    abort(422, 'Insufficient stock for: ' . $products[$item['product_id']]->name);
                }
            }

            // Calculate subtotal using DB prices
            $subtotal = collect($validated['items'])->sum(function ($item) use ($products) {
                return (float) $products[$item['product_id']]->price * $item['quantity'];
            });

            // Generate unique order number (retry on collision — C-5 partial fix)
            do {
                $orderNumber = Order::generateOrderNumber();
            } while (Order::where('order_number', $orderNumber)->exists());

            $order = Order::create([
                'order_number'  => $orderNumber,
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
                'subtotal_omr'  => round($subtotal, 3),
                'total_omr'     => round($subtotal, 3),
                'status'        => 'pending',
            ]);

            foreach ($validated['items'] as $item) {
                $product  = $products[$item['product_id']];
                $unitPrice = (float) $product->price; // DB price — not from client

                OrderItem::create([
                    'order_id'        => $order->id,
                    'product_id'      => $item['product_id'],
                    'product_name'    => $product->name,
                    'product_name_ar' => $product->name_ar,
                    'color_name'      => $item['color_name'] ?? null,
                    'color_hex'       => $item['color_hex'] ?? null,
                    'quantity'        => $item['quantity'],
                    'unit_price_omr'  => $unitPrice,
                    'total_price_omr' => round($unitPrice * $item['quantity'], 3),
                ]);

                // C-6 FIX: Decrement stock and record movement
                $stock = ProductStock::where('product_id', $item['product_id'])->first();
                if ($stock) {
                    $before = $stock->quantity;
                    $stock->decrement('quantity', $item['quantity']);
                    StockMovement::create([
                        'product_id'     => $item['product_id'],
                        'type'           => 'stock_out',
                        'quantity'       => $item['quantity'],
                        'quantity_after' => $before - $item['quantity'],
                        'reference'      => $order->order_number,
                        'reason'         => 'Customer order ' . $order->order_number,
                    ]);
                }
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
