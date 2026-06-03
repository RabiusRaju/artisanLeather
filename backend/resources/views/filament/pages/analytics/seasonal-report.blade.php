<x-filament-panels::page>
<script src="/js/chart.umd.min.js"></script>
@php $d = $this->getData(); $maxMonthRev = max(collect($d['monthStats'])->max('revenue'), 1); @endphp

{{-- ══════════════════════════════════════════════════════════
     ROW 1 — KPI Cards
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">

    <div style="border-radius:14px;border:1px solid #d1fae5;background:linear-gradient(135deg,#f0fdf4,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">{{ $d['now']->year }} Revenue</span>
            <span style="font-size:20px">💰</span>
        </div>
        <div style="font-size:20px;font-weight:900;color:#059669;font-variant-numeric:tabular-nums;line-height:1">OMR {{ number_format($d['totalYear'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Year to date</div>
    </div>

    <div style="border-radius:14px;border:1px solid #fde68a;background:linear-gradient(135deg,#fffbeb,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Monthly Average</span>
            <span style="font-size:20px">📊</span>
        </div>
        <div style="font-size:20px;font-weight:900;color:#d97706;font-variant-numeric:tabular-nums;line-height:1">OMR {{ number_format($d['avgMonth'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Active months only</div>
    </div>

    <div style="border-radius:14px;border:1px solid #dbeafe;background:linear-gradient(135deg,#eff6ff,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Best Month</span>
            <span style="font-size:20px">🏆</span>
        </div>
        <div style="font-size:22px;font-weight:900;color:#2563eb;line-height:1">{{ $d['bestMonth']['month'] ?? '—' }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">OMR {{ number_format($d['bestMonth']['revenue']??0,3) }}</div>
    </div>

    <div style="border-radius:14px;border:1px solid {{ $d['yoyGrowth']>=0?'#bbf7d0':'#fecaca' }};background:linear-gradient(135deg,{{ $d['yoyGrowth']>=0?'#f0fdf4':'#fef2f2' }},#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">YoY Growth</span>
            <span style="font-size:20px">{{ $d['yoyGrowth']>=0?'📈':'📉' }}</span>
        </div>
        <div style="font-size:26px;font-weight:900;color:{{ $d['yoyGrowth']>=0?'#059669':'#dc2626' }};line-height:1">{{ $d['yoyGrowth']>=0?'+':'' }}{{ $d['yoyGrowth'] }}%</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">vs {{ $d['now']->year-1 }} (OMR {{ number_format($d['totalLY'],3) }})</div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 2 — Year-over-Year Chart (full width)
══════════════════════════════════════════════════════════ --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:16px">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px">
        <div>
            <div style="font-size:14px;font-weight:700;color:#111827">📅 {{ $d['now']->year }} vs {{ $d['now']->year-1 }} — Monthly Comparison</div>
            <div style="font-size:11px;color:#9ca3af;margin-top:2px">Identify seasonal peaks · Plan stock & promotions ahead</div>
        </div>
        <div style="display:flex;gap:16px;align-items:center">
            <div style="display:flex;align-items:center;gap:6px"><div style="width:12px;height:12px;border-radius:2px;background:#f59e0b"></div><span style="font-size:11px;color:#6b7280">{{ $d['now']->year }}</span></div>
            <div style="display:flex;align-items:center;gap:6px"><div style="width:12px;height:12px;border-radius:2px;background:#d1d5db"></div><span style="font-size:11px;color:#6b7280">{{ $d['now']->year-1 }}</span></div>
        </div>
    </div>
    <div x-data="{}"
         x-init="$nextTick(() => {
            const el = $el.querySelector('canvas');
            if (!el || !window.Chart) return;
            if (el._ci) el._ci.destroy();
            el._ci = new Chart(el, {
                type:'bar',
                data:{
                    labels:{{ json_encode($d['months']) }},
                    datasets:[
                        {label:'{{ $d["now"]->year }}',data:{{ json_encode($d['currentYear']) }},
                         backgroundColor:'rgba(245,158,11,0.8)',borderColor:'#f59e0b',borderWidth:2,borderRadius:6,order:2},
                        {label:'{{ $d["now"]->year-1 }}',data:{{ json_encode($d['lastYear']) }},
                         backgroundColor:'rgba(209,213,219,0.5)',borderColor:'#9ca3af',borderWidth:1.5,borderRadius:6,order:3},
                        {label:'Orders',data:{{ json_encode($d['orderCounts']) }},type:'line',
                         borderColor:'#3b82f6',borderWidth:2,pointRadius:4,pointBackgroundColor:'#3b82f6',
                         fill:false,tension:0.4,yAxisID:'y1',order:1}
                    ]
                },
                options:{
                    responsive:true,maintainAspectRatio:false,
                    interaction:{mode:'index',intersect:false},
                    plugins:{
                        legend:{display:false},
                        tooltip:{callbacks:{
                            label:c=>c.dataset.yAxisID==='y1'?'Orders: '+c.parsed.y:c.dataset.label+': OMR '+c.parsed.y.toFixed(3)
                        }}
                    },
                    scales:{
                        x:{grid:{display:false},ticks:{font:{size:11,weight:'500'}}},
                        y:{beginAtZero:true,position:'left',ticks:{callback:v=>'OMR '+v.toFixed(0)},grid:{color:'rgba(0,0,0,.05)'}},
                        y1:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false},ticks:{stepSize:1,callback:v=>v+' ord',font:{size:10}}}
                    }
                }
            });
         })"
         style="position:relative;height:260px">
        <canvas></canvas>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 3 — Quarterly Summary
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px">
    @php
        $qColors = ['#f59e0b','#10b981','#3b82f6','#8b5cf6'];
        $qBorders = ['#fde68a','#a7f3d0','#bfdbfe','#e9d5ff'];
        $qBg = ['#fffbeb','#f0fdf4','#eff6ff','#faf5ff'];
        $qLabels = array_keys($d['quarters']);
        $qVals   = array_values($d['quarters']);
        $qLYVals = array_values($d['quartersLY']);
    @endphp
    @foreach($qLabels as $qi => $qLabel)
    @php
        $qRev    = $qVals[$qi];
        $qLY     = $qLYVals[$qi];
        $qGrowth = $qLY > 0 ? round((($qRev-$qLY)/$qLY)*100,1) : null;
    @endphp
    <div style="border-radius:14px;border:1px solid {{ $qBorders[$qi] }};background:linear-gradient(135deg,{{ $qBg[$qi] }},#fff);padding:16px 18px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:13px;font-weight:800;color:{{ $qColors[$qi] }}">{{ $qLabel }}</span>
            @if($qGrowth !== null)
            <span style="font-size:10px;font-weight:700;padding:2px 6px;border-radius:99px;background:{{ $qGrowth>=0?'#d1fae5':'#fee2e2' }};color:{{ $qGrowth>=0?'#065f46':'#991b1b' }}">
                {{ $qGrowth>=0?'▲':'▼' }} {{ abs($qGrowth) }}%
            </span>
            @endif
        </div>
        <div style="font-size:18px;font-weight:900;color:#111827;font-variant-numeric:tabular-nums;line-height:1">OMR {{ number_format($qRev,3) }}</div>
        <div style="font-size:10px;color:#9ca3af;margin-top:6px">{{ $d['now']->year-1 }}: OMR {{ number_format($qLY,3) }}</div>
    </div>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 4 — Quarterly Bar + Seasonal Heatmap
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">

    {{-- Quarterly Chart --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">📊 Quarterly Revenue</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">{{ $d['now']->year }} vs {{ $d['now']->year-1 }} by quarter</div>
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                el._ci = new Chart(el, {
                    type:'bar',
                    data:{
                        labels:['Q1','Q2','Q3','Q4'],
                        datasets:[
                            {label:'{{ $d["now"]->year }}',data:{{ json_encode(array_values($d['quarters'])) }},
                             backgroundColor:['rgba(245,158,11,.8)','rgba(16,185,129,.8)','rgba(59,130,246,.8)','rgba(139,92,246,.8)'],
                             borderWidth:0,borderRadius:8},
                            {label:'{{ $d["now"]->year-1 }}',data:{{ json_encode(array_values($d['quartersLY'])) }},
                             backgroundColor:'rgba(209,213,219,.4)',borderColor:'#9ca3af',borderWidth:1.5,borderRadius:8}
                        ]
                    },
                    options:{
                        responsive:true,maintainAspectRatio:false,
                        plugins:{legend:{display:true,position:'top',align:'end',labels:{boxWidth:10,padding:10,font:{size:10}}},
                                 tooltip:{callbacks:{label:c=>c.dataset.label+': OMR '+c.parsed.y.toFixed(3)}}},
                        scales:{x:{grid:{display:false},ticks:{font:{size:11}}},y:{beginAtZero:true,ticks:{callback:v=>'OMR '+v.toFixed(0)}}}
                    }
                });
             })"
             style="position:relative;height:200px">
            <canvas></canvas>
        </div>
    </div>

    {{-- Seasonal Heatmap (visual grid) --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">🌡️ Revenue Intensity Heatmap</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Darker = higher revenue · * = Oman seasonal events</div>
        <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:6px">
            @foreach($d['monthStats'] as $i => $ms)
            @php
                $intensity = $maxMonthRev > 0 ? ($ms['revenue']/$maxMonthRev) : 0;
                $r = 245; $g = 158; $b = 11;
                $alpha = max(0.08, $intensity);
                $textColor = $intensity > 0.5 ? '#78350f' : ($intensity > 0 ? '#92400e' : '#9ca3af');
                $hasEvent = isset($d['seasonalEvents'][$i+1]);
            @endphp
            <div style="border-radius:8px;padding:8px 4px;text-align:center;background:rgba({{ $r }},{{ $g }},{{ $b }},{{ round($alpha,2) }});border:1px solid rgba({{ $r }},{{ $g }},{{ $b }},{{ round(min($alpha+0.1,1),2) }})">
                <div style="font-size:10px;font-weight:700;color:{{ $textColor }}">{{ $ms['month'] }}{{ $hasEvent?' *':'' }}</div>
                <div style="font-size:11px;font-weight:900;color:{{ $intensity>0?'#92400e':'#d1d5db' }};font-variant-numeric:tabular-nums;margin-top:2px">
                    {{ $ms['revenue']>0?number_format($ms['revenue'],0):'0' }}
                </div>
                @if($ms['orders'] > 0)
                <div style="font-size:9px;color:#b45309;margin-top:1px">{{ $ms['orders'] }}ord</div>
                @endif
            </div>
            @endforeach
        </div>
        <div style="margin-top:12px;font-size:10px;color:#9ca3af;border-top:1px solid #f3f4f6;padding-top:10px">
            * Approximate Oman seasonal events: Ramadan, Eid Al-Fitr, Eid Al-Adha (dates vary yearly), National Day (Nov)
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 5 — Monthly Table
══════════════════════════════════════════════════════════ --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div>
            <div style="font-size:14px;font-weight:700;color:#111827">📋 Monthly Breakdown — {{ $d['now']->year }}</div>
            <div style="font-size:11px;color:#9ca3af;margin-top:2px">Full year comparison with growth indicators</div>
        </div>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f9fafb">
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Month</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">{{ $d['now']->year }} Revenue</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Orders</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">{{ $d['now']->year-1 }} Revenue</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">YoY</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Performance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($d['monthStats'] as $i => $ms)
                @php
                    $isBest   = ($ms['month'] === ($d['bestMonth']['month']??''));
                    $pct      = ($ms['revenue']/$maxMonthRev)*100;
                    $barColor = $pct>70?'#22c55e':($pct>30?'#f59e0b':'#d1d5db');
                    $hasEvent = isset($d['seasonalEvents'][$i+1]);
                @endphp
                <tr style="border-top:1px solid #f3f4f6;background:{{ $isBest?'#fffbeb':'transparent' }}"
                    onmouseover="this.style.background='{{ $isBest?'#fef9c3':'#f9fafb' }}'"
                    onmouseout="this.style.background='{{ $isBest?'#fffbeb':'transparent' }}'">
                    <td style="padding:12px 16px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <span style="font-size:13px;font-weight:{{ $isBest?'700':'500' }};color:{{ $isBest?'#d97706':'#374151' }}">
                                {{ $ms['month'] }} {{ $d['now']->year }}
                                {{ $isBest?' 🏆':'' }}
                            </span>
                            @if($hasEvent)
                            <span style="font-size:10px;padding:1px 6px;border-radius:99px;background:#fef3c7;color:#92400e;font-weight:600">🌙 {{ $d['seasonalEvents'][$i+1] }}</span>
                            @endif
                        </div>
                    </td>
                    <td style="padding:12px 16px;text-align:right;font-size:14px;font-weight:{{ $ms['revenue']>0?'700':'400' }};color:{{ $ms['revenue']>0?'#d97706':'#9ca3af' }};font-variant-numeric:tabular-nums">
                        OMR {{ number_format($ms['revenue'],3) }}
                    </td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;color:#374151">
                        {{ $ms['orders'] > 0 ? $ms['orders'] : '—' }}
                    </td>
                    <td style="padding:12px 16px;text-align:right;font-size:12px;color:#9ca3af;font-variant-numeric:tabular-nums">
                        OMR {{ number_format($ms['last_year'],3) }}
                    </td>
                    <td style="padding:12px 16px;text-align:center">
                        @if($ms['growth'] !== null)
                        <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:99px;background:{{ $ms['growth']>=0?'#d1fae5':'#fee2e2' }};color:{{ $ms['growth']>=0?'#065f46':'#991b1b' }}">
                            {{ $ms['growth']>=0?'▲':'▼' }} {{ abs($ms['growth']) }}%
                        </span>
                        @else
                        <span style="font-size:11px;color:#d1d5db">—</span>
                        @endif
                    </td>
                    <td style="padding:12px 16px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="width:80px;height:6px;background:#f3f4f6;border-radius:99px;overflow:hidden">
                                <div style="height:100%;width:{{ round($pct) }}%;background:{{ $barColor }};border-radius:99px"></div>
                            </div>
                            <span style="font-size:10px;color:#9ca3af">{{ round($pct) }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f9fafb;border-top:2px solid #e5e7eb">
                    <td style="padding:12px 16px;font-size:12px;font-weight:700;color:#374151">Total {{ $d['now']->year }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:14px;font-weight:800;color:#d97706;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalYear'],3) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:600;color:#374151">{{ array_sum($d['orderCounts']) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:12px;font-weight:600;color:#9ca3af;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalLY'],3) }}</td>
                    <td style="padding:12px 16px;text-align:center">
                        <span style="font-size:12px;font-weight:700;padding:3px 10px;border-radius:99px;background:{{ $d['yoyGrowth']>=0?'#d1fae5':'#fee2e2' }};color:{{ $d['yoyGrowth']>=0?'#065f46':'#991b1b' }}">
                            {{ $d['yoyGrowth']>=0?'▲':'▼' }} {{ abs($d['yoyGrowth']) }}%
                        </span>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

</x-filament-panels::page>
