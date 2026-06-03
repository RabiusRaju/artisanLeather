<x-filament-panels::page>
<script src="/js/chart.umd.min.js"></script>
@php $d = $this->getReportData(); @endphp

{{-- Period Selector --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:20px">
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px">
        @foreach(['this_month'=>'This Month','last_month'=>'Last Month','this_quarter'=>'This Quarter','this_year'=>'This Year','last_year'=>'Last Year'] as $val=>$label)
        <button wire:click="$set('period','{{ $val }}')"
            style="padding:7px 18px;font-size:12px;font-weight:700;border-radius:8px;border:none;cursor:pointer;white-space:nowrap;
                   background:{{ $period===$val?'#f59e0b':'#f3f4f6' }};color:{{ $period===$val?'#fff':'#6b7280' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <span style="font-size:12px;color:#9ca3af;font-weight:500">Custom range:</span>
        <input type="date" wire:model.live="dateFrom"
            style="font-size:12px;border:1px solid #d1d5db;border-radius:8px;padding:6px 10px;background:#fff;color:#374151;outline:none">
        <span style="color:#9ca3af">→</span>
        <input type="date" wire:model.live="dateTo"
            style="font-size:12px;border:1px solid #d1d5db;border-radius:8px;padding:6px 10px;background:#fff;color:#374151;outline:none">
        <span style="font-size:11px;color:#9ca3af;margin-left:4px">{{ \Carbon\Carbon::parse($d['from'])->format('d M Y') }} – {{ \Carbon\Carbon::parse($d['to'])->format('d M Y') }}</span>
    </div>
</div>

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">

    <div style="border-radius:14px;border:1px solid #a7f3d0;background:linear-gradient(135deg,#ecfdf5,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Total Revenue</span>
            <span style="font-size:18px">💰</span>
        </div>
        <div style="font-size:22px;font-weight:900;color:#059669;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalRevenue'],3) }}</div>
        <div style="height:3px;background:#a7f3d0;border-radius:99px;margin-top:10px"></div>
    </div>

    <div style="border-radius:14px;border:1px solid #fde68a;background:linear-gradient(135deg,#fffbeb,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Purchases (COGS)</span>
            <span style="font-size:18px">📦</span>
        </div>
        <div style="font-size:22px;font-weight:900;color:#d97706;font-variant-numeric:tabular-nums">OMR {{ number_format($d['purchaseCost'],3) }}</div>
        <div style="height:3px;background:#fde68a;border-radius:99px;margin-top:10px"></div>
    </div>

    <div style="border-radius:14px;border:1px solid #fecaca;background:linear-gradient(135deg,#fef2f2,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Total Expenses</span>
            <span style="font-size:18px">💸</span>
        </div>
        <div style="font-size:22px;font-weight:900;color:#dc2626;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalExpenses'],3) }}</div>
        <div style="height:3px;background:#fecaca;border-radius:99px;margin-top:10px"></div>
    </div>

    <div style="border-radius:14px;border:2px solid {{ $d['netProfit']>=0?'#22c55e':'#ef4444' }};background:linear-gradient(135deg,{{ $d['netProfit']>=0?'#ecfdf5':'#fef2f2' }},#fff);padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.08)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Net Profit</span>
            <span style="font-size:18px">{{ $d['netProfit']>=0?'✅':'⚠️' }}</span>
        </div>
        <div style="font-size:22px;font-weight:900;color:{{ $d['netProfit']>=0?'#059669':'#dc2626' }};font-variant-numeric:tabular-nums">OMR {{ number_format($d['netProfit'],3) }}</div>
        <div style="font-size:11px;color:{{ $d['netProfit']>=0?'#059669':'#dc2626' }};margin-top:8px;font-weight:600">{{ $d['netMargin'] }}% net margin</div>
    </div>

</div>

{{-- Charts Row --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px">

    {{-- Overview Bar Chart --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">📊 Financial Overview</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Revenue · Purchases · Expenses · Net Profit</div>
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) { el._ci.destroy(); el._ci = null; }
                el._ci = new Chart(el, {
                    type:'bar',
                    data:{
                        labels:['Revenue','Purchases (COGS)','Expenses','Net Profit'],
                        datasets:[{
                            data:[{{ $d['totalRevenue'] }},{{ $d['purchaseCost'] }},{{ $d['totalExpenses'] }},{{ abs($d['netProfit']) }}],
                            backgroundColor:['rgba(34,197,94,.8)','rgba(234,179,8,.8)','rgba(239,68,68,.8)','{{ $d['netProfit']>=0?'rgba(16,185,129,.85)':'rgba(239,68,68,.85)' }}'],
                            borderColor:['#22c55e','#eab308','#ef4444','{{ $d['netProfit']>=0?'#10b981':'#ef4444' }}'],
                            borderWidth:2,borderRadius:8
                        }]
                    },
                    options:{
                        responsive:true,maintainAspectRatio:false,
                        plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>'OMR '+c.parsed.y.toFixed(3)}}},
                        scales:{x:{grid:{display:false},ticks:{font:{size:11,weight:'600'}}},y:{beginAtZero:true,ticks:{callback:v=>'OMR '+v.toFixed(0)},grid:{color:'rgba(0,0,0,.05)'}}}
                    }
                });
             })"
             style="position:relative;height:220px"><canvas></canvas></div>
    </div>

    {{-- Expense Donut --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">💸 Expense Breakdown</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:12px">By category</div>
        @if($d['expensesByCategory']->count() > 0)
        @php $expPalette = ['#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899','#10b981']; @endphp
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) { el._ci.destroy(); el._ci = null; }
                el._ci = new Chart(el, {
                    type:'doughnut',
                    data:{
                        labels:{{ $d['expensesByCategory']->keys()->toJson() }},
                        datasets:[{data:{{ $d['expensesByCategory']->values()->toJson() }},backgroundColor:{{ json_encode($expPalette) }},borderWidth:0,hoverOffset:6}]
                    },
                    options:{responsive:true,maintainAspectRatio:false,cutout:'70%',
                        plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>c.label+': OMR '+c.parsed.toFixed(3)}}}}
                });
             })"
             style="position:relative;height:140px"><canvas></canvas></div>
        <div style="margin-top:12px;display:flex;flex-direction:column;gap:6px">
            @foreach($d['expensesByCategory'] as $cat => $amt)
            @php $ci = $loop->index; @endphp
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div style="display:flex;align-items:center;gap:6px;min-width:0;flex:1">
                    <div style="width:8px;height:8px;border-radius:50%;background:{{ $expPalette[$ci%count($expPalette)] }};flex-shrink:0"></div>
                    <span style="font-size:11px;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $cat }}</span>
                </div>
                <span style="font-size:11px;font-weight:700;color:#dc2626;margin-left:8px;white-space:nowrap">OMR {{ number_format($amt,3) }}</span>
            </div>
            @endforeach
        </div>
        @else
        <div style="display:flex;flex-direction:column;align-items:center;padding:40px 0;color:#9ca3af">
            <span style="font-size:28px;margin-bottom:8px">📊</span>
            <span style="font-size:12px">No expenses in this period</span>
        </div>
        @endif
    </div>

