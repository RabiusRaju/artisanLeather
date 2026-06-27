<x-filament-panels::page>
<script src="/js/chart.umd.min.js"></script>
@php
$d = $this->getData();
$k = $d['kpis'];
$colPalette = ['#f59e0b','#8b5cf6','#10b981','#ef4444','#3b82f6','#ec4899'];

$staleHours = function (?string $ts) {
    return $ts ? now()->diffInHours($ts) : null;
};
$ga4Stale = $staleHours($d['sync']['ga4_last_sync']);
$gscStale = $staleHours($d['sync']['gsc_last_sync']);
@endphp

{{-- ══════════════════════════════════════════════════════════
     Date range filter + sync status banner
══════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px">
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
        <select wire:model.live="range" style="font-size:12px;border:1px solid #e5e7eb;border-radius:8px;padding:7px 12px;color:#374151;background:#fff">
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="7d">Last 7 Days</option>
            <option value="30d">Last 30 Days</option>
            <option value="90d">Last 90 Days</option>
            <option value="year">This Year</option>
            <option value="custom">Custom Range</option>
        </select>

        @if($range === 'custom')
        <input type="date" wire:model.live="customFrom" style="font-size:12px;border:1px solid #e5e7eb;border-radius:8px;padding:6px 10px">
        <span style="font-size:12px;color:#9ca3af">to</span>
        <input type="date" wire:model.live="customTo" style="font-size:12px;border:1px solid #e5e7eb;border-radius:8px;padding:6px 10px">
        @endif
    </div>

    @if($ga4Stale === null || $gscStale === null || $ga4Stale > 6 || $gscStale > 72 || $d['sync']['ga4_last_error'] || $d['sync']['gsc_last_error'])
    <div style="font-size:11px;color:#92400e;background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:6px 12px">
        ⚠️
        @if($ga4Stale === null) GA4 not yet connected
        @elseif($d['sync']['ga4_last_error']) GA4 sync error
        @elseif($ga4Stale > 6) GA4 data {{ $ga4Stale }}h old
        @endif
        @if($gscStale === null) · Search Console not yet connected
        @elseif($d['sync']['gsc_last_error']) · Search Console sync error
        @elseif($gscStale > 72) · Search Console data {{ $gscStale }}h old
        @endif
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 1 — Visitors & Engagement
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:16px">
    <div style="border-radius:14px;border:1px solid #dbeafe;background:linear-gradient(135deg,#eff6ff,#fff);padding:16px 18px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:8px">Sessions</div>
        <div style="font-size:24px;font-weight:900;color:#2563eb;font-variant-numeric:tabular-nums">{{ number_format($k['sessions']) }}</div>
    </div>
    <div style="border-radius:14px;border:1px solid #dbeafe;background:linear-gradient(135deg,#eff6ff,#fff);padding:16px 18px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:8px">Users</div>
        <div style="font-size:24px;font-weight:900;color:#2563eb;font-variant-numeric:tabular-nums">{{ number_format($k['users']) }}</div>
    </div>
    <div style="border-radius:14px;border:1px solid #d1fae5;background:linear-gradient(135deg,#f0fdf4,#fff);padding:16px 18px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:8px">New Users</div>
        <div style="font-size:24px;font-weight:900;color:#059669;font-variant-numeric:tabular-nums">{{ number_format($k['new_users']) }}</div>
    </div>
    <div style="border-radius:14px;border:1px solid #fde68a;background:linear-gradient(135deg,#fffbeb,#fff);padding:16px 18px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:8px">Bounce Rate</div>
        <div style="font-size:24px;font-weight:900;color:#d97706;font-variant-numeric:tabular-nums">{{ $k['bounce_rate'] }}%</div>
    </div>
    <div style="border-radius:14px;border:1px solid #e9d5ff;background:linear-gradient(135deg,#faf5ff,#fff);padding:16px 18px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:8px">Avg Engagement</div>
        <div style="font-size:24px;font-weight:900;color:#7c3aed;font-variant-numeric:tabular-nums">{{ gmdate('i:s', $k['avg_engagement_time']) }}</div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 2 — Orders & Revenue
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px">
    <div style="border-radius:14px;border:1px solid #fecaca;background:linear-gradient(135deg,#fef2f2,#fff);padding:16px 18px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:8px">Orders</div>
        <div style="font-size:24px;font-weight:900;color:#dc2626;font-variant-numeric:tabular-nums">{{ number_format($k['orders']) }}</div>
    </div>
    <div style="border-radius:14px;border:1px solid #d1fae5;background:linear-gradient(135deg,#f0fdf4,#fff);padding:16px 18px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:8px">Revenue</div>
        <div style="font-size:24px;font-weight:900;color:#059669;font-variant-numeric:tabular-nums">OMR {{ number_format($k['revenue'],3) }}</div>
    </div>
    <div style="border-radius:14px;border:1px solid #bfdbfe;background:linear-gradient(135deg,#f0f9ff,#fff);padding:16px 18px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:8px">Conversion Rate</div>
        <div style="font-size:24px;font-weight:900;color:#0284c7;font-variant-numeric:tabular-nums">{{ $k['conversion_rate'] }}%</div>
    </div>
    <div style="border-radius:14px;border:1px solid #fde68a;background:linear-gradient(135deg,#fffbeb,#fff);padding:16px 18px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:8px">Avg Order Value</div>
        <div style="font-size:24px;font-weight:900;color:#d97706;font-variant-numeric:tabular-nums">OMR {{ number_format($k['aov'],3) }}</div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 3 — Search Console
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:16px 18px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:8px">Google Clicks</div>
        <div style="font-size:24px;font-weight:900;color:#111827;font-variant-numeric:tabular-nums">{{ number_format($k['gsc_clicks']) }}</div>
    </div>
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:16px 18px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:8px">Google Impressions</div>
        <div style="font-size:24px;font-weight:900;color:#111827;font-variant-numeric:tabular-nums">{{ number_format($k['gsc_impressions']) }}</div>
    </div>
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:16px 18px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:8px">CTR</div>
        <div style="font-size:24px;font-weight:900;color:#111827;font-variant-numeric:tabular-nums">{{ $k['gsc_ctr'] }}%</div>
    </div>
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:16px 18px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:8px">Avg Position</div>
        <div style="font-size:24px;font-weight:900;color:#111827;font-variant-numeric:tabular-nums">{{ $k['gsc_position'] }}</div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 4 — Visitors Trend + Orders/Revenue Trend
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">

    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">📈 Visitors Trend</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Sessions vs Users</div>
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                el._ci = new Chart(el, {
                    type:'line',
                    data:{
                        labels:{{ collect($d['visitorsTrend'])->pluck('date')->toJson() }},
                        datasets:[
                            {label:'Sessions',data:{{ collect($d['visitorsTrend'])->pluck('sessions')->toJson() }},borderColor:'#2563eb',backgroundColor:'rgba(37,99,235,.1)',fill:true,tension:.3,pointRadius:2},
                            {label:'Users',data:{{ collect($d['visitorsTrend'])->pluck('users')->toJson() }},borderColor:'#059669',backgroundColor:'rgba(5,150,105,.08)',fill:true,tension:.3,pointRadius:2}
                        ]
                    },
                    options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},
                        plugins:{legend:{display:true,position:'top',align:'end',labels:{boxWidth:12,padding:10,usePointStyle:true,font:{size:10}}}},
                        scales:{x:{grid:{display:false},ticks:{font:{size:9},maxTicksLimit:10}},y:{beginAtZero:true}}}
                });
             })"
             style="position:relative;height:220px">
            <canvas></canvas>
        </div>
    </div>

    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">💰 Revenue Trend</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Daily revenue (OMR)</div>
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                el._ci = new Chart(el, {
                    type:'bar',
                    data:{
                        labels:{{ collect($d['revenueTrend'])->pluck('date')->toJson() }},
                        datasets:[{label:'Revenue (OMR)',data:{{ collect($d['revenueTrend'])->pluck('revenue')->toJson() }},
                            backgroundColor:'rgba(245,158,11,.65)',borderColor:'#f59e0b',borderWidth:1,borderRadius:4}]
                    },
                    options:{responsive:true,maintainAspectRatio:false,
                        plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>'OMR '+(c.parsed.y||0).toFixed(3)}}},
                        scales:{x:{grid:{display:false},ticks:{font:{size:9},maxTicksLimit:10}},
                                y:{beginAtZero:true,ticks:{callback:v=>'OMR '+v.toFixed(0)}}}}
                });
             })"
             style="position:relative;height:220px">
            <canvas></canvas>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 5 — Search Performance Trend + Device Split
