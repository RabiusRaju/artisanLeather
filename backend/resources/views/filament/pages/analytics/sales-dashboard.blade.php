<x-filament-panels::page>
<script src="/js/chart.umd.min.js"></script>
@php
$d = $this->getData();
$colPalette = ['#f59e0b','#8b5cf6','#10b981','#ef4444','#3b82f6','#ec4899'];
@endphp

{{-- ══════════════════════════════════════════════════════════
     ROW 1 — KPI Cards
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">

    {{-- Revenue This Month --}}
    <div style="border-radius:14px;border:1px solid #d1fae5;background:linear-gradient(135deg,#f0fdf4,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Revenue This Month</span>
            <span style="font-size:20px">💰</span>
        </div>
        <div style="font-size:26px;font-weight:900;color:#059669;font-variant-numeric:tabular-nums;line-height:1">OMR {{ number_format($d['revCM'],3) }}</div>
        <div style="margin-top:6px;font-size:11px;color:{{ $d['growth'] >= 0 ? '#16a34a' : '#dc2626' }}">
            {{ $d['growth'] >= 0 ? '▲' : '▼' }} {{ abs($d['growth']) }}% vs last month
        </div>
    </div>

    {{-- Orders This Month --}}
    <div style="border-radius:14px;border:1px solid #dbeafe;background:linear-gradient(135deg,#eff6ff,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Orders This Month</span>
            <span style="font-size:20px">📦</span>
        </div>
        <div style="font-size:26px;font-weight:900;color:#2563eb;font-variant-numeric:tabular-nums;line-height:1">{{ $d['ordCM'] }}</div>
        <div style="margin-top:6px;font-size:11px;color:#6b7280">Last month: {{ $d['ordLM'] }} orders</div>
    </div>

    {{-- Avg Order Value --}}
    <div style="border-radius:14px;border:1px solid #fde68a;background:linear-gradient(135deg,#fffbeb,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Avg Order Value</span>
            <span style="font-size:20px">📊</span>
        </div>
        <div style="font-size:26px;font-weight:900;color:#d97706;font-variant-numeric:tabular-nums;line-height:1">OMR {{ number_format($d['avgVal'],3) }}</div>
        <div style="margin-top:6px;font-size:11px;color:#6b7280">Per completed order</div>
    </div>

    {{-- Today's Revenue --}}
    <div style="border-radius:14px;border:1px solid #fecaca;background:linear-gradient(135deg,#fef2f2,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Today's Revenue</span>
            <span style="font-size:20px">🌅</span>
        </div>
        <div style="font-size:26px;font-weight:900;color:#dc2626;font-variant-numeric:tabular-nums;line-height:1">OMR {{ number_format($d['revToday'],3) }}</div>
        <div style="margin-top:6px;font-size:11px;color:#6b7280">{{ now()->format('d M Y') }}</div>
    </div>

    {{-- Last Month --}}
    <div style="border-radius:14px;border:1px solid #e9d5ff;background:linear-gradient(135deg,#faf5ff,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Revenue Last Month</span>
            <span style="font-size:20px">📅</span>
        </div>
        <div style="font-size:26px;font-weight:900;color:#7c3aed;font-variant-numeric:tabular-nums;line-height:1">OMR {{ number_format($d['revLM'],3) }}</div>
        <div style="margin-top:6px;font-size:11px;color:#6b7280">Previous full period</div>
    </div>

    {{-- New Customers --}}
    <div style="border-radius:14px;border:1px solid #bfdbfe;background:linear-gradient(135deg,#f0f9ff,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">New Customers</span>
            <span style="font-size:20px">👥</span>
        </div>
        <div style="font-size:26px;font-weight:900;color:#0284c7;font-variant-numeric:tabular-nums;line-height:1">{{ $d['newCustomers'] }}</div>
        <div style="margin-top:6px;font-size:11px;color:#6b7280">Registered this month</div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 2 — 30-Day Daily Revenue Bar Chart (full width)
══════════════════════════════════════════════════════════ --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:16px">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px">
        <div>
            <div style="font-size:14px;font-weight:700;color:#111827">📈 Daily Revenue — Last 30 Days</div>
            <div style="font-size:11px;color:#9ca3af;margin-top:2px">Gold bars = above average · Green dashed = daily average</div>
        </div>
        <div style="text-align:right">
            <div style="font-size:10px;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em">30-Day Total</div>
            <div style="font-size:18px;font-weight:800;color:#d97706">OMR {{ number_format(collect($d['daily30'])->sum('revenue'),3) }}</div>
        </div>
    </div>
    <div x-data="{}"
         x-init="$nextTick(() => {
            const el = $el.querySelector('canvas');
            if (!el || !window.Chart) return;
            if (el._ci) el._ci.destroy();
            const data = {{ collect($d['daily30'])->pluck('revenue')->toJson() }};
            const avg  = data.reduce((a,b)=>a+b,0)/data.length;
            const maxV = Math.max(...data)||1;
            el._ci = new Chart(el, {
                type:'bar',
                data:{
                    labels:{{ collect($d['daily30'])->pluck('day')->toJson() }},
                    datasets:[
                        {label:'Revenue (OMR)',data,
                         backgroundColor:data.map(v=>v>=avg?'rgba(245,158,11,.75)':'rgba(245,158,11,.2)'),
                         borderColor:data.map(v=>v>=avg?'#f59e0b':'rgba(245,158,11,.3)'),
                         borderWidth:1,borderRadius:4,order:2},
                        {label:'Daily Avg',data:Array(data.length).fill(parseFloat(avg.toFixed(3))),
                         type:'line',borderColor:'rgba(34,197,94,.8)',borderWidth:2,
                         borderDash:[6,4],pointRadius:0,fill:false,tension:0,order:1}
                    ]
                },
                options:{responsive:true,maintainAspectRatio:false,
                    interaction:{mode:'index',intersect:false},
                    plugins:{legend:{display:true,position:'top',align:'end',labels:{boxWidth:12,padding:10,usePointStyle:true,font:{size:10}}},
                             tooltip:{callbacks:{label:c=>c.dataset.label+': OMR '+(c.parsed.y||0).toFixed(3)}}},
                    scales:{x:{grid:{display:false},ticks:{font:{size:9},maxTicksLimit:10}},
                            y:{beginAtZero:true,ticks:{callback:v=>'OMR '+v.toFixed(0)}}}}
            });
         })"
         style="position:relative;height:220px">
        <canvas></canvas>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 3 — 12-Month Trend + Collections Donut
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px">

    {{-- 12-Month Line Chart --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">📅 Monthly Revenue — Last 12 Months</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Website + custom orders combined</div>
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                const vals = {{ collect($d['monthly'])->pluck('revenue')->toJson() }};
                const maxV = Math.max(...vals)||1;
                el._ci = new Chart(el, {
                    type:'bar',
                    data:{
                        labels:{{ collect($d['monthly'])->pluck('month')->toJson() }},
                        datasets:[
                            {label:'Revenue (OMR)',data:vals,
                             backgroundColor:vals.map((v,i)=>i===vals.length-1?'#f59e0b':`rgba(245,158,11,${(0.3+v/maxV*0.6).toFixed(2)})`),
                             borderRadius:5,borderWidth:0}
                        ]
                    },
                    options:{responsive:true,maintainAspectRatio:false,
                        plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>'OMR '+c.parsed.y.toFixed(3)}}},
                        scales:{x:{grid:{display:false},ticks:{font:{size:10}}},
                                y:{beginAtZero:true,ticks:{callback:v=>'OMR '+v.toFixed(0)}}}}
                });
             })"
             style="position:relative;height:200px">
            <canvas></canvas>
        </div>
    </div>

    {{-- Collections Donut --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">🏷️ Collections This Month</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:12px">Revenue by collection</div>
        @if($d['byCollection']->where('revenue','>',0)->count() > 0)
        @php
            $collLabels  = $d['byCollection']->pluck('name')->toJson();
            $collValues  = $d['byCollection']->pluck('revenue')->map(fn($v) => round((float)$v,3))->toJson();
        @endphp
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                el._ci = new Chart(el, {
                    type:'doughnut',
                    data:{labels:{{ $collLabels }},datasets:[{data:{{ $collValues }},backgroundColor:{{ json_encode($colPalette) }},borderWidth:0,hoverOffset:6}]},
                    options:{responsive:true,maintainAspectRatio:false,cutout:'68%',
                        plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>c.label+': OMR '+c.parsed.toFixed(3)}}}}
                });
             })"
             style="position:relative;height:140px">
            <canvas></canvas>
        </div>
        <div style="margin-top:12px;display:flex;flex-direction:column;gap:6px">
            @foreach($d['byCollection'] as $i => $c)
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div style="display:flex;align-items:center;gap:6px;min-width:0;flex:1">
                    <div style="width:8px;height:8px;border-radius:50%;background:{{ $colPalette[$i % count($colPalette)] }};flex-shrink:0"></div>
                    <span style="font-size:11px;color:#4b5563;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $c->name }}</span>
                </div>
                <span style="font-size:11px;font-weight:700;color:#d97706;margin-left:8px;white-space:nowrap">OMR {{ number_format($c->revenue,3) }}</span>
            </div>
            @endforeach
        </div>
        @else
        <div style="display:flex;flex-direction:column;align-items:center;padding:40px 0;color:#9ca3af">
            <span style="font-size:28px;margin-bottom:8px">🏷️</span>
            <span style="font-size:12px">No collection sales yet</span>
        </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 4 — Top Products + Payment Methods
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px">

    {{-- Top Products Horizontal Bar --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <div>
                <div style="font-size:14px;font-weight:700;color:#111827">🏆 Top Products — All Time</div>
                <div style="font-size:11px;color:#9ca3af;margin-top:2px">Revenue by product</div>
            </div>
            <a href="/admin/product-performance" style="font-size:11px;color:#f59e0b;text-decoration:none;font-weight:600">Full report →</a>
        </div>
        @if($d['topProducts']->count() > 0)
        @php
            $pLabels = $d['topProducts']->pluck('product_name')->map(fn($n) => mb_strlen($n)>22 ? mb_substr($n,0,22).'…' : $n)->toJson();
            $pVals   = $d['topProducts']->pluck('total_revenue')->map(fn($v) => round((float)$v,3))->toJson();
        @endphp
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                const vals = {{ $pVals }};
                const maxV = Math.max(...vals)||1;
                el._ci = new Chart(el, {
                    type:'bar',
                    data:{labels:{{ $pLabels }},datasets:[{
                        label:'Revenue (OMR)',data:vals,
                        backgroundColor:vals.map(v=>`rgba(245,158,11,${(0.35+v/maxV*0.6).toFixed(2)})`),
                        borderColor:'#f59e0b',borderWidth:1.5,borderRadius:{topRight:6,bottomRight:6},borderSkipped:'left'
                    }]},
                    options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,
                        plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>'OMR '+c.parsed.x.toFixed(3)}}},
                        scales:{x:{beginAtZero:true,ticks:{callback:v=>'OMR '+v.toFixed(0),font:{size:10}}},
                                y:{grid:{display:false},ticks:{font:{size:12,weight:'500'}}}}}
                });
             })"
             style="position:relative;height:{{ max(180, $d['topProducts']->count() * 44) }}px">
            <canvas></canvas>
        </div>
        @else
        <div style="display:flex;flex-direction:column;align-items:center;padding:48px 0;color:#9ca3af">
            <span style="font-size:32px;margin-bottom:8px">📦</span>
            <span style="font-size:12px">No product sales yet</span>
        </div>
        @endif
    </div>

    {{-- Payment Methods Donut --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">💳 Payment Methods</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:12px">Revenue by channel</div>
        @if($d['byPayment']->count() > 0)
        @php
            $payLabels = $d['byPayment']->map(fn($p) => match($p->payment_method){
                'cod'=>'Cash on Delivery','bank'=>'Bank Transfer','whatsapp'=>'WhatsApp',default=>ucfirst($p->payment_method)
            })->toJson();
            $payVals = $d['byPayment']->pluck('total')->map(fn($v)=>round((float)$v,3))->toJson();
            $totalPay = $d['byPayment']->sum('total');
        @endphp
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                el._ci = new Chart(el, {
                    type:'doughnut',
                    data:{labels:{{ $payLabels }},datasets:[{data:{{ $payVals }},backgroundColor:['#f59e0b','#3b82f6','#22c55e','#8b5cf6'],borderWidth:0,hoverOffset:6}]},
                    options:{responsive:true,maintainAspectRatio:false,cutout:'70%',
                        plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>c.label+': OMR '+c.parsed.toFixed(3)}}}}
                });
             })"
             style="position:relative;height:160px">
            <canvas></canvas>
        </div>
        <div style="margin-top:14px;display:flex;flex-direction:column;gap:8px">
            @php $pmColors=['cod'=>'#f59e0b','bank'=>'#3b82f6','whatsapp'=>'#22c55e']; @endphp
            @foreach($d['byPayment'] as $p)
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div style="display:flex;align-items:center;gap:6px">
                    <div style="width:8px;height:8px;border-radius:50%;background:{{ $pmColors[$p->payment_method] ?? '#8b5cf6' }}"></div>
                    <span style="font-size:11px;color:#4b5563">{{ match($p->payment_method){'cod'=>'Cash on Del.','bank'=>'Bank Transfer','whatsapp'=>'WhatsApp',default=>ucfirst($p->payment_method)} }}</span>
                </div>
                <div style="text-align:right">
                    <span style="font-size:11px;font-weight:700;color:#111827">{{ $totalPay>0 ? round(($p->total/$totalPay)*100,1) : 0 }}%</span>
                    <span style="font-size:10px;color:#9ca3af;margin-left:4px">{{ $p->cnt }} orders</span>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="display:flex;flex-direction:column;align-items:center;padding:48px 0;color:#9ca3af">
            <span style="font-size:28px;margin-bottom:8px">💳</span>
            <span style="font-size:12px">No payment data</span>
        </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 5 — Order Status Donut + Sales by Governorate
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 2fr;gap:16px;margin-bottom:16px">

    {{-- Order Status Donut --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">🛒 Order Status</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:12px">All-time distribution</div>
        @php
            $statuses  = ['pending','confirmed','processing','shipped','delivered','cancelled'];
            $stLabels  = ['Pending','Confirmed','Processing','Shipped','Delivered','Cancelled'];
            $stColors  = ['#f59e0b','#3b82f6','#8b5cf6','#10b981','#22c55e','#ef4444'];
            $stCounts  = array_values(array_map(fn($s) => $d['statusCounts'][$s] ?? 0, $statuses));
            $totalOrds = array_sum($stCounts);
        @endphp
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                el._ci = new Chart(el, {
                    type:'doughnut',
                    data:{labels:{{ json_encode($stLabels) }},datasets:[{data:{{ json_encode($stCounts) }},backgroundColor:{{ json_encode($stColors) }},borderWidth:0,hoverOffset:6}]},
                    options:{responsive:true,maintainAspectRatio:false,cutout:'68%',
                        plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>c.label+': '+c.parsed+' orders'}}}}
                });
             })"
             style="position:relative;height:160px">
            <canvas></canvas>
        </div>
        <div style="margin-top:12px;display:grid;grid-template-columns:1fr 1fr;gap:4px">
            @foreach($statuses as $i => $s)
            <div style="display:flex;align-items:center;gap:5px">
                <div style="width:7px;height:7px;border-radius:50%;background:{{ $stColors[$i] }}"></div>
                <span style="font-size:10px;color:#6b7280">{{ $stLabels[$i] }}</span>
                <span style="font-size:10px;font-weight:700;color:#111827;margin-left:auto">{{ $stCounts[$i] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Sales by Governorate --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">🗺️ Sales by Governorate</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Revenue distribution across Oman</div>
        @if($d['byGov']->count() > 0)
        @php
            $govLabels = $d['byGov']->pluck('governorate')->map(fn($g) => $g ?: 'Unknown')->toJson();
            $govVals   = $d['byGov']->pluck('total')->map(fn($v) => round((float)$v,3))->toJson();
        @endphp
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                const vals = {{ $govVals }};
                const maxV = Math.max(...vals)||1;
                el._ci = new Chart(el, {
                    type:'bar',
                    data:{labels:{{ $govLabels }},datasets:[{
                        label:'Revenue (OMR)',data:vals,
                        backgroundColor:vals.map(v=>`rgba(59,130,246,${(0.4+v/maxV*0.55).toFixed(2)})`),
                        borderColor:'#3b82f6',borderWidth:1.5,borderRadius:5
                    }]},
                    options:{responsive:true,maintainAspectRatio:false,
                        plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>'OMR '+c.parsed.y.toFixed(3)}}},
                        scales:{x:{grid:{display:false},ticks:{font:{size:10}}},
                                y:{beginAtZero:true,ticks:{callback:v=>'OMR '+v.toFixed(0)}}}}
                });
             })"
             style="position:relative;height:200px">
            <canvas></canvas>
        </div>
        @else
        <div style="display:flex;flex-direction:column;align-items:center;padding:60px 0;color:#9ca3af">
            <span style="font-size:32px;margin-bottom:8px">🗺️</span>
            <span style="font-size:12px">No location data available</span>
        </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 6 — Recent Orders Table