</div>

{{-- Revenue Sources Horizontal Bar --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:16px">
    <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">💰 Revenue Sources</div>
    <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Website orders vs Custom orders vs Other income</div>
    <div x-data="{}"
         x-init="$nextTick(() => {
            const el = $el.querySelector('canvas');
            if (!el || !window.Chart) return;
            if (el._ci) { el._ci.destroy(); el._ci = null; }
            el._ci = new Chart(el, {
                type:'bar',
                data:{
                    labels:['Website Orders','Custom Orders','Other Income'],
                    datasets:[{
                        data:[{{ $d['websiteRevenue'] }},{{ $d['customRevenue'] }},{{ $d['otherRevenue'] }}],
                        backgroundColor:['rgba(34,197,94,.75)','rgba(16,185,129,.75)','rgba(59,130,246,.75)'],
                        borderColor:['#22c55e','#10b981','#3b82f6'],
                        borderWidth:2,borderRadius:{topRight:6,bottomRight:6},borderSkipped:'left'
                    }]
                },
                options:{
                    indexAxis:'y',responsive:true,maintainAspectRatio:false,
                    plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>'OMR '+c.parsed.x.toFixed(3)}}},
                    scales:{
                        x:{beginAtZero:true,ticks:{callback:v=>'OMR '+v.toFixed(0)},grid:{color:'rgba(0,0,0,.05)'}},
                        y:{grid:{display:false},ticks:{font:{size:12,weight:'600'}}}
                    }
                }
            });
         })"
         style="position:relative;height:130px"><canvas></canvas></div>
</div>

