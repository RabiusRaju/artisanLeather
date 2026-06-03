<x-filament-panels::page>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@php $d = $this->getData(); @endphp

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4 shadow-sm"><p class="text-[10px] uppercase tracking-widest text-gray-500 mb-1">Actual Revenue (6 months)</p><p class="text-2xl font-bold text-blue-600 dark:text-blue-400 tabular-nums">OMR {{ number_format($d['totalActual'],3) }}</p></div>
    <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4 shadow-sm"><p class="text-[10px] uppercase tracking-widest text-gray-500 mb-1">Revenue Target (6 months)</p><p class="text-2xl font-bold text-amber-600 dark:text-amber-400 tabular-nums">OMR {{ number_format($d['totalTarget'],3) }}</p></div>
    <div class="rounded-xl border {{ $d['achievement']>=100?'border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20':'border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20' }} p-4 shadow-sm"><p class="text-[10px] uppercase tracking-widest text-gray-500 mb-1">Target Achievement</p><p class="text-2xl font-bold {{ $d['achievement']>=100?'text-green-600 dark:text-green-400':'text-orange-600 dark:text-orange-400' }}">{{ $d['achievement'] }}%</p></div>
</div>

<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 shadow-sm mb-4">
    <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1">Revenue: Actual vs Target + 3-Month Forecast</h3>
    <p class="text-xs text-gray-400 mb-4">Solid = Actual · Dashed = Target · Dotted = Forecast</p>
    <div style="position:relative;height:260px;"><canvas id="budgetChart"></canvas></div>
</div>

<div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm mb-4">
    <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Monthly Budget vs Actual</h3>
        <a href="/admin/compliance/budgets/create" class="text-xs text-amber-500 hover:underline">+ Set Budget →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-xs uppercase tracking-wider text-gray-500 bg-gray-50 dark:bg-gray-800">
                <tr><th class="px-4 py-2.5 text-left">Month</th><th class="px-4 py-2.5 text-right">Actual Rev.</th><th class="px-4 py-2.5 text-right">Target</th><th class="px-4 py-2.5 text-right">Gap</th><th class="px-4 py-2.5 text-right">Expenses</th><th class="px-4 py-2.5 text-right">Budget</th><th class="px-4 py-2.5 text-center">Status</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($d['months'] as $m)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                    <td class="px-4 py-3 font-medium">{{ $m['label'] }}</td>
                    <td class="px-4 py-3 text-right tabular-nums font-semibold text-green-600 dark:text-green-400">OMR {{ number_format($m['actual_revenue'],3) }}</td>
                    <td class="px-4 py-3 text-right tabular-nums text-amber-500">{{ $m['has_budget']?'OMR '.number_format($m['target_revenue'],3):'—' }}</td>
                    <td class="px-4 py-3 text-right tabular-nums text-xs {{ $m['revenue_gap']<=0?'text-green-600 dark:text-green-400':'text-red-500' }}">
                        @if($m['has_budget']) {{ $m['revenue_gap']<=0?'✅ +'.number_format(abs($m['revenue_gap']),3):'▼ '.number_format($m['revenue_gap'],3) }} @else — @endif
                    </td>
                    <td class="px-4 py-3 text-right tabular-nums text-red-500">OMR {{ number_format($m['actual_expense'],3) }}</td>
                    <td class="px-4 py-3 text-right tabular-nums text-gray-400">{{ $m['has_budget']?'OMR '.number_format($m['expense_budget'],3):'—' }}</td>
                    <td class="px-4 py-3 text-center text-xs">
                        @if(!$m['has_budget'])<span class="text-gray-400">No budget set</span>
                        @elseif($m['revenue_gap']<=0)<span class="text-green-600 font-medium">On Track ✅</span>
                        @else<span class="text-red-500 font-medium">Behind ⚠️</span>@endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="rounded-xl border border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20 p-4 shadow-sm">
    <h3 class="font-semibold text-purple-800 dark:text-purple-300 text-sm mb-3">🔮 3-Month Revenue Forecast (based on last 3 months average)</h3>
    <div class="grid grid-cols-3 gap-3">
        @foreach($d['forecast'] as $f)
        <div class="bg-white dark:bg-gray-900 rounded-lg p-3 border border-purple-200 dark:border-purple-800 text-center">
            <p class="text-xs text-gray-500 mb-1">{{ $f['label'] }}</p>
            <p class="font-bold text-purple-600 dark:text-purple-400 tabular-nums">OMR {{ number_format($f['revenue'],3) }}</p>
            <p class="text-[10px] text-gray-400">Est. expenses: OMR {{ number_format($f['expense'],3) }}</p>
        </div>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', initBudget);
document.addEventListener('livewire:navigated', initBudget);
function initBudget() {
    const dark=document.documentElement.classList.contains('dark');
    const text=dark?'#9ca3af':'#6b7280', grid=dark?'rgba(255,255,255,0.06)':'rgba(0,0,0,0.06)';
    const allLabels=[...@json(collect($d['months'])->pluck('label')),...@json(collect($d['forecast'])->pluck('label'))];
    const actual=[...@json(collect($d['months'])->pluck('actual_revenue')),...Array(3).fill(null)];
    const target=[...@json(collect($d['months'])->pluck('target_revenue')),...Array(3).fill(null)];
    const forecast=[...Array(6).fill(null),...@json(collect($d['forecast'])->pluck('revenue'))];
    const bc=document.getElementById('budgetChart');
    if(bc){if(bc._c)bc._c.destroy();bc._c=new Chart(bc,{type:'line',data:{labels:allLabels,datasets:[
        {label:'Actual Revenue',data:actual,borderColor:'#22c55e',backgroundColor:'rgba(34,197,94,0.1)',borderWidth:2.5,pointRadius:4,pointBackgroundColor:'#22c55e',fill:true,tension:0.3,spanGaps:false},
        {label:'Revenue Target',data:target,borderColor:'#f59e0b',backgroundColor:'transparent',borderWidth:2,borderDash:[6,3],pointRadius:3,tension:0.3,spanGaps:false},
        {label:'Forecast',data:forecast,borderColor:'#a855f7',backgroundColor:'rgba(168,85,247,0.08)',borderWidth:2,borderDash:[3,3],pointRadius:4,pointStyle:'diamond',fill:true,tension:0.3,spanGaps:false},
    ]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'top',labels:{color:text,font:{size:11}}},tooltip:{callbacks:{label:c=>c.dataset.label+': OMR '+(c.parsed.y||0).toFixed(3)}}},scales:{x:{ticks:{color:text,font:{size:10}},grid:{display:false}},y:{ticks:{color:text,font:{size:10},callback:v=>'OMR '+v.toFixed(0)},grid:{color:grid},beginAtZero:true}}}});}
}
document.addEventListener('livewire:updated',()=>setTimeout(initBudget,100));
</script>
</x-filament-panels::page>