══════════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px">

    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">🔍 Search Performance</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Google Search clicks vs impressions</div>
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                el._ci = new Chart(el, {
                    type:'line',
                    data:{
                        labels:{{ collect($d['searchTrend'])->pluck('date')->toJson() }},
                        datasets:[
                            {label:'Clicks',data:{{ collect($d['searchTrend'])->pluck('clicks')->toJson() }},borderColor:'#f59e0b',backgroundColor:'rgba(245,158,11,.1)',fill:true,tension:.3,pointRadius:2,yAxisID:'y'},
                            {label:'Impressions',data:{{ collect($d['searchTrend'])->pluck('impressions')->toJson() }},borderColor:'#8b5cf6',backgroundColor:'rgba(139,92,246,.08)',fill:true,tension:.3,pointRadius:2,yAxisID:'y1'}
                        ]
                    },
                    options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},
                        plugins:{legend:{display:true,position:'top',align:'end',labels:{boxWidth:12,padding:10,usePointStyle:true,font:{size:10}}}},
                        scales:{
                            x:{grid:{display:false},ticks:{font:{size:9},maxTicksLimit:10}},
                            y:{beginAtZero:true,position:'left',title:{display:true,text:'Clicks',font:{size:9}}},
                            y1:{beginAtZero:true,position:'right',grid:{display:false},title:{display:true,text:'Impressions',font:{size:9}}}
                        }}
                });
             })"
             style="position:relative;height:200px">
            <canvas></canvas>
        </div>
    </div>

    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">📱 Device Split</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:12px">Sessions by device</div>
        @if(count($d['deviceSplit']) > 0)
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                el._ci = new Chart(el, {
                    type:'doughnut',
                    data:{labels:{{ collect($d['deviceSplit'])->pluck('device')->toJson() }},datasets:[{data:{{ collect($d['deviceSplit'])->pluck('sessions')->toJson() }},backgroundColor:{{ json_encode($colPalette) }},borderWidth:0,hoverOffset:6}]},
                    options:{responsive:true,maintainAspectRatio:false,cutout:'68%',plugins:{legend:{display:false}}}
                });
             })"
             style="position:relative;height:140px">
            <canvas></canvas>
        </div>
        <div style="margin-top:12px;display:flex;flex-direction:column;gap:6px">
            @foreach($d['deviceSplit'] as $i => $dev)
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div style="display:flex;align-items:center;gap:6px">
                    <div style="width:8px;height:8px;border-radius:50%;background:{{ $colPalette[$i % count($colPalette)] }}"></div>
                    <span style="font-size:11px;color:#4b5563;text-transform:capitalize">{{ $dev['device'] }}</span>
                </div>
                <span style="font-size:11px;font-weight:700;color:#111827">{{ number_format($dev['sessions']) }}</span>
            </div>
            @endforeach
        </div>
        @else
        <div style="display:flex;flex-direction:column;align-items:center;padding:40px 0;color:#9ca3af">
            <span style="font-size:28px;margin-bottom:8px">📱</span>
            <span style="font-size:12px">No device data yet</span>
        </div>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 6 — Top Countries