{{-- P&L Statement --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06)">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 24px;background:#f9fafb;border-bottom:1px solid #e5e7eb">
        <div>
            <div style="font-size:16px;font-weight:800;color:#111827">Profit & Loss Statement</div>
            <div style="font-size:12px;color:#9ca3af;margin-top:2px">{{ \Carbon\Carbon::parse($d['from'])->format('d M Y') }} – {{ \Carbon\Carbon::parse($d['to'])->format('d M Y') }}</div>
        </div>
        <span style="font-size:13px;font-weight:700;padding:6px 16px;border-radius:99px;background:{{ $d['netProfit']>=0?'#d1fae5':'#fee2e2' }};color:{{ $d['netProfit']>=0?'#065f46':'#991b1b' }}">
            {{ $d['netProfit']>=0?'✅ Profitable':'⚠️ Loss Period' }}
        </span>
    </div>

    {{-- REVENUE --}}
    <div style="padding:4px 0;background:#f0fdf4;border-bottom:1px solid #e5e7eb">
        <div style="padding:10px 24px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#15803d">💰 Revenue</div>
    </div>
    @foreach(['Website Orders'=>$d['websiteRevenue'],'Custom / Bespoke Orders'=>$d['customRevenue'],'Other Income (Wholesale etc.)'=>$d['otherRevenue']] as $label=>$val)
    <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 24px 13px 40px;border-bottom:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
        <span style="font-size:13px;color:#374151">{{ $label }}</span>
        <span style="font-size:13px;font-weight:600;color:#059669;font-variant-numeric:tabular-nums">OMR {{ number_format($val,3) }}</span>
    </div>
    @endforeach
    <div style="display:flex;align-items:baseline;justify-content:space-between;padding:14px 24px;background:#ecfdf5;border-bottom:2px solid #22c55e">
        <span style="font-size:14px;font-weight:800;color:#065f46">TOTAL REVENUE</span>
        <span style="font-size:18px;font-weight:900;color:#059669;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalRevenue'],3) }}</span>
    </div>

    {{-- COGS --}}
    <div style="padding:4px 0;background:#fffbeb;border-bottom:1px solid #e5e7eb">
        <div style="padding:10px 24px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#92400e">📦 Cost of Goods Sold</div>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 24px 13px 40px;border-bottom:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
        <span style="font-size:13px;color:#374151">Purchases received in period</span>
        <span style="font-size:13px;font-weight:600;color:#d97706;font-variant-numeric:tabular-nums">OMR {{ number_format($d['purchaseCost'],3) }}</span>
    </div>
    <div style="display:flex;align-items:baseline;justify-content:space-between;padding:14px 24px;background:{{ $d['grossProfit']>=0?'#ecfdf5':'#fef2f2' }};border-bottom:2px solid {{ $d['grossProfit']>=0?'#22c55e':'#ef4444' }}">
        <div>
            <span style="font-size:14px;font-weight:800;color:{{ $d['grossProfit']>=0?'#065f46':'#991b1b' }}">GROSS PROFIT</span>
            <span style="font-size:12px;color:#9ca3af;margin-left:8px">{{ $d['grossMargin'] }}% margin</span>
        </div>
        <span style="font-size:18px;font-weight:900;color:{{ $d['grossProfit']>=0?'#059669':'#dc2626' }};font-variant-numeric:tabular-nums">OMR {{ number_format($d['grossProfit'],3) }}</span>
    </div>

    {{-- EXPENSES --}}
    <div style="padding:4px 0;background:#fef2f2;border-bottom:1px solid #e5e7eb">
        <div style="padding:10px 24px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#991b1b">💸 Operating Expenses</div>
    </div>
    @forelse($d['expensesByCategory'] as $catName => $amount)
    <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 24px 13px 40px;border-bottom:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
        <span style="font-size:13px;color:#374151">{{ $catName }}</span>
        <span style="font-size:13px;font-weight:600;color:#dc2626;font-variant-numeric:tabular-nums">OMR {{ number_format($amount,3) }}</span>
    </div>
    @empty
    <div style="padding:16px 40px;font-size:12px;color:#9ca3af;font-style:italic;border-bottom:1px solid #f3f4f6">No expenses recorded in this period</div>
    @endforelse
    <div style="display:flex;align-items:baseline;justify-content:space-between;padding:14px 24px;background:#fef2f2;border-bottom:2px solid #ef4444">
        <span style="font-size:14px;font-weight:800;color:#991b1b">TOTAL EXPENSES</span>
        <span style="font-size:18px;font-weight:900;color:#dc2626;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalExpenses'],3) }}</span>
    </div>

    {{-- NET PROFIT --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:22px 24px;background:{{ $d['netProfit']>=0?'linear-gradient(135deg,#ecfdf5,#d1fae5)':'linear-gradient(135deg,#fef2f2,#fee2e2)' }};border-top:3px solid {{ $d['netProfit']>=0?'#22c55e':'#ef4444' }}">
        <div>
            <div style="font-size:16px;font-weight:900;color:{{ $d['netProfit']>=0?'#065f46':'#991b1b' }};text-transform:uppercase;letter-spacing:.04em">NET PROFIT / (LOSS)</div>
            <div style="font-size:12px;color:{{ $d['netProfit']>=0?'#059669':'#dc2626' }};margin-top:4px;font-weight:600">Net margin: {{ $d['netMargin'] }}%</div>
        </div>
        <div style="font-size:28px;font-weight:900;color:{{ $d['netProfit']>=0?'#059669':'#dc2626' }};font-variant-numeric:tabular-nums">
            OMR {{ number_format($d['netProfit'],3) }}
        </div>
    </div>

</div>

<script>
// Re-init Alpine charts after Livewire updates period/date
document.addEventListener('livewire:updated', () => {
    document.querySelectorAll('[x-data]').forEach(el => {
        if (el._x_dataStack) {
            const canvas = el.querySelector('canvas');
            if (canvas && canvas._ci) {
                canvas._ci.destroy();
                canvas._ci = null;
            }
        }
    });
    if (window.Alpine) {
        window.Alpine.initTree(document.body);
    }
});
</script>

</x-filament-panels::page>
