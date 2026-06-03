<x-filament-panels::page>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@php $d = $this->getData(); @endphp

<div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-6">
    <div class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4"><p class="text-[10px] uppercase tracking-widest text-gray-500 font-medium mb-1">Total Products Sold</p><p class="text-2xl font-bold text-blue-600 dark:text-blue-400 tabular-nums">{{ $d['products']->count() }} products</p></div>
    <div class="rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-4"><p class="text-[10px] uppercase tracking-widest text-gray-500 font-medium mb-1">Total Units Sold</p><p class="text-2xl font-bold text-green-600 dark:text-green-400 tabular-nums">{{ number_format($d['totalUnits']) }} units</p></div>
    <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4 col-span-2 md:col-span-1"><p class="text-[10px] uppercase tracking-widest text-gray-500 font-medium mb-1">Total Revenue (All Products)</p><p class="text-2xl font-bold text-amber-600 dark:text-amber-400 tabular-nums">OMR {{ number_format($d['totalRevenue'],3) }}</p></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1">🏆 Top Performers by Revenue</h3>
        <p class="text-xs text-gray-400 mb-4">Products that generate the most income</p>
        <div style="position:relative;height:200px;"><canvas id="bestChart"></canvas></div>
    </div>
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1">🐌 Slowest Movers</h3>
        <p class="text-xs text-gray-400 mb-4">Products that need attention or promotion</p>
        <div style="position:relative;height:200px;"><canvas id="slowChart"></canvas></div>
    </div>
</div>

<div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
    <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Complete Product Performance Table</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-xs uppercase tracking-wider text-gray-500 bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-2.5 text-left">#</th>
                    <th class="px-4 py-2.5 text-left">Product</th>
                    <th class="px-4 py-2.5 text-left">Category</th>
                    <th class="px-4 py-2.5 text-right">Units Sold</th>
                    <th class="px-4 py-2.5 text-right">Orders</th>
                    <th class="px-4 py-2.5 text-right">Avg Price</th>
                    <th class="px-4 py-2.5 text-right">Revenue</th>
                    <th class="px-4 py-2.5 text-right">Share %</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($d['products'] as $i => $p)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $i+1 }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $p->product_name }}</td>
                    <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">{{ $p->category_name ?? '—' }}</span></td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ $p->units_sold }}</td>
                    <td class="px-4 py-3 text-right tabular-nums text-gray-500">{{ $p->order_count }}</td>
                    <td class="px-4 py-3 text-right tabular-nums text-gray-500">OMR {{ number_format($p->avg_price,3) }}</td>
                    <td class="px-4 py-3 text-right tabular-nums font-bold text-amber-600 dark:text-amber-400">OMR {{ number_format($p->total_revenue,3) }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @php $pct = $d['totalRevenue']>0 ? round(($p->total_revenue/$d['totalRevenue'])*100,1):0 @endphp
                            <div class="w-16 h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden"><div class="h-full bg-amber-400 rounded-full" style="width:{{ min($pct,100) }}%"></div></div>
                            <span class="text-xs text-gray-500 w-8 text-right">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400 text-xs">No sales data yet. Orders will appear here once placed.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', initPP);
document.addEventListener('livewire:navigated', initPP);
function initPP() {
    const dark=document.documentElement.classList.contains('dark');
    const text=dark?'#9ca3af':'#6b7280', grid=dark?'rgba(255,255,255,0.06)':'rgba(0,0,0,0.06)';

    const opts = (color) => ({
        type:'bar', options:{
            indexAxis:'y', responsive:true, maintainAspectRatio:false,
            plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>'OMR '+c.parsed.x.toFixed(3)}}},
            scales:{x:{ticks:{color:text,font:{size:10},callback:v=>'OMR '+v.toFixed(0)},grid:{color:grid},beginAtZero:true},y:{ticks:{color:text,font:{size:10}},grid:{display:false}}}
        }
    });

    const bc = document.getElementById('bestChart');
    if (bc) { if(bc._c)bc._c.destroy(); bc._c=new Chart(bc,{...opts('#22c55e'),data:{labels:@json($d['best']->pluck('product_name')->map(fn($n)=>strlen($n)>20?substr($n,0,20).'…':$n)),datasets:[{data:@json($d['best']->pluck('total_revenue')),backgroundColor:'rgba(34,197,94,0.75)',borderColor:'rgba(34,197,94,1)',borderWidth:2,borderRadius:4}]}}); }

    const sc = document.getElementById('slowChart');
    if (sc && {{ $d['slowest']->count() }}>0) { if(sc._c)sc._c.destroy(); sc._c=new Chart(sc,{...opts('#f97316'),data:{labels:@json($d['slowest']->pluck('product_name')->map(fn($n)=>strlen($n)>20?substr($n,0,20).'…':$n)),datasets:[{data:@json($d['slowest']->pluck('total_revenue')),backgroundColor:'rgba(249,115,22,0.65)',borderColor:'rgba(249,115,22,1)',borderWidth:2,borderRadius:4}]}}); }
}
document.addEventListener('livewire:updated',()=>setTimeout(initPP,100));
</script>
</x-filament-panels::page>