══════════════════════════════════════════════════════════ --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div>
            <div style="font-size:14px;font-weight:700;color:#111827">🕐 Recent Orders</div>
            <div style="font-size:11px;color:#9ca3af;margin-top:2px">Latest {{ $d['recent']->count() }} orders</div>
        </div>
        <a href="/admin/orders" style="font-size:12px;color:#f59e0b;text-decoration:none;font-weight:600">View all →</a>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="background:#f9fafb">
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;white-space:nowrap">Order</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Customer</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Items</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Total</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Status</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Payment</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($d['recent'] as $o)
                @php
                    $sc = match($o->status) {
                        'pending'    => ['#fef3c7','#92400e'],
                        'confirmed'  => ['#dbeafe','#1e40af'],
                        'processing' => ['#ede9fe','#5b21b6'],
                        'shipped'    => ['#d1fae5','#065f46'],
                        'delivered'  => ['#dcfce7','#14532d'],
                        'cancelled'  => ['#fee2e2','#991b1b'],
                        default      => ['#f3f4f6','#374151'],
                    };
                @endphp
                <tr style="border-top:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='#fff'">
                    <td style="padding:12px 16px">
                        <a href="/admin/orders/{{ $o->id }}/edit" style="font-family:monospace;font-size:12px;font-weight:600;color:#d97706;text-decoration:none">{{ $o->order_number }}</a>
                    </td>
                    <td style="padding:12px 16px;color:#374151;font-size:12px">{{ $o->first_name }} {{ $o->last_name }}</td>
                    <td style="padding:12px 16px;color:#6b7280;font-size:12px">{{ $o->items->sum('quantity') }} items</td>
                    <td style="padding:12px 16px;text-align:right;font-weight:700;color:#d97706;font-size:13px;font-variant-numeric:tabular-nums">OMR {{ number_format($o->total_omr,3) }}</td>
                    <td style="padding:12px 16px;text-align:center">
                        <span style="font-size:10px;font-weight:600;padding:3px 10px;border-radius:99px;background:{{ $sc[0] }};color:{{ $sc[1] }};white-space:nowrap">{{ ucfirst($o->status) }}</span>
                    </td>
                    <td style="padding:12px 16px;font-size:11px;color:#6b7280">
                        {{ match($o->payment_method ?? ''){'cod'=>'💵 Cash','bank'=>'🏦 Bank','whatsapp'=>'📱 WhatsApp',default=>ucfirst($o->payment_method ?? '-')} }}
                    </td>
                    <td style="padding:12px 16px;font-size:11px;color:#9ca3af;white-space:nowrap">{{ $o->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="padding:40px;text-align:center;color:#9ca3af;font-size:12px">No orders yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</x-filament-panels::page>