══════════════════════════════════════════════════════════ --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:16px">
    <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">🌍 Top Countries</div>
    <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Sessions by visitor country</div>
    @if(count($d['countryBreakdown']) > 0)
    <div x-data="{}"
         x-init="$nextTick(() => {
            const el = $el.querySelector('canvas');
            if (!el || !window.Chart) return;
            if (el._ci) el._ci.destroy();
            el._ci = new Chart(el, {
                type:'bar',
                data:{labels:{{ collect($d['countryBreakdown'])->pluck('country')->toJson() }},datasets:[{
                    label:'Sessions',data:{{ collect($d['countryBreakdown'])->pluck('sessions')->toJson() }},
                    backgroundColor:'rgba(59,130,246,.65)',borderColor:'#3b82f6',borderWidth:1,borderRadius:{topRight:6,bottomRight:6},borderSkipped:'left'
                }]},
                options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,
                    plugins:{legend:{display:false}},
                    scales:{x:{beginAtZero:true,ticks:{font:{size:10}}},y:{grid:{display:false},ticks:{font:{size:11}}}}}
            });
         })"
         style="position:relative;height:{{ max(160, count($d['countryBreakdown']) * 36) }}px">
        <canvas></canvas>
    </div>
    @else
    <div style="display:flex;flex-direction:column;align-items:center;padding:40px 0;color:#9ca3af">
        <span style="font-size:28px;margin-bottom:8px">🌍</span>
        <span style="font-size:12px">No country data yet</span>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 7 — Top Pages
