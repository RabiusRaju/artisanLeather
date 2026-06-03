<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $order->order_number }} — Artisan Leather</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            color: #1a1208;
            background: #fff;
            font-size: 13px;
            line-height: 1.6;
        }

        .invoice-wrapper {
            max-width: 820px;
            margin: 0 auto;
            padding: 48px 48px 64px;
        }

        /* ── Header ── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 32px;
            border-bottom: 3px solid #C9A84C;
            margin-bottom: 36px;
        }
        .brand-name {
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #1a1208;
        }
        .brand-tagline {
            font-size: 11px;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: #9B7B3D;
            margin-top: 4px;
        }
        .brand-contact {
            font-size: 11px;
            color: #6b5c3a;
            margin-top: 8px;
            line-height: 1.8;
        }
        .invoice-label {
            text-align: right;
        }
        .invoice-label h1 {
            font-size: 36px;
            font-weight: normal;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #C9A84C;
        }
        .invoice-label .order-number {
            font-size: 13px;
            color: #6b5c3a;
            margin-top: 4px;
            letter-spacing: 0.05em;
        }

        /* ── Meta grid ── */
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            margin-bottom: 36px;
        }
        .meta-block h3 {
            font-size: 10px;
            letter-spacing: 0.35em;
            text-transform: uppercase;
            color: #C9A84C;
            font-weight: normal;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e8d5a0;
        }
        .meta-block p {
            font-size: 13px;
            color: #1a1208;
            line-height: 1.8;
        }
        .meta-block .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: #9B7B3D;
            display: inline-block;
            width: 85px;
        }

        /* ── Status badge ── */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border: 1px solid #C9A84C;
            font-size: 10px;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: #9B7B3D;
        }

        /* ── Items table ── */
        .items-section {
            margin-bottom: 32px;
        }
        .section-title {
            font-size: 10px;
            letter-spacing: 0.35em;
            text-transform: uppercase;
            color: #C9A84C;
            font-weight: normal;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e8d5a0;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
        }
        table.items thead tr {
            border-bottom: 2px solid #C9A84C;
        }
        table.items th {
            font-size: 10px;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: #9B7B3D;
            font-weight: normal;
            padding: 8px 10px;
            text-align: left;
        }
        table.items th:last-child,
        table.items th:nth-last-child(2),
        table.items th:nth-last-child(3) { text-align: right; }
        table.items td {
            padding: 14px 10px;
            vertical-align: middle;
            border-bottom: 1px solid #f0e8d4;
            font-size: 13px;
        }
        table.items td:last-child,
        table.items td:nth-last-child(2),
        table.items td:nth-last-child(3) { text-align: right; }
        .product-name { font-weight: bold; color: #1a1208; }
        .product-name-ar { font-size: 11px; color: #9B7B3D; }
        .color-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: 1px solid rgba(0,0,0,0.15);
            margin-right: 5px;
            vertical-align: middle;
        }

        /* ── Totals ── */
        .totals-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 40px;
        }
        .totals-table {
            width: 300px;
        }
        .totals-table tr td {
            padding: 6px 0;
            font-size: 13px;
        }
        .totals-table tr td:last-child { text-align: right; }
        .totals-table .total-row {
            border-top: 2px solid #C9A84C;
            font-size: 16px;
            font-weight: bold;
        }
        .totals-table .total-row td { padding-top: 12px; color: #1a1208; }
        .total-amount { color: #C9A84C; font-size: 18px; }
        .free-shipping { color: #059669; font-weight: 500; }

        /* ── Payment info ── */
        .payment-box {
            border: 1px solid #e8d5a0;
            padding: 16px 20px;
            margin-bottom: 36px;
            background: #faf7f0;
        }
        .payment-box h3 {
            font-size: 10px;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: #C9A84C;
            font-weight: normal;
            margin-bottom: 8px;
        }

        /* ── Footer ── */
        .footer {
            border-top: 1px solid #e8d5a0;
            padding-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .footer-left { font-size: 12px; color: #9B7B3D; line-height: 1.8; }
        .footer-right {
            text-align: right;
            font-size: 11px;
            color: #9B7B3D;
        }
        .footer-brand {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #C9A84C;
        }
        .thank-you {
            font-size: 14px;
            color: #1a1208;
            font-style: italic;
            margin-bottom: 4px;
        }

        /* ── Print actions (screen only) ── */
        .print-actions {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 100;
        }
        .btn {
            padding: 10px 20px;
            font-size: 12px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            cursor: pointer;
            border: none;
            font-family: inherit;
        }
        .btn-print {
            background: #C9A84C;
            color: #fff;
        }
        .btn-close {
            background: #f3f4f6;
            color: #374151;
        }

        @media print {
            .print-actions { display: none !important; }
            .invoice-wrapper { padding: 24px 32px; }
            body { font-size: 12px; }
            @page { margin: 15mm; }
        }
    </style>
</head>
<body>

    <!-- Print / Close actions (hidden on print) -->
    <div class="print-actions">
        <button class="btn btn-close" onclick="window.close()">✕ Close</button>
        <button class="btn btn-print" onclick="window.print()">🖨 Print / Save PDF</button>
    </div>

    <div class="invoice-wrapper">

        <!-- ── Header ── -->
        <div class="header">
            <div>
                <div class="brand-name">Artisan Leather</div>
                <div class="brand-tagline">Muscat · Sultanate of Oman</div>
                <div class="brand-contact">
                    artisanleatherom.com<br>
                    +968 1234 5678<br>
                    orders@artisanleatherom.com
                </div>
            </div>
            <div class="invoice-label">
                <h1>Invoice</h1>
                <div class="order-number">{{ $order->order_number }}</div>
                <div style="margin-top:8px;font-size:12px;color:#6b5c3a;">
                    {{ $order->created_at->format('d F Y') }}
                </div>
                <div style="margin-top:8px;">
                    <span class="status-badge">{{ ucfirst($order->status) }}</span>
                </div>
            </div>
        </div>

        <!-- ── Bill To + Order Details ── -->
        <div class="meta-grid">
            <div class="meta-block">
                <h3>Bill To</h3>
                <p>
                    <strong>{{ $order->first_name }} {{ $order->last_name }}</strong><br>
                    {{ $order->phone }}<br>
                    @if($order->email){{ $order->email }}<br>@endif
                    {{ $order->address }},<br>
                    {{ $order->city }}, {{ $order->governorate }}, Oman
                </p>
                @if($order->notes)
                    <p style="margin-top:10px;font-style:italic;color:#9B7B3D;font-size:12px;">
                        Note: {{ $order->notes }}
                    </p>
                @endif
            </div>
            <div class="meta-block">
                <h3>Order Information</h3>
                <p>
                    <span class="label">Invoice</span> {{ $order->order_number }}<br>
                    <span class="label">Date</span> {{ $order->created_at->format('d M Y') }}<br>
                    <span class="label">Payment</span>
                    @php
                        $paymentLabels = ['cod' => 'Cash on Delivery', 'bank' => 'Bank Transfer', 'whatsapp' => 'WhatsApp Order'];
                    @endphp
                    {{ $paymentLabels[$order->payment_method] ?? $order->payment_method }}<br>
                    <span class="label">Currency</span> {{ $order->currency_code }}<br>
                    @if($order->payment_method === 'bank')
                        <span class="label">Bank</span> Bank Muscat<br>
                        <span class="label">Account</span> 0123-4567890-001
                    @endif
                </p>
            </div>
        </div>

        <!-- ── Items ── -->
        <div class="items-section">
            <div class="section-title">Items Ordered</div>
            <table class="items">
                <thead>
                    <tr>
                        <th style="width:40%">Product</th>
                        <th>Colour</th>
                        <th style="text-align:center">Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>
                            <div class="product-name">{{ $item->product_name }}</div>
                            @if($item->product_name_ar)
                                <div class="product-name-ar">{{ $item->product_name_ar }}</div>
                            @endif
                        </td>
                        <td>
                            @if($item->color_hex)
                                <span class="color-dot" style="background:{{ $item->color_hex }}"></span>
                            @endif
                            {{ $item->color_name ?? '—' }}
                        </td>
                        <td style="text-align:center">{{ $item->quantity }}</td>
                        <td>OMR {{ number_format($item->unit_price_omr, 3) }}</td>
                        <td><strong>OMR {{ number_format($item->total_price_omr, 3) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- ── Totals ── -->
        <div class="totals-wrapper">
            <table class="totals-table">
                <tr>
                    <td style="color:#6b5c3a;">Subtotal</td>
                    <td>OMR {{ number_format($order->subtotal_omr, 3) }}</td>
                </tr>
                <tr>
                    <td style="color:#6b5c3a;">Shipping</td>
                    <td class="free-shipping">FREE</td>
                </tr>
                <tr class="total-row">
                    <td>Total</td>
                    <td class="total-amount">OMR {{ number_format($order->total_omr, 3) }}</td>
                </tr>
            </table>
        </div>

        <!-- ── Payment note for bank transfer ── -->
        @if($order->payment_method === 'bank')
        <div class="payment-box">
            <h3>Bank Transfer Details</h3>
            <p style="font-size:12px;line-height:1.8;">
                Please transfer <strong>OMR {{ number_format($order->total_omr, 3) }}</strong> to:<br>
                Bank: Bank Muscat &nbsp;·&nbsp; Account Name: Artisan Leather LLC<br>
                Account No: 0123-4567890-001 &nbsp;·&nbsp; IBAN: OM12 1234 0000 0123 4567 890<br>
                Reference: <strong>{{ $order->order_number }}</strong> or your phone number
            </p>
        </div>
        @endif

        <!-- ── Footer ── -->
        <div class="footer">
            <div class="footer-left">
                <div class="thank-you">Thank you for choosing Artisan Leather.</div>
                <span style="font-size:11px;">Crafted with passion · Built to last</span><br>
                <span style="font-size:11px;color:#c5a855;">14-day return policy · Free GCC delivery</span>
            </div>
            <div class="footer-right">
                <div class="footer-brand">Artisan Leather</div>
                artisanleatherom.com<br>
                +968 1234 5678<br>
                orders@artisanleatherom.com
            </div>
        </div>

    </div>
</body>
</html>
