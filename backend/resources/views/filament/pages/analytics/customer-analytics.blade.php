<x-filament-panels::page>
<script src="/js/chart.umd.min.js"></script>
@php $d = $this->getData(); @endphp

{{-- ══════════════════════════════════════════════════════════
     ROW 1 — KPI Cards
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">

    <div style="border-radius:14px;border:1px solid #dbeafe;background:linear-gradient(135deg,#eff6ff,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Total Customers</span>
            <span style="font-size:20px">👥</span>
        </div>
        <div style="font-size:32px;font-weight:900;color:#2563eb;line-height:1">{{ number_format($d['total']) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Registered accounts</div>
    </div>

    <div style="border-radius:14px;border:1px solid #fde68a;background:linear-gradient(135deg,#fffbeb,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">VIP Customers</span>
            <span style="font-size:20px">⭐</span>
        </div>
        <div style="font-size:32px;font-weight:900;color:#d97706;line-height:1">{{ $d['vip'] }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">{{ $d['total']>0 ? round(($d['vip']/$d['total'])*100,1) : 0 }}% of total</div>
    </div>

    <div style="border-radius:14px;border:1px solid #d1fae5;background:linear-gradient(135deg,#f0fdf4,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">New This Month</span>
            <span style="font-size:20px">🆕</span>
        </div>
        <div style="font-size:32px;font-weight:900;color:#059669;line-height:1">{{ $d['newThisMonth'] }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">{{ now()->format('F Y') }}</div>
    </div>

    <div style="border-radius:14px;border:1px solid #e9d5ff;background:linear-gradient(135deg,#faf5ff,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Repeat Buyers</span>
            <span style="font-size:20px">🔄</span>
        </div>
        <div style="font-size:32px;font-weight:900;color:#7c3aed;line-height:1">{{ $d['repeat'] }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Ordered 2+ times</div>
    </div>

    <div style="border-radius:14px;border:1px solid #fecaca;background:linear-gradient(135deg,#fef2f2,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Inactive 60+ Days</span>
            <span style="font-size:20px">😴</span>
        </div>
        <div style="font-size:32px;font-weight:900;color:#dc2626;line-height:1">{{ $d['inactive'] }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Need re-engagement</div>
    </div>

    <div style="border-radius:14px;border:1px solid #bfdbfe;background:linear-gradient(135deg,#f0f9ff,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Avg Lifetime Value</span>
            <span style="font-size:20px">💎</span>
        </div>
        <div style="font-size:22px;font-weight:900;color:#0284c7;font-variant-numeric:tabular-nums;line-height:1">OMR {{ number_format($d['avgLifetimeValue'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Per customer avg spend</div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 2 — 6-Month Trend + Spending Tiers
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px">

    {{-- 6-Month Trend Bar+Line --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">📈 New Customers & Orders — Last 6 Months</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Acquisition trend with order volume</div>
        @php
            $mLabels  = collect($d['monthly'])->pluck('month')->toJson();
            $mNew     = collect($d['monthly'])->pluck('new')->toJson();
            $mOrders  = collect($d['monthly'])->pluck('orders')->toJson();
            $mRevenue = collect($d['monthly'])->pluck('revenue')->toJson();
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
                            {type:'bar',label:'New Customers',data:{{ $mNew }},
                             backgroundColor:'rgba(59,130,246,0.7)',borderRadius:5,borderWidth:0,yAxisID:'y'},
                            {type:'bar',label:'Orders',data:{{ $mOrders }},
                             backgroundColor:'rgba(245,158,11,0.7)',borderRadius:5,borderWidth:0,yAxisID:'y'},
                            {type:'line',label:'Revenue (OMR)',data:{{ $mRevenue }},
                             borderColor:'#10b981',backgroundColor:'rgba(16,185,129,.1)',
                             borderWidth:2.5,pointRadius:4,pointBackgroundColor:'#10b981',
                             fill:true,tension:0.4,yAxisID:'y1'}
                        ]
                    },
                    options:{
                        responsive:true,maintainAspectRatio:false,
                        interaction:{mode:'index',intersect:false},
                        plugins:{
                            legend:{display:true,position:'top',align:'end',labels:{boxWidth:12,padding:10,usePointStyle:true,font:{size:10}}},
                            tooltip:{callbacks:{
                                label:c=>c.dataset.yAxisID==='y1'?'Revenue: OMR '+c.parsed.y.toFixed(3):c.dataset.label+': '+c.parsed.y
                            }}
                        },
                        scales:{
                            x:{grid:{display:false},ticks:{font:{size:10}}},
                            y:{beginAtZero:true,position:'left',ticks:{stepSize:1,font:{size:10}},grid:{color:'rgba(0,0,0,.05)'}},
                            y1:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false},ticks:{callback:v=>'OMR '+v.toFixed(0),font:{size:10}}}
                        }
                    }
                });
             })"
             style="position:relative;height:210px">
            <canvas></canvas>
        </div>
    </div>

    {{-- Spending Tiers Donut --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">💰 Spending Tiers</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:12px">Customers by lifetime spend</div>
        @php
            $tierColors = ['#22c55e','#84cc16','#f59e0b','#f97316','#ef4444'];
            $tierLabels = array_keys($d['tiers']);
            $tierVals   = array_values($d['tiers']);
        @endphp
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                el._ci = new Chart(el, {
                    type:'doughnut',
                    data:{labels:{{ json_encode($tierLabels) }},datasets:[{data:{{ json_encode($tierVals) }},backgroundColor:{{ json_encode($tierColors) }},borderWidth:0,hoverOffset:6}]},
                    options:{responsive:true,maintainAspectRatio:false,cutout:'68%',
                        plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>c.label+': '+c.parsed+' customers'}}}}
                });
             })"
             style="position:relative;height:150px">
            <canvas></canvas>
        </div>
        <div style="margin-top:12px;display:flex;flex-direction:column;gap:6px">
            @foreach($tierLabels as $i => $label)
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div style="display:flex;align-items:center;gap:6px">
                    <div style="width:8px;height:8px;border-radius:50%;background:{{ $tierColors[$i] }}"></div>
                    <span style="font-size:11px;color:#4b5563">{{ $label }}</span>
                </div>
                <span style="font-size:12px;font-weight:700;color:#111827">{{ $tierVals[$i] }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 3 — Customer Segments + Geography
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">

    {{-- Segment Progress Bars --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:16px">🎯 Customer Segments</div>
        @php
        $segments = [
            ['label'=>'VIP Customers',        'count'=>$d['vip'],          'color'=>'#f59e0b','desc'=>'High-value loyal buyers'],
            ['label'=>'Repeat Buyers',         'count'=>$d['repeat'],       'color'=>'#8b5cf6','desc'=>'Ordered more than once'],
            ['label'=>'New This Month',        'count'=>$d['newThisMonth'], 'color'=>'#22c55e','desc'=>'Recently joined'],
            ['label'=>'Inactive (60+ days)',   'count'=>$d['inactive'],     'color'=>'#ef4444','desc'=>'Need re-engagement'],
        ];
        $base = max($d['total'], 1);
        @endphp
        <div style="display:flex;flex-direction:column;gap:20px">
            @foreach($segments as $seg)
            @php $pct = min(round(($seg['count'] / $base)*100, 1), 100); @endphp
            <div>
                <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:6px">
                    <div>
                        <span style="font-size:13px;font-weight:600;color:#111827">{{ $seg['label'] }}</span>
                        <span style="font-size:11px;color:#9ca3af;margin-left:6px">{{ $seg['desc'] }}</span>
                    </div>
                    <div style="text-align:right;flex-shrink:0;margin-left:12px">
                        <span style="font-size:16px;font-weight:800;color:{{ $seg['color'] }}">{{ $seg['count'] }}</span>
                        <span style="font-size:11px;color:#9ca3af;margin-left:4px">{{ $pct }}%</span>
                    </div>
                </div>
                <div style="height:8px;background:#f3f4f6;border-radius:99px;overflow:hidden">
                    <div style="height:100%;width:{{ $pct }}%;background:{{ $seg['color'] }};border-radius:99px;transition:width .6s ease"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Sales by Governorate --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">🗺️ Customers by Governorate</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Revenue & order concentration across Oman</div>
        @if($d['byGov']->count() > 0)
        @php
            $govLabels = $d['byGov']->pluck('governorate')->toJson();
            $govRevs   = $d['byGov']->pluck('revenue')->map(fn($v)=>round((float)$v,3))->toJson();
            $govCusts  = $d['byGov']->pluck('customers')->toJson();
        @endphp
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                const vals = {{ $govRevs }};
                const custs = {{ $govCusts }};
                const maxV = Math.max(...vals)||1;
                el._ci = new Chart(el, {
                    type:'bar',
                    data:{
                        labels:{{ $govLabels }},
                        datasets:[{
                            label:'Revenue (OMR)',data:vals,
                            backgroundColor:vals.map(v=>`rgba(59,130,246,${(0.4+v/maxV*0.55).toFixed(2)})`),
                            borderColor:'#3b82f6',borderWidth:1.5,borderRadius:6
                        }]
                    },
                    options:{
                        responsive:true,maintainAspectRatio:false,
                        plugins:{legend:{display:false},tooltip:{callbacks:{
                            label:c=>'Revenue: OMR '+c.parsed.y.toFixed(3),
                            afterLabel:(c)=>'Customers: '+custs[c.dataIndex]
                        }}},
                        scales:{x:{grid:{display:false},ticks:{font:{size:10}}},y:{beginAtZero:true,ticks:{callback:v=>'OMR '+v.toFixed(0),font:{size:10}}}}
                    }
                });
             })"
             style="position:relative;height:200px">
            <canvas></canvas>
        </div>
        @else
        <div style="display:flex;flex-direction:column;align-items:center;padding:60px 0;color:#9ca3af">
            <span style="font-size:32px;margin-bottom:8px">🗺️</span>
            <span style="font-size:12px">No location data yet</span>
        </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 4 — Top Customers Table
══════════════════════════════════════════════════════════ --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div>
            <div style="font-size:14px;font-weight:700;color:#111827">🏆 Top {{ $d['topCustomers']->count() }} Customers by Lifetime Spend</div>
            <div style="font-size:11px;color:#9ca3af;margin-top:2px">Ranked by total revenue generated</div>
        </div>
        <a href="/admin/customers" style="font-size:12px;color:#f59e0b;text-decoration:none;font-weight:600">View all →</a>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f9fafb">
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">#</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Customer</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Orders</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Lifetime Spend</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Avg Order</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">First Order</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Last Order</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Share</th>
                </tr>
            </thead>
            <tbody>
                @php $maxSpend = $d['topCustomers']->max('lifetime_spend') ?: 1; @endphp
                @forelse($d['topCustomers'] as $i => $c)
                @php
                    $rank  = $i + 1;
                    $medal = match($rank){ 1=>'🥇',2=>'🥈',3=>'🥉',default=>'' };
                    $avg   = $c->order_count > 0 ? round($c->lifetime_spend/$c->order_count,3) : 0;
                    $share = $d['totalRevenue']>0 ? round(($c->lifetime_spend/$d['totalRevenue'])*100,1) : 0;
                    $pct   = round(($c->lifetime_spend/$maxSpend)*100);
                @endphp
                <tr style="border-top:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:12px 16px;font-size:13px;font-weight:700;color:#9ca3af">{{ $medal ?: $rank }}</td>
                    <td style="padding:12px 16px">
                        <div style="font-size:13px;font-weight:600;color:#111827">{{ $c->first_name }} {{ $c->last_name }}</div>
                        <div style="font-size:11px;color:#9ca3af">{{ $c->email }}</div>
                    </td>
                    <td style="padding:12px 16px;text-align:center">
                        <span style="font-size:12px;font-weight:700;padding:3px 10px;border-radius:99px;background:#dbeafe;color:#1e40af">{{ $c->order_count }}</span>
                    </td>
                    <td style="padding:12px 16px;text-align:right">
                        <div style="font-size:14px;font-weight:800;color:#d97706;font-variant-numeric:tabular-nums">OMR {{ number_format($c->lifetime_spend,3) }}</div>
                        <div style="height:4px;background:#f3f4f6;border-radius:99px;margin-top:4px;overflow:hidden">
                            <div style="height:100%;width:{{ $pct }}%;background:#f59e0b;border-radius:99px"></div>
                        </div>
                    </td>
                    <td style="padding:12px 16px;text-align:right;font-size:12px;color:#6b7280;font-variant-numeric:tabular-nums">OMR {{ number_format($avg,3) }}</td>
                    <td style="padding:12px 16px;font-size:11px;color:#9ca3af;white-space:nowrap">{{ \Carbon\Carbon::parse($c->first_order)->format('d M Y') }}</td>
                    <td style="padding:12px 16px;font-size:11px;color:#9ca3af;white-space:nowrap">{{ \Carbon\Carbon::parse($c->last_order)->format('d M Y') }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:12px;font-weight:600;color:#6b7280">{{ $share }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding:60px;text-align:center;color:#9ca3af">
                        <div style="font-size:40px;margin-bottom:12px">👥</div>
                        <div style="font-size:14px">No customer orders yet</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</x-filament-panels::page>
