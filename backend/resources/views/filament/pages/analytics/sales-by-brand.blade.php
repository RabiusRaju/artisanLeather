<x-filament-panels::page>
<script src="/js/chart.umd.min.js"></script>
@php
$d       = $this->getData();
$palette = ['#f59e0b','#3b82f6','#10b981','#8b5cf6','#ef4444','#ec4899','#06b6d4','#84cc16'];
$activeBrands = $d['brands']->where('total_revenue','>',0);
@endphp

{{-- ══════════════════════════════════════════════════════════
     ROW 1 — KPI Cards
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">

    <div style="border-radius:14px;border:1px solid #fde68a;background:linear-gradient(135deg,#fffbeb,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Total Revenue</span>
            <span style="font-size:20px">💰</span>
        </div>
        <div style="font-size:22px;font-weight:900;color:#d97706;font-variant-numeric:tabular-nums;line-height:1">OMR {{ number_format($d['total'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">All collections combined</div>
    </div>

    <div style="border-radius:14px;border:1px solid #dbeafe;background:linear-gradient(135deg,#eff6ff,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Total Orders</span>
            <span style="font-size:20px">📦</span>
        </div>
        <div style="font-size:32px;font-weight:900;color:#2563eb;line-height:1">{{ $d['totalOrders'] }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Across all collections</div>
    </div>

    <div style="border-radius:14px;border:1px solid #d1fae5;background:linear-gradient(135deg,#f0fdf4,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Units Sold</span>
            <span style="font-size:20px">🏷️</span>
        </div>
        <div style="font-size:32px;font-weight:900;color:#059669;line-height:1">{{ number_format($d['totalUnits']) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Total items sold</div>
    </div>

    <div style="border-radius:14px;border:1px solid #e9d5ff;background:linear-gradient(135deg,#faf5ff,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Active Collections</span>
            <span style="font-size:20px">✨</span>
        </div>
        <div style="font-size:32px;font-weight:900;color:#7c3aed;line-height:1">{{ $d['activeBrands'] }} <span style="font-size:16px;color:#9ca3af">/ {{ $d['brands']->count() }}</span></div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Have recorded sales</div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 2 — Revenue Bar Chart + Donut
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px">

    {{-- Bar Chart --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">📊 Revenue by Collection</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">All-time revenue per collection</div>
        @php
            $bLabels = $d['brands']->pluck('name')->toJson();
            $bVals   = $d['brands']->pluck('total_revenue')->map(fn($v)=>round((float)$v,3))->toJson();
        @endphp
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                const palette = {{ json_encode($palette) }};
                const vals = {{ $bVals }};
                el._ci = new Chart(el, {
                    type:'bar',
                    data:{
                        labels:{{ $bLabels }},
                        datasets:[{
                            data:vals,
                            backgroundColor:palette.map((c,i)=>c+'cc'),
                            borderColor:palette,
                            borderWidth:2,borderRadius:8
                        }]
                    },
                    options:{
                        responsive:true,maintainAspectRatio:false,
                        plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>'OMR '+c.parsed.y.toFixed(3)}}},
                        scales:{
                            x:{grid:{display:false},ticks:{font:{size:11,weight:'500'}}},
                            y:{beginAtZero:true,ticks:{callback:v=>'OMR '+v.toFixed(0)},grid:{color:'rgba(0,0,0,.05)'}}
                        }
                    }
                });
             })"
             style="position:relative;height:220px">
            <canvas></canvas>
        </div>
    </div>

    {{-- Donut + Legend --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">🥧 Revenue Share</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:12px">Collection contribution %</div>
        @if($activeBrands->count() > 0)
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                el._ci = new Chart(el, {
                    type:'doughnut',
                    data:{
                        labels:{{ $bLabels }},
                        datasets:[{data:{{ $bVals }},backgroundColor:{{ json_encode($palette) }},borderWidth:0,hoverOffset:6}]
                    },
                    options:{responsive:true,maintainAspectRatio:false,cutout:'68%',
                        plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>c.label+': OMR '+c.parsed.toFixed(3)}}}}
                });
             })"
             style="position:relative;height:160px">
            <canvas></canvas>
        </div>
        <div style="margin-top:14px;display:flex;flex-direction:column;gap:8px">
            @foreach($d['brands'] as $i => $b)
            @php $pct = $d['total']>0 ? round(($b->total_revenue/$d['total'])*100,1) : 0; @endphp
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:10px;height:10px;border-radius:50%;background:{{ $palette[$i % count($palette)] }};flex-shrink:0"></div>
                <span style="font-size:11px;color:#374151;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $b->name }}</span>
                <span style="font-size:12px;font-weight:700;color:#111827">{{ $pct }}%</span>
            </div>
            @endforeach
        </div>
        @else
        <div style="display:flex;flex-direction:column;align-items:center;padding:40px 0;color:#9ca3af">
            <span style="font-size:28px;margin-bottom:8px">📊</span>
            <span style="font-size:12px">No sales yet</span>
        </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 3 — 6-Month Trend Lines per Collection
══════════════════════════════════════════════════════════ --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:16px">
    <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">📈 Collection Trend — Last 6 Months</div>
    <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Monthly revenue per collection</div>
    @php
        $trendDatasets = [];
        foreach ($d['brands'] as $i => $brand) {
            $trendDatasets[] = [
                'label'           => $brand->name,
                'data'            => $d['brandMonthly'][$brand->id],
                'borderColor'     => $palette[$i % count($palette)],
                'backgroundColor' => $palette[$i % count($palette)] . '20',
                'borderWidth'     => 2.5,
                'pointRadius'     => 4,
                'pointBackgroundColor' => $palette[$i % count($palette)],
                'fill'            => false,
                'tension'         => 0.4,
            ];
        }
    @endphp
    <div x-data="{}"
         x-init="$nextTick(() => {
            const el = $el.querySelector('canvas');
            if (!el || !window.Chart) return;
            if (el._ci) el._ci.destroy();
            el._ci = new Chart(el, {
                type:'line',
                data:{
                    labels:{{ json_encode($d['monthLabels']) }},
                    datasets:{{ json_encode($trendDatasets) }}
                },
                options:{
                    responsive:true,maintainAspectRatio:false,
                    interaction:{mode:'index',intersect:false},
                    plugins:{
                        legend:{display:true,position:'top',align:'end',labels:{boxWidth:12,padding:12,usePointStyle:true,font:{size:11}}},
                        tooltip:{callbacks:{label:c=>c.dataset.label+': OMR '+c.parsed.y.toFixed(3)}}
                    },
                    scales:{
                        x:{grid:{display:false},ticks:{font:{size:10}}},
                        y:{beginAtZero:true,ticks:{callback:v=>'OMR '+v.toFixed(0)},grid:{color:'rgba(0,0,0,.05)'}}
                    }
                }
            });
         })"
         style="position:relative;height:220px">
        <canvas></canvas>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 4 — Collection Cards Grid
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:16px">
    @foreach($d['brands'] as $i => $b)
    @php
        $pct     = $d['total']>0 ? round(($b->total_revenue/$d['total'])*100,1) : 0;
        $color   = $palette[$i % count($palette)];
        $maxRev  = (float)($d['brands']->max('total_revenue') ?: 1);
        $barW    = $maxRev > 0 ? round(($b->total_revenue/$maxRev)*100) : 0;
    @endphp
    <div style="border-radius:12px;border:1px solid #e5e7eb;background:#fff;padding:16px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06);border-left:4px solid {{ $color }}">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px">
            <div>
                <div style="font-size:14px;font-weight:700;color:#111827">{{ $b->name }}</div>
                @if($b->name_ar)
                <div style="font-size:11px;color:#9ca3af;direction:rtl">{{ $b->name_ar }}</div>
                @endif
            </div>
            <div style="text-align:right">
                <div style="font-size:18px;font-weight:800;color:{{ $color }};font-variant-numeric:tabular-nums">OMR {{ number_format($b->total_revenue,3) }}</div>
                <div style="font-size:11px;color:#9ca3af">{{ $pct }}% of total</div>
            </div>
        </div>
        <div style="height:6px;background:#f3f4f6;border-radius:99px;overflow:hidden;margin-bottom:12px">
            <div style="height:100%;width:{{ $barW }}%;background:{{ $color }};border-radius:99px"></div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
            <div style="text-align:center;padding:8px;background:#f9fafb;border-radius:8px">
                <div style="font-size:16px;font-weight:800;color:#111827">{{ $b->product_count }}</div>
                <div style="font-size:10px;color:#9ca3af;margin-top:2px">Products</div>
            </div>
            <div style="text-align:center;padding:8px;background:#f9fafb;border-radius:8px">
                <div style="font-size:16px;font-weight:800;color:#111827">{{ $b->order_count }}</div>
                <div style="font-size:10px;color:#9ca3af;margin-top:2px">Orders</div>
            </div>
            <div style="text-align:center;padding:8px;background:#f9fafb;border-radius:8px">
                <div style="font-size:16px;font-weight:800;color:#111827">{{ $b->units_sold }}</div>
                <div style="font-size:10px;color:#9ca3af;margin-top:2px">Units</div>
            </div>
        </div>
        @if($d['topPerBrand'][$b->id] !== '—')
        <div style="margin-top:10px;padding:8px 12px;background:#fffbeb;border-radius:8px;border:1px solid #fde68a">
            <div style="font-size:10px;color:#92400e;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px">🏆 Top Product</div>
            <div style="font-size:12px;color:#78350f;font-weight:500">{{ $d['topPerBrand'][$b->id] }}</div>
        </div>
        @endif
    </div>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 5 — Full Comparison Table
══════════════════════════════════════════════════════════ --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06)">
    <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div style="font-size:14px;font-weight:700;color:#111827">📋 Collection Performance Table</div>
        <div style="font-size:11px;color:#9ca3af;margin-top:2px">Detailed breakdown per collection</div>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f9fafb">
                    @foreach(['Collection','Products','Orders','Units','Revenue','Share','Top Product'] as $col)
                    <th style="padding:10px 16px;text-align:{{ in_array($col,['Collection','Top Product'])?'left':'right' }};font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;white-space:nowrap">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($d['brands'] as $i => $b)
                @php $pct = $d['total']>0 ? round(($b->total_revenue/$d['total'])*100,1) : 0; @endphp
                <tr style="border-top:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:12px 16px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="width:10px;height:10px;border-radius:50%;background:{{ $palette[$i%count($palette)] }};flex-shrink:0"></div>
                            <div>
                                <div style="font-size:13px;font-weight:600;color:#111827">{{ $b->name }}</div>
                                @if($b->name_ar)<div style="font-size:10px;color:#9ca3af">{{ $b->name_ar }}</div>@endif
                            </div>
                        </div>
                    </td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;color:#374151">{{ $b->product_count }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;color:#374151">{{ $b->order_count }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;color:#374151">{{ $b->units_sold }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:14px;font-weight:700;color:#d97706;font-variant-numeric:tabular-nums">OMR {{ number_format($b->total_revenue,3) }}</td>
                    <td style="padding:12px 16px">
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px">
                            <div style="width:50px;height:6px;background:#f3f4f6;border-radius:99px;overflow:hidden">
                                <div style="height:100%;width:{{ $pct }}%;background:{{ $palette[$i%count($palette)] }};border-radius:99px"></div>
                            </div>
                            <span style="font-size:11px;color:#6b7280;width:32px;text-align:right">{{ $pct }}%</span>
                        </div>
                    </td>
                    <td style="padding:12px 16px;font-size:11px;color:#6b7280;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $d['topPerBrand'][$b->id] }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="padding:60px;text-align:center;color:#9ca3af"><div style="font-size:40px;margin-bottom:12px">🏷️</div><div>No collection data</div></td></tr>
                @endforelse
            </tbody>
            @if($d['brands']->count() > 0)
            <tfoot>
                <tr style="background:#f9fafb;border-top:2px solid #e5e7eb">
                    <td style="padding:12px 16px;font-size:12px;font-weight:700;color:#374151">Totals</td>
                    <td style="padding:12px 16px;text-align:right;font-size:12px;font-weight:600;color:#374151">{{ $d['brands']->sum('product_count') }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:12px;font-weight:600;color:#374151">{{ $d['totalOrders'] }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:12px;font-weight:600;color:#374151">{{ $d['totalUnits'] }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:14px;font-weight:800;color:#d97706">OMR {{ number_format($d['total'],3) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:11px;font-weight:600;color:#9ca3af">100%</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

</x-filament-panels::page>
