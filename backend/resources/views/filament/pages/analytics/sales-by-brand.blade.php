<x-filament-panels::page>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@php $d = $this->getData(); @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="lg:col-span-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">📊 Revenue by Collection</h3>
        <div style="position:relative;height:240px;"><canvas id="brandChart"></canvas></div>
    </div>
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">🥧 Revenue Share</h3>
        @if($d['brands']->where('total_revenue','>',0)->count() > 0)
        <div style="position:relative;height:180px;"><canvas id="brandDonut"></canvas></div>
        @else
        <div class="flex flex-col items-center justify-center h-40 text-gray-400"><span class="text-3xl mb-2">📊</span><p class="text-xs">No sales data yet</p></div>
        @endif
    </div>
</div>

<div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
    <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Collection Performance Breakdown</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-xs uppercase tracking-wider text-gray-500 bg-gray-50 dark:bg-gray-800">
                <tr><th class="px-4 py-2.5 text-left">Collection</th><th class="px-4 py-2.5 text-center">Products</th><th class="px-4 py-2.5 text-center">Orders</th><th class="px-4 py-2.5 text-center">Units Sold</th><th class="px-4 py-2.5 text-right">Revenue</th><th class="px-4 py-2.5 text-right">Share</th><th class="px-4 py-2.5 text-left">Top Product</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($d['brands'] as $b)
                @php $pct = $d['total']>0 ? round(($b->total_revenue/$d['total'])*100,1):0 @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                    <td class="px-4 py-3"><div class="font-medium text-gray-900 dark:text-white">{{ $b->name }}</div>@if($b->name_ar)<div class="text-xs text-gray-400">{{ $b->name_ar }}</div>@endif</td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $b->product_count }}</td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $b->order_count }}</td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $b->units_sold }}</td>
                    <td class="px-4 py-3 text-right font-bold tabular-nums text-amber-600 dark:text-amber-400">OMR {{ number_format($b->total_revenue,3) }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <div class="w-12 h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden"><div class="h-full bg-amber-400 rounded-full" style="width:{{ $pct }}%"></div></div>
                            <span class="text-xs text-gray-500 w-7 text-right">{{ $pct }}%</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $d['topPerBrand'][$b->id] }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400 text-xs">No collection data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', initBrand);
document.addEventListener('livewire:navigated', initBrand);
function initBrand() {
    const dark=document.documentElement.classList.contains('dark');
    const text=dark?'#9ca3af':'#6b7280', grid=dark?'rgba(255,255,255,0.06)':'rgba(0,0,0,0.06)';
    const palette=['#f59e0b','#3b82f6','#10b981','#8b5cf6','#ef4444'];
    const labels=@json($d['brands']->pluck('name'));
    const data=@json($d['brands']->pluck('total_revenue'));

    const bc=document.getElementById('brandChart');
    if(bc){if(bc._c)bc._c.destroy();bc._c=new Chart(bc,{type:'bar',data:{labels,datasets:[{data,backgroundColor:labels.map((_,i)=>palette[i%palette.length]+'bb'),borderColor:labels.map((_,i)=>palette[i%palette.length]),borderWidth:2,borderRadius:6}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>'OMR '+c.parsed.y.toFixed(3)}}},scales:{x:{ticks:{color:text,font:{size:11}},grid:{display:false}},y:{ticks:{color:text,font:{size:10},callback:v=>'OMR '+v.toFixed(0)},grid:{color:grid},beginAtZero:true}}}});}

    const dc=document.getElementById('brandDonut');
    if(dc&&data.some(v=>v>0)){if(dc._c)dc._c.destroy();dc._c=new Chart(dc,{type:'doughnut',data:{labels,datasets:[{data,backgroundColor:labels.map((_,i)=>palette[i%palette.length]),borderWidth:0,hoverOffset:5}]},options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{position:'bottom',labels:{color:text,font:{size:10},boxWidth:10,padding:8}},tooltip:{callbacks:{label:c=>'OMR '+c.parsed.toFixed(3)}}}}}); }
}
document.addEventListener('livewire:updated',()=>setTimeout(initBrand,100));
</script>
</x-filament-panels::page>
