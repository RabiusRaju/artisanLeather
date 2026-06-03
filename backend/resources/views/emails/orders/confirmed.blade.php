<x-mail::message>
# Thank You, {{ $order->first_name }}!

Your order **{{ $order->order_number }}** has been received and is being prepared.

---

## Order Summary

| Product | Qty | Price (OMR) |
|---------|-----|------------|
@foreach ($order->items as $item)
| {{ $item->product_name }} ({{ $item->color_name }}) | {{ $item->quantity }} | {{ number_format($item->total_price_omr, 3) }} |
@endforeach

**Total: OMR {{ number_format($order->total_omr, 3) }}**

**Payment:** {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}

---

## Delivery To

{{ $order->first_name }} {{ $order->last_name }}  
{{ $order->address }}, {{ $order->city }}, {{ $order->governorate }}, Oman  
{{ $order->phone }}

---

**Estimated Delivery:** 3–5 business days

@if($order->payment_method === 'bank')
> **Bank Transfer Reminder:** Please transfer OMR {{ number_format($order->total_omr, 3) }} to complete your order.
@endif

<x-mail::button :url="'https://wa.me/96812345678?text=Hello, I placed order ' . $order->order_number">
Track on WhatsApp
</x-mail::button>

Thank you for choosing Artisan Leather.  
*Crafted with passion. Built to last.*

© {{ date('Y') }} Artisan Leather · artisanleatherom.com
</x-mail::message>
