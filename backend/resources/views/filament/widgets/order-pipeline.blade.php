@php
$d = $this->getData();
$stages = [
    ['key'=>'pending',    'label'=>'Pending',    'icon'=>'⏳', 'color'=>'#d97706', 'light'=>'#fef3c7'],
    ['key'=>'confirmed',  'label'=>'Confirmed',  'icon'=>'✅', 'color'=>'#2563eb', 'light'=>'#dbeafe'],
    ['key'=>'processing', 'label'=>'Processing', 'icon'=>'🔨', 'color'=>'#7c3aed', 'light'=>'#ede9fe'],
    ['key'=>'shipped',    'label'=>'Shipped',    'icon'=>'🚚', 'color'=>'#059669', 'light'=>'#d1fae5'],
    ['key'=>'delivered',  'label'=>'Delivered',  'icon'=>'🎉', 'color'=>'#16a34a', 'light'=>'#dcfce7'],
];
@endphp

<div style="border-radius:12px;border:1px solid #e5e7eb;background:#fff;overflow:hidden;height:100%">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #f3f4f6">
        <div style="display:flex;align-items:center;gap:8px">
            <span style="font-size:16px">🛒</span>
            <span style="font-weight:600;font-size:14px;color:#111827">Order Pipeline</span>
            @if($d['total'] > 0)
            <span style="padding:2px 8px;font-size:10px;font-weight:700;border-radius:99px;background:#fef3c7;color:#92400e">{{ $d['total'] }} active</span>
            @endif
        </div>
        <a href="/admin/orders" style="font-size:12px;color:#f59e0b;text-decoration:none;font-weight:500">View all →</a>
    </div>

    {{-- Today Revenue --}}
    <div style="padding:12px 20px;background:linear-gradient(to right,#fffbeb,#fff7ed);border-bottom:1px solid #fde68a;display:flex;align-items:baseline;justify-content:space-between">
        <span style="font-size:11px;color:#b45309;font-weight:500">Today's Revenue</span>
        <span style="font-size:20px;font-weight:800;color:#d97706;font-variant-numeric:tabular-nums">OMR {{ number_format($d['todayRevenue'],3) }}</span>
    </div>

    {{-- Pipeline Stages --}}
    <div style="padding:16px 16px 8px;display:grid;grid-template-columns:repeat(5,1fr);gap:6px">
        @foreach($stages as $stage)
        @php $count = $d['pipeline'][$stage['key']] ?? 0; @endphp
        <a href="/admin/orders?status={{ $stage['key'] }}" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:10px 4px;border-radius:10px;border:2px solid {{ $count>0 ? $stage['color'] : '#f3f4f6' }};background:{{ $count>0 ? $stage['light'] : '#f9fafb' }};text-decoration:none;transition:transform 0.1s" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
            <span style="font-size:18px;line-height:1">{{ $stage['icon'] }}</span>
            <span style="font-size:22px;font-weight:900;line-height:1;color:{{ $count>0 ? $stage['color'] : '#9ca3af' }};font-variant-numeric:tabular-nums">{{ $count }}</span>
            <span style="font-size:9px;font-weight:500;color:#6b7280;text-align:center;line-height:1.2">{{ $stage['label'] }}</span>
        </a>
        @endforeach
    </div>

    {{-- Progress bar --}}
    @if($d['total'] > 0)
    <div style="padding:0 16px 8px">
        <div style="height:6px;background:#f3f4f6;border-radius:99px;overflow:hidden;display:flex">
            @foreach($stages as $stage)
            @php $count = $d['pipeline'][$stage['key']] ?? 0; $w = $d['total']>0 ? ($count/$d['total'])*100 : 0; @endphp
            @if($w > 0)<div style="height:100%;width:{{ $w }}%;background:{{ $stage['color'] }}"></div>@endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- Footer --}}
    <div style="padding:10px 20px;border-top:1px solid #f3f4f6;display:flex;justify-content:space-between">
        <div>
            <div style="font-size:10px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px">Active Orders</div>
            <div style="font-size:14px;font-weight:700;color:#111827">{{ $d['total'] }}</div>
        </div>
        @if(($d['pipeline']['cancelled'] ?? 0) > 0)
        <div style="text-align:right">
            <div style="font-size:10px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px">Cancelled</div>
            <div style="font-size:14px;font-weight:700;color:#ef4444">{{ $d['pipeline']['cancelled'] }}</div>
        </div>
        @endif
    </div>

</div>
