<x-filament-panels::page>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@php $d = $this->getData(); @endphp

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
    @foreach([
        ['label'=>'Total Customers','value'=>$d['total'],'icon'=>'👥','color'=>'text-blue-600 dark:text-blue-400','border'=>'border-blue-200 dark:border-blue-800','bg'=>'bg-blue-50 dark:bg-blue-900/20'],
        ['label'=>'VIP Customers','value'=>$d['vip'],'icon'=>'⭐','color'=>'text-amber-600 dark:text-amber-400','border'=>'border-amber-200 dark:border-amber-800','bg'=>'bg-amber-50 dark:bg-amber-900/20'],
        ['label'=>'New This Month','value'=>$d['newThisMonth'],'icon'=>'🆕','color'=>'text-green-600 dark:text-green-400','border'=>'border-green-200 dark:border-green-800','bg'=>'bg-green-50 dark:bg-green-900/20'],
        ['label'=>'Repeat Buyers','value'=>$d['repeat'],'icon'=>'🔄','color'=>'text-purple-600 dark:text-purple-400','border'=>'border-purple-200 dark:border-purple-800','bg'=>'bg-purple-50 dark:bg-purple-900/20'],
        ['label'=>'Inactive (60+ days)','value'=>$d['inactive'],'icon'=>'😴','color'=>'text-red-500 dark:text-red-400','border'=>'border-red-200 dark:border-red-800','bg'=>'bg-red-50 dark:bg-red-900/20'],
    ] as $k)
    <div class="rounded-xl border {{ $k['border'] }} {{ $k['bg'] }} p-4 shadow-sm">
        <div class="flex items-center gap-1.5 mb-1"><span>{{ $k['icon'] }}</span><p class="text-[10px] uppercase tracking-widest text-gray-500 font-medium leading-tight">{{ $k['label'] }}</p></div>
        <p class="text-2xl font-bold {{ $k['color'] }} tabular-nums">{{ $k['value'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
    {{-- 6-month trend --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">📈 New Customers & Orders — Last 6 Months</h3>
        <div style="position:relative;height:200px;"><canvas id="custTrend"></canvas></div>
    </div>

    {{-- Retention insight --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">🎯 Customer Segments</h3>
        <div class="space-y-4">
            @php
            $segments = [
                ['label'=>'VIP Customers','count'=>$d['vip'],'total'=>$d['total'],'color'=>'bg-amber-400','desc'=>'High value, loyal buyers'],
                ['label'=>'Repeat Buyers','count'=>$d['repeat'],'total'=>$d['total'],'color'=>'bg-purple-400','desc'=>'Ordered more than once'],
                ['label'=>'Inactive (60+ days)','count'=>$d['inactive'],'total'=>max($d['total'],1),'color'=>'bg-red-400','desc'=>'Need re-engagement'],
                ['label'=>'New This Month','count'=>$d['newThisMonth'],'total'=>max($d['total'],1),'color'=>'bg-green-400','desc'=>'Recently joined'],
            ];
            @endphp
            @foreach($segments as $seg)
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $seg['label'] }}</span>
                    <span class="text-gray-500">{{ $seg['count'] }} <span class="text-gray-400">({{ $seg['total']>0?round(($seg['count']/$seg['total'])*100):0 }}%)</span></span>
                </div>
                <div class="h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full {{ $seg['color'] }} rounded-full transition-all" style="width:{{ $seg['total']>0?min(($seg['count']/$seg['total'])*100,100):0 }}%"></div>
                </div>
                <p class="text-[10px] text-gray-400 mt-0.5">{{ $seg['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Top customers table --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
    <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">🏆 Top 10 Customers by Lifetime Spend</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-xs uppercase tracking-wider text-gray-500 bg-gray-50 dark:bg-gray-800">
                <tr><th class="px-4 py-2.5 text-left">#</th><th class="px-4 py-2.5 text-left">Customer</th><th class="px-4 py-2.5 text-center">Orders</th><th class="px-4 py-2.5 text-right">Lifetime Spend</th><th class="px-4 py-2.5 text-left">Last Order</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($d['topCustomers'] as $i => $c)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $i+1 }}</td>
                    <td class="px-4 py-3"><div class="font-medium">{{ $c->first_name }} {{ $c->last_name }}</div><div class="text-xs text-gray-400">{{ $c->email }}</div></td>
                    <td class="px-4 py-3 text-center"><span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400">{{ $c->order_count }} orders</span></td>
                    <td class="px-4 py-3 text-right font-bold tabular-nums text-amber-600 dark:text-amber-400">OMR {{ number_format($c->lifetime_spend,3) }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ \Carbon\Carbon::parse($c->last_order)->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 text-xs">No customer order data yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', initCA);
document.addEventListener('livewire:navigated', initCA);
function initCA() {
    const dark=document.documentElement.classList.contains('dark');
    const text=dark?'#9ca3af':'#6b7280',grid=dark?'rgba(255,255,255,0.06)':'rgba(0,0,0,0.06)';
    const ct=document.getElementById('custTrend');
    if(ct){if(ct._c)ct._c.destroy();ct._c=new Chart(ct,{type:'bar',data:{labels:@json(collect($d['monthly'])->pluck('month')),datasets:[{label:'New Customers',data:@json(collect($d['monthly'])->pluck('new')),backgroundColor:'rgba(59,130,246,0.7)',borderRadius:4,yAxisID:'y'},{label:'Orders',data:@json(collect($d['monthly'])->pluck('orders')),backgroundColor:'rgba(245,158,11,0.7)',borderRadius:4,yAxisID:'y'}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'top',labels:{color:text,font:{size:11}}}},scales:{x:{ticks:{color:text,font:{size:10}},grid:{display:false}},y:{ticks:{color:text,font:{size:10}},grid:{color:grid},beginAtZero:true}}}}); }
}
document.addEventListener('livewire:updated',()=>setTimeout(initCA,100));
</script>
</x-filament-panels::page>
