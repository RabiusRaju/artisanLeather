@php
$d = $this->getData();
$outCount = $d['out']->count();
$lowCount = $d['low']->count();
$okCount  = $d['ok'];
$total    = $d['total'];
@endphp

<div style="border-radius:12px;border:1px solid {{ $outCount>0 ? '#fecaca' : ($lowCount>0 ? '#fde68a' : '#bbf7d0') }};background:#fff;overflow:hidden;height:100%">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #f3f4f6">
        <div style="display:flex;align-items:center;gap:8px">
            <span style="font-size:16px">📦</span>
            <span style="font-weight:600;font-size:14px;color:#111827">Stock Status</span>
        </div>
        <a href="/admin/operations/inventory/inventories" style="font-size:12px;color:#f59e0b;text-decoration:none;font-weight:500">Manage →</a>
    </div>

    {{-- 3 Summary Tiles --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);border-bottom:1px solid #f3f4f6">
        <div style="display:flex;flex-direction:column;align-items:center;padding:12px 8px;background:#f0fdf4">
            <span style="font-size:22px;font-weight:900;color:#16a34a;font-variant-numeric:tabular-nums">{{ $okCount }}</span>
            <span style="font-size:10px;font-weight:500;color:#15803d;margin-top:2px">In Stock</span>
        </div>
        <div style="display:flex;flex-direction:column;align-items:center;padding:12px 8px;background:{{ $lowCount>0 ? '#fefce8' : '#f9fafb' }};border-left:1px solid #f3f4f6;border-right:1px solid #f3f4f6">
            <span style="font-size:22px;font-weight:900;color:{{ $lowCount>0 ? '#ca8a04' : '#d1d5db' }};font-variant-numeric:tabular-nums">{{ $lowCount }}</span>
            <span style="font-size:10px;font-weight:500;color:{{ $lowCount>0 ? '#a16207' : '#9ca3af' }};margin-top:2px">Low Stock</span>
        </div>
        <div style="display:flex;flex-direction:column;align-items:center;padding:12px 8px;background:{{ $outCount>0 ? '#fef2f2' : '#f9fafb' }}">
            <span style="font-size:22px;font-weight:900;color:{{ $outCount>0 ? '#dc2626' : '#d1d5db' }};font-variant-numeric:tabular-nums">{{ $outCount }}</span>
            <span style="font-size:10px;font-weight:500;color:{{ $outCount>0 ? '#b91c1c' : '#9ca3af' }};margin-top:2px">Out of Stock</span>
        </div>
    </div>

    {{-- Overall Progress Bar --}}
    @if($total > 0)
    <div style="padding:12px 20px 4px">
        <div style="display:flex;justify-content:space-between;margin-bottom:4px">
            <span style="font-size:10px;color:#9ca3af">{{ $total }} total products</span>
            <span style="font-size:10px;color:#9ca3af">{{ $total>0 ? round(($okCount/$total)*100) : 0 }}% healthy</span>
        </div>
        <div style="height:8px;background:#f3f4f6;border-radius:99px;overflow:hidden;display:flex">
            @if($okCount>0)<div style="height:100%;width:{{ ($okCount/$total)*100 }}%;background:#4ade80"></div>@endif
            @if($lowCount>0)<div style="height:100%;width:{{ ($lowCount/$total)*100 }}%;background:#facc15"></div>@endif
            @if($outCount>0)<div style="height:100%;width:{{ ($outCount/$total)*100 }}%;background:#f87171"></div>@endif
        </div>
    </div>
    @endif

    {{-- Product List --}}
    <div style="padding:8px 20px;max-height:200px;overflow-y:auto">

        @foreach($d['out'] as $s)
        <div style="margin-bottom:10px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                <span style="font-size:12px;font-weight:500;color:#111827;max-width:60%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $s->product?->name }}</span>
                <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;background:#fee2e2;color:#b91c1c">OUT OF STOCK</span>
            </div>
            <div style="height:6px;background:#f3f4f6;border-radius:99px">
                <div style="height:100%;width:0%;background:#f87171;border-radius:99px"></div>
            </div>
            <div style="font-size:10px;color:#9ca3af;margin-top:2px">Reorder: {{ $s->reorder_qty ?? 10 }} units needed</div>
        </div>
        @endforeach

        @foreach($d['low'] as $s)
        @php
            $max = max($s->minimum_alert * 3, 15);
            $pct = min(100, ($s->quantity / $max) * 100);
        @endphp
        <div style="margin-bottom:10px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                <span style="font-size:12px;font-weight:500;color:#111827;max-width:60%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $s->product?->name }}</span>
                <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;background:#fef9c3;color:#854d0e">{{ $s->quantity }} left</span>
            </div>
            <div style="height:6px;background:#f3f4f6;border-radius:99px;overflow:hidden">
                <div style="height:100%;width:{{ $pct }}%;background:#facc15;border-radius:99px"></div>
            </div>
            <div style="font-size:10px;color:#9ca3af;margin-top:2px">Min alert: {{ $s->minimum_alert }}</div>
        </div>
        @endforeach

        @if($outCount === 0 && $lowCount === 0)
        <div style="display:flex;flex-direction:column;align-items:center;padding:24px 0;color:#9ca3af">
            <span style="font-size:24px;margin-bottom:6px">✅</span>
            <span style="font-size:12px">All products well stocked</span>
        </div>
        @endif
    </div>

</div>
