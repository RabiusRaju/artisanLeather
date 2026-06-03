<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class TrackOrderController extends Controller
{
    public function show(string $orderNumber)
    {
        $order = Order::with('items')
            ->where('order_number', strtoupper(trim($orderNumber)))
            ->first();

        if (!$order) {
            return response()->json(['found' => false, 'message' => 'Order not found. Please check the order number.'], 404);
        }

        $steps = [
            ['label' => 'Order Placed',    'label_ar' => 'تم الطلب',          'done' => true],
            ['label' => 'Confirmed',        'label_ar' => 'مؤكد',              'done' => in_array($order->status, ['confirmed','processing','shipped','delivered'])],
            ['label' => 'Processing',       'label_ar' => 'قيد التجهيز',       'done' => in_array($order->status, ['processing','shipped','delivered'])],
            ['label' => 'Shipped',          'label_ar' => 'تم الشحن',          'done' => in_array($order->status, ['shipped','delivered'])],
            ['label' => 'Delivered',        'label_ar' => 'تم التسليم',        'done' => $order->status === 'delivered'],
        ];

        return response()->json([
            'found'        => true,
            'order_number' => $order->order_number,
            'status'       => $order->status,
            'customer_name'=> $order->first_name . ' ' . $order->last_name,
            'city'         => $order->city,
            'governorate'  => $order->governorate,
            'total_omr'    => $order->total_omr,
            'payment_method' => $order->payment_method,
            'items_count'  => $order->items->sum('quantity'),
            'items'        => $order->items->map(fn($i) => [
                'name'     => $i->product_name,
                'qty'      => $i->quantity,
                'color'    => $i->color_name,
            ]),
            'created_at'   => $order->created_at->format('d M Y'),
            'steps'        => $steps,
        ]);
    }
}