══════════════════════════════════════════════════════════ --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:16px">
    <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div style="font-size:14px;font-weight:700;color:#111827">📄 Top Pages</div>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="background:#f9fafb">
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Page</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Views</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Users</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Bounce Rate</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Conversions</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($d['topPages'] as $p)
                <tr style="border-top:1px solid #f3f4f6">
                    <td style="padding:12px 16px;color:#374151;font-family:monospace;font-size:12px">{{ $p['page_path'] }}</td>
                    <td style="padding:12px 16px;text-align:right;color:#111827;font-weight:600">{{ number_format($p['views']) }}</td>
                    <td style="padding:12px 16px;text-align:right;color:#6b7280">{{ number_format($p['users']) }}</td>
                    <td style="padding:12px 16px;text-align:right;color:#6b7280">{{ $p['bounce_rate'] }}%</td>
                    <td style="padding:12px 16px;text-align:right;color:#6b7280">{{ $p['conversions'] }}</td>
                    <td style="padding:12px 16px;text-align:right;color:#059669;font-weight:700">OMR {{ number_format($p['revenue_omr'],3) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="padding:40px;text-align:center;color:#9ca3af;font-size:12px">No page data yet — runs after the first GA4 sync</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 8 — Top Keywords
══════════════════════════════════════════════════════════ --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06)">
    <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div style="font-size:14px;font-weight:700;color:#111827">🔑 Top Search Keywords</div>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="background:#f9fafb">
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Query</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Clicks</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Impressions</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">CTR</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Avg Position</th>
                </tr>
            </thead>
            <tbody>
                @forelse($d['topKeywords'] as $kw)
                <tr style="border-top:1px solid #f3f4f6">
                    <td style="padding:12px 16px;color:#374151">{{ $kw['query'] }}</td>
                    <td style="padding:12px 16px;text-align:right;color:#111827;font-weight:600">{{ number_format($kw['clicks']) }}</td>
                    <td style="padding:12px 16px;text-align:right;color:#6b7280">{{ number_format($kw['impressions']) }}</td>
                    <td style="padding:12px 16px;text-align:right;color:#6b7280">{{ $kw['ctr'] }}%</td>
                    <td style="padding:12px 16px;text-align:right;color:#6b7280">{{ $kw['position'] }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="padding:40px;text-align:center;color:#9ca3af;font-size:12px">No keyword data yet — runs after the first Search Console sync</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</x-filament-panels::page>
