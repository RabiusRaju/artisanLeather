<x-filament-panels::page>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@php $d = $this->getData(); @endphp

<div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-6">
    <div class="rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-4 col-span-2 md:col-span-1"><p class="text-[10px] uppercase tracking-widest text-gray-500 font-medium mb-1">{{ $d['now']->year }} Total Revenue</p><p class="text-2xl font-bold text-green-600 dark:text-green-400 tabular-nums">OMR {{ number_format($d['totalYear'],3) }}</p></div>
    <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4"><p class="text-[10px] uppercase tracking-widest text-gray-500 font-medium mb-1">Monthly Average</p><p class="text-2xl font-bold text-amber-600 dark:text-amber-400 tabular-nums">OMR {{ number_format($d['avgMonth'],3) }}</p></div>
    <div class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4"><p class="text-[10px] uppercase tracking-widest text-gray-500 font-medium mb-1">Best Month</p><p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $d['bestMonth']['month'] ?? '—' }}</p><p class="text-xs text-gray-400 mt-0.5">OMR {{ number_format($d['bestMonth']['revenue']??0,3) }}</p></div>
</div>

<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 shadow-sm mb-4">
    <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1">📅 Monthly Revenue — {{ $d['now']->year }} vs {{ $d['now']->year - 1 }}</h3>
    <p class="text-xs text-gray-400 mb-4">Identify peak seasons (Ramadan, Eid, National Day) and plan ahead</p>
    <div style="position:relative;height:260px;"><canvas id="seasonChart"></canvas></div>
</div>

<div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
    <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Monthly Breakdown — {{ $d['now']->year }}</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-xs uppercase tracking-wider text-gray-500 bg-gray-50 dark:bg-gray-800">
                <tr><th class="px-4 py-2.5 text-left">Month</th><th class="px-4 py-2.5 text-right">{{ $d['now']->year }} Revenue</th><th class="px-4 py-2.5 text-right">Orders</th><th class="px-4 py-2.5 text-right">{{ $d['now']->year - 1 }} Revenue</th><th class="px-4 py-2.5 text-right">Growth</th><th class="px-4 py-2.5 text-left">Performance</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($d['monthStats'] as $ms)
                @php $isBest = ($ms['month'] === ($d['bestMonth']['month']??'')) @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 {{ $isBest?'bg-amber-50/50 dark:bg-amber-900/10':'' }}">
                    <td class="px-4 py-3 font-medium {{ $isBest?'text-amber-600 dark:text-amber-400':'' }}">{{ $ms['month'] }} {{ $isBest?'🏆':'' }}</td>
                    <td class="px-4 py-3 text-right tabular-nums font-semibold">OMR {{ number_format($ms['revenue'],3) }}</td>
                    <td class="px-4 py-3 text-right text-gray-500">{{ $ms['orders'] }}</td>
                    <td class="px-4 py-3 text-right text-gray-400 tabular-nums">OMR {{ number_format($ms['last_year'],3) }}</td>
                    <td class="px-4 py-3 text-right">
                        @if($ms['growth'] !== null)
                        <span class="text-xs font-medium {{ $ms['growth']>=0?'text-green-600 dark:text-green-400':'text-red-500' }}">
                            {{ $ms['growth']>=0?'↑':'↓' }} {{ abs($ms['growth']) }}%
                        </span>
                        @else<span class="text-xs text-gray-300">—</span>@endif
                    </td>
                    <td class="px-4 py-3">
                        @php $maxRev = max(collect($d['monthStats'])->max('revenue'),1); $pct=($ms['revenue']/$maxRev)*100; @endphp
                        <div class="flex items-center gap-2">
                            <div class="w-20 h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                <div class="h-full {{ $pct>70?'bg-green-400':($pct>30?'bg-amber-400':'bg-gray-300 dark:bg-gray-600') }} rounded-full" style="width:{{ $pct }}%"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', initSeasonal);
document.addEventListener('livewire:navigated', initSeasonal);
function initSeasonal() {
    const dark=document.documentElement.classList.contains('dark');
    const text=dark?'#9ca3af':'#6b7280', grid=dark?'rgba(255,255,255,0.06)':'rgba(0,0,0,0.06)';
    const sc=document.getElementById('seasonChart');
    if(sc){if(sc._c)sc._c.destroy();sc._c=new Chart(sc,{type:'bar',data:{labels:@json($d['months']),datasets:[{label:'{{ $d["now"]->year }}',data:@json($d['currentYear']),backgroundColor:'rgba(245,158,11,0.8)',borderColor:'#f59e0b',borderWidth:2,borderRadius:5},{label:'{{ $d["now"]->year - 1 }}',data:@json($d['lastYear']),backgroundColor:'rgba(156,163,175,0.4)',borderColor:'#9ca3af',borderWidth:1.5,borderRadius:5}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'top',labels:{color:text,font:{size:11}}},tooltip:{callbacks:{label:c=>c.dataset.label+': OMR '+c.parsed.y.toFixed(3)}}},scales:{x:{ticks:{color:text,font:{size:11}},grid:{display:false}},y:{ticks:{color:text,font:{size:10},callback:v=>'OMR '+v.toFixed(0)},grid:{color:grid},beginAtZero:true}}}});}
}
document.addEventListener('livewire:updated',()=>setTimeout(initSeasonal,100));
</script>
</x-filament-panels::page>
