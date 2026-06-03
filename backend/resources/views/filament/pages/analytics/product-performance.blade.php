<x-filament-panels::page>
<script src="/js/chart.umd.min.js"></script>
@php
$d = $this->getData();
$colPalette = ['#f59e0b','#10b981','#3b82f6','#8b5cf6','#ef4444','#ec4899','#06b6d4','#84cc16'];
@endphp

{{-- ══════════════════════════════════════════════════════════
     ROW 1 — KPI Cards
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">

    <div style="border-radius:14px;border:1px solid #dbeafe;background:linear-gradient(135deg,#eff6ff,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Products Sold</span>
            <span style="font-size:20px">🛍️</span>
        </div>
        <div style="font-size:28px;font-weight:900;color:#2563eb;line-height:1">{{ $d['products']->count() }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Unique products with sales</div>
    </div>

    <div style="border-radius:14px;border:1px solid #d1fae5;background:linear-gradient(135deg,#f0fdf4,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Units Sold</span>
            <span style="font-size:20px">📦</span>
        </div>
        <div style="font-size:28px;font-weight:900;color:#059669;line-height:1">{{ number_format($d['totalUnits']) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Total units across all orders</div>
    </div>

    <div style="border-radius:14px;border:1px solid #fde68a;background:linear-gradient(135deg,#fffbeb,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Total Revenue</span>
            <span style="font-size:20px">💰</span>
        </div>
        <div style="font-size:22px;font-weight:900;color:#d97706;font-variant-numeric:tabular-nums;line-height:1">OMR {{ number_format($d['totalRevenue'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">All-time product revenue</div>
    </div>

    <div style="border-radius:14px;border:1px solid #e9d5ff;background:linear-gradient(135deg,#faf5ff,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Avg per Product</span>
            <span style="font-size:20px">📊</span>
        </div>
        <div style="font-size:22px;font-weight:900;color:#7c3aed;font-variant-numeric:tabular-nums;line-height:1">OMR {{ number_format($d['avgRevPerProd'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Average revenue per product</div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 2 — Top Products Horizontal Bar (full width)
══════════════════════════════════════════════════════════ --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:16px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <div>
            <div style="font-size:14px;font-weight:700;color:#111827">🏆 Top Products by Revenue</div>
            <div style="font-size:11px;color:#9ca3af;margin-top:2px">All-time revenue ranking</div>
        </div>
        <div style="display:flex;align-items:center;gap:16px">
            <div style="text-align:right">
                <div style="font-size:10px;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em">#1 Product</div>
                <div style="font-size:13px;font-weight:700;color:#d97706">{{ $d['top8']->first()?->product_name ?? '—' }}</div>
            </div>
        </div>
    </div>
    @if($d['top8']->count() > 0)
    @php
        $top8Labels = $d['top8']->pluck('product_name')->map(fn($n) => mb_strlen($n)>24 ? mb_substr($n,0,24).'…' : $n)->toJson();
        $top8Vals   = $d['top8']->pluck('total_revenue')->map(fn($v) => round((float)$v,3))->toJson();
        $top8Units  = $d['top8']->pluck('units_sold')->toJson();
    @endphp
    <div x-data="{}"
         x-init="$nextTick(() => {
            const el = $el.querySelector('canvas');
            if (!el || !window.Chart) return;
            if (el._ci) el._ci.destroy();
            const vals  = {{ $top8Vals }};
            const units = {{ $top8Units }};
            const maxV  = Math.max(...vals)||1;
            el._ci = new Chart(el, {
                type:'bar',
                data:{
                    labels:{{ $top8Labels }},
                    datasets:[{
                        label:'Revenue (OMR)',
                        data:vals,
                        backgroundColor:vals.map(v=>`rgba(245,158,11,${(0.35+v/maxV*0.6).toFixed(2)})`),
                        borderColor:'#f59e0b',borderWidth:1.5,
                        borderRadius:{topRight:6,bottomRight:6},borderSkipped:'left'
                    }]
                },
                options:{
                    indexAxis:'y',responsive:true,maintainAspectRatio:false,
                    plugins:{
                        legend:{display:false},
                        tooltip:{callbacks:{
                            label:c=>'Revenue: OMR '+c.parsed.x.toFixed(3),
                            afterLabel:(c)=>'Units sold: '+units[c.dataIndex]
                        }}
                    },
                    scales:{
                        x:{beginAtZero:true,grid:{color:'rgba(0,0,0,.05)'},ticks:{callback:v=>'OMR '+v.toFixed(0),font:{size:10}}},
                        y:{grid:{display:false},ticks:{font:{size:12,weight:'500'}}}
                    }
                }
            });
         })"
         style="position:relative;height:{{ max(200, $d['top8']->count() * 46) }}px">
        <canvas></canvas>
    </div>
    @else
    <div style="display:flex;flex-direction:column;align-items:center;padding:60px 0;color:#9ca3af">
        <span style="font-size:40px;margin-bottom:12px">📦</span>
        <span style="font-size:14px">No product sales recorded yet</span>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 3 — Monthly Trend + Category Donut
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px">

    {{-- Monthly Revenue Trend --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">📅 Monthly Sales Trend — Last 6 Months</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Revenue and units sold per month</div>
        @php
            $mLabels  = collect($d['monthlyTrend'])->pluck('month')->toJson();
            $mRevs    = collect($d['monthlyTrend'])->pluck('revenue')->toJson();
            $mUnits   = collect($d['monthlyTrend'])->pluck('units')->toJson();
        @endphp
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                el._ci = new Chart(el, {
                    data:{
                        labels:{{ $mLabels }},
                        datasets:[
                            {type:'bar',label:'Revenue (OMR)',data:{{ $mRevs }},
                             backgroundColor:'rgba(245,158,11,0.65)',borderRadius:6,borderWidth:0,yAxisID:'y'},
                            {type:'line',label:'Units Sold',data:{{ $mUnits }},
                             borderColor:'#10b981',backgroundColor:'rgba(16,185,129,.1)',
                             borderWidth:2.5,pointRadius:4,pointBackgroundColor:'#10b981',
                             fill:true,tension:0.4,yAxisID:'y1'}
                        ]
                    },
                    options:{
                        responsive:true,maintainAspectRatio:false,
                        interaction:{mode:'index',intersect:false},
                        plugins:{
                            legend:{display:true,position:'top',align:'end',labels:{boxWidth:12,padding:12,usePointStyle:true,font:{size:10}}},
                            tooltip:{callbacks:{
                                label:c=>c.dataset.label+(c.dataset.yAxisID==='y'?': OMR '+c.parsed.y.toFixed(3):': '+c.parsed.y+' units')
                            }}
                        },
                        scales:{
                            x:{grid:{display:false},ticks:{font:{size:10}}},
                            y:{beginAtZero:true,position:'left',ticks:{callback:v=>'OMR '+v.toFixed(0),font:{size:10}}},
                            y1:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false},ticks:{callback:v=>v+' u',font:{size:10}}}
                        }
                    }
                });
             })"
             style="position:relative;height:210px">
            <canvas></canvas>
        </div>
    </div>

    {{-- Revenue by Category --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">🏷️ Revenue by Category</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:12px">All-time by product category</div>
        @if($d['byCategory']->count() > 0)
        @php
            $catLabels = $d['byCategory']->pluck('name')->toJson();
            $catVals   = $d['byCategory']->pluck('revenue')->toJson();
        @endphp
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                el._ci = new Chart(el, {
                    type:'doughnut',
                    data:{labels:{{ $catLabels }},datasets:[{data:{{ $catVals }},backgroundColor:{{ json_encode($colPalette) }},borderWidth:0,hoverOffset:6}]},
                    options:{responsive:true,maintainAspectRatio:false,cutout:'68%',
                        plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>c.label+': OMR '+c.parsed.toFixed(3)}}}}
                });
             })"
             style="position:relative;height:150px">
            <canvas></canvas>
        </div>
        <div style="margin-top:12px;display:flex;flex-direction:column;gap:8px">
            @foreach($d['byCategory'] as $i => $cat)
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:10px;height:10px;border-radius:50%;background:{{ $colPalette[$i % count($colPalette)] }};flex-shrink:0"></div>
                <span style="font-size:11px;color:#4b5563;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $cat['name'] }}</span>
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-size:11px;font-weight:700;color:#d97706">OMR {{ number_format($cat['revenue'],3) }}</div>
                    <div style="font-size:10px;color:#9ca3af">{{ $cat['units'] }} units</div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="display:flex;flex-direction:column;align-items:center;padding:40px 0;color:#9ca3af">
            <span style="font-size:28px;margin-bottom:8px">🏷️</span>
            <span style="font-size:12px">No category data</span>
        </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 4 — Stock Levels Bar
══════════════════════════════════════════════════════════ --}}
@if($d['stockLevels']->count() > 0)
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:16px">
    <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">📦 Current Stock Levels</div>
    <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Inventory status — red = out of stock, amber = low stock</div>
    @php
        $stkLabels = $d['stockLevels']->pluck('name')->map(fn($n) => mb_strlen($n)>20 ? mb_substr($n,0,20).'…' : $n)->toJson();
        $stkVals   = $d['stockLevels']->pluck('quantity')->map(fn($v) => max(0,(int)$v))->toJson();
        $stkAlerts = $d['stockLevels']->pluck('minimum_alert')->map(fn($v) => (int)$v)->toJson();
    @endphp
    <div x-data="{}"
         x-init="$nextTick(() => {
            const el = $el.querySelector('canvas');
            if (!el || !window.Chart) return;
            if (el._ci) el._ci.destroy();
            const qty    = {{ $stkVals }};
            const alerts = {{ $stkAlerts }};
            el._ci = new Chart(el, {
                type:'bar',
                data:{
                    labels:{{ $stkLabels }},
                    datasets:[{
                        label:'Stock Qty',
                        data:qty,
                        backgroundColor:qty.map((v,i)=>v<=0?'rgba(239,68,68,.75)':v<=alerts[i]?'rgba(245,158,11,.75)':'rgba(34,197,94,.65)'),
                        borderColor:qty.map((v,i)=>v<=0?'#ef4444':v<=alerts[i]?'#f59e0b':'#22c55e'),
                        borderWidth:1.5,borderRadius:5
                    }]
                },
                options:{
                    responsive:true,maintainAspectRatio:false,
                    plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>'Stock: '+c.parsed.y+' units'}}},
                    scales:{x:{grid:{display:false},ticks:{font:{size:10}}},y:{beginAtZero:true,ticks:{stepSize:1}}}
                }
            });
         })"
         style="position:relative;height:160px">
        <canvas></canvas>
    </div>
    {{-- Legend --}}
    <div style="display:flex;align-items:center;gap:16px;margin-top:10px">
        <div style="display:flex;align-items:center;gap:5px"><div style="width:10px;height:10px;border-radius:2px;background:#22c55e"></div><span style="font-size:11px;color:#6b7280">In Stock</span></div>
        <div style="display:flex;align-items:center;gap:5px"><div style="width:10px;height:10px;border-radius:2px;background:#f59e0b"></div><span style="font-size:11px;color:#6b7280">Low Stock</span></div>
        <div style="display:flex;align-items:center;gap:5px"><div style="width:10px;height:10px;border-radius:2px;background:#ef4444"></div><span style="font-size:11px;color:#6b7280">Out of Stock</span></div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     ROW 5 — Complete Product Table
══════════════════════════════════════════════════════════ --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div>
            <div style="font-size:14px;font-weight:700;color:#111827">📋 Complete Product Performance</div>
            <div style="font-size:11px;color:#9ca3af;margin-top:2px">All {{ $d['products']->count() }} products ranked by revenue</div>
        </div>
        <div style="font-size:12px;color:#9ca3af">Sorted by revenue ↓</div>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f9fafb">
                    @foreach(['#','Product','Category','Units','Orders','Avg Price','Revenue','Share'] as $col)
                    <th style="padding:10px 16px;text-align:{{ in_array($col,['#','Product','Category'])?'left':'right' }};font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;white-space:nowrap">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($d['products'] as $i => $p)
                @php
                    $pct    = $d['totalRevenue'] > 0 ? round(($p->total_revenue/$d['totalRevenue'])*100,1) : 0;
                    $rank   = $i + 1;
                    $medal  = match($rank){ 1=>'🥇', 2=>'🥈', 3=>'🥉', default=>'' };
                @endphp
                <tr style="border-top:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:12px 16px;font-size:12px;color:#9ca3af;font-weight:600">{{ $medal ?: $rank }}</td>
                    <td style="padding:12px 16px">
                        <div style="font-size:13px;font-weight:600;color:#111827">{{ $p->product_name }}</div>
                    </td>
                    <td style="padding:12px 16px">
                        <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:#f3f4f6;color:#374151;font-weight:500">{{ $p->category_name ?? '—' }}</span>
                    </td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:600;color:#059669;font-variant-numeric:tabular-nums">{{ number_format($p->units_sold) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:12px;color:#6b7280;font-variant-numeric:tabular-nums">{{ $p->order_count }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:12px;color:#6b7280;font-variant-numeric:tabular-nums">OMR {{ number_format($p->avg_price,3) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:14px;font-weight:700;color:#d97706;font-variant-numeric:tabular-nums">OMR {{ number_format($p->total_revenue,3) }}</td>
                    <td style="padding:12px 16px">
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px">
                            <div style="width:60px;height:6px;background:#f3f4f6;border-radius:99px;overflow:hidden">
                                <div style="height:100%;width:{{ min($pct,100) }}%;background:#f59e0b;border-radius:99px"></div>
                            </div>
                            <span style="font-size:11px;color:#6b7280;width:36px;text-align:right">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding:60px;text-align:center;color:#9ca3af">
                        <div style="font-size:40px;margin-bottom:12px">📦</div>
                        <div style="font-size:14px">No sales data yet</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($d['products']->count() > 0)
            <tfoot>
                <tr style="background:#f9fafb;border-top:2px solid #e5e7eb">
                    <td colspan="3" style="padding:12px 16px;font-size:12px;font-weight:700;color:#374151">Totals</td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:700;color:#059669">{{ number_format($d['totalUnits']) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:12px;font-weight:600;color:#6b7280">{{ $d['products']->sum('order_count') }}</td>
                    <td style="padding:12px 16px"></td>
                    <td style="padding:12px 16px;text-align:right;font-size:14px;font-weight:800;color:#d97706">OMR {{ number_format($d['totalRevenue'],3) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:11px;font-weight:600;color:#9ca3af">100%</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

</x-filament-panels::page>
