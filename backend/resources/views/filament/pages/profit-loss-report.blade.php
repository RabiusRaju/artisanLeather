<x-filament-panels::page>

{{-- Chart.js from CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

@php $d = $this->getReportData(); @endphp

{{-- ── Period Selector ─────────────────────────────────────────────────── --}}
<div class="mb-6 space-y-3">
    <div class="flex gap-2 overflow-x-auto pb-1 -mx-1 px-1 scrollbar-none">
        @foreach(['this_month'=>'This Month','last_month'=>'Last Month','this_quarter'=>'Quarter','this_year'=>'This Year','last_year'=>'Last Year'] as $val=>$label)
            <button wire:click="$set('period','{{ $val }}')"
                class="flex-shrink-0 px-4 py-2 text-xs font-semibold tracking-wider uppercase rounded-md transition-all whitespace-nowrap
                    {{ $period===$val ? 'bg-amber-500 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-amber-100 dark:hover:bg-gray-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
        <span class="text-xs text-gray-500 font-medium whitespace-nowrap">Custom range:</span>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <input type="date" wire:model.live="dateFrom" class="flex-1 sm:w-36 text-xs border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 dark:text-gray-300 focus:ring-2 focus:ring-amber-400 outline-none">
            <span class="text-gray-400">→</span>
            <input type="date" wire:model.live="dateTo" class="flex-1 sm:w-36 text-xs border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-900 dark:text-gray-300 focus:ring-2 focus:ring-amber-400 outline-none">
        </div>
        <span class="text-xs text-gray-400 hidden sm:inline">
            {{ \Carbon\Carbon::parse($d['from'])->format('d M Y') }} – {{ \Carbon\Carbon::parse($d['to'])->format('d M Y') }}
        </span>
    </div>
</div>

{{-- ── KPI Cards: 2 cols → 4 cols ─────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    @php
    $kpis = [
        ['label'=>'Revenue',       'value'=>number_format($d['totalRevenue'],3), 'icon'=>'💰', 'color'=>'text-green-600 dark:text-green-400',  'bg'=>'from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-900/10',  'border'=>'border-green-200 dark:border-green-800'],
        ['label'=>'Purchases',     'value'=>number_format($d['purchaseCost'],3), 'icon'=>'📦', 'color'=>'text-yellow-600 dark:text-yellow-400','bg'=>'from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-900/10','border'=>'border-yellow-200 dark:border-yellow-800'],
        ['label'=>'Expenses',      'value'=>number_format($d['totalExpenses'],3),'icon'=>'💸', 'color'=>'text-red-600 dark:text-red-400',      'bg'=>'from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-900/10',          'border'=>'border-red-200 dark:border-red-800'],
        ['label'=>'Net Profit',    'value'=>number_format($d['netProfit'],3),    'icon'=>$d['netProfit']>=0?'✅':'⚠️',
         'color'=>$d['netProfit']>=0?'text-emerald-700 dark:text-emerald-400':'text-red-600',
         'bg'   =>$d['netProfit']>=0?'from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-900/10':'from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-900/10',
         'border'=>$d['netProfit']>=0?'border-emerald-200 dark:border-emerald-800':'border-red-200 dark:border-red-800'],
    ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="rounded-xl border {{ $kpi['border'] }} bg-gradient-to-br {{ $kpi['bg'] }} p-4 shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] uppercase tracking-widest font-semibold text-gray-500 dark:text-gray-400">{{ $kpi['label'] }}</span>
            <span class="text-lg leading-none">{{ $kpi['icon'] }}</span>
        </div>
        <p class="text-lg sm:text-2xl font-bold {{ $kpi['color'] }} tabular-nums leading-tight break-all">OMR {{ $kpi['value'] }}</p>
    </div>
    @endforeach
</div>

{{-- ── Charts Row ────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    {{-- Bar Chart: Revenue vs Costs vs Profit --}}
    <div class="md:col-span-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Financial Overview</h3>
                <p class="text-xs text-gray-400 mt-0.5">Revenue · Purchases · Expenses · Net Profit</p>
            </div>
        </div>
        <div style="position:relative;height:220px;">
            <canvas id="overviewChart"></canvas>
        </div>
    </div>

    {{-- Donut Chart: Expense Breakdown --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <div class="mb-4">
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Expense Breakdown</h3>
            <p class="text-xs text-gray-400 mt-0.5">By category</p>
        </div>
        @if($d['expensesByCategory']->count() > 0)
        <div style="position:relative;height:180px;">
            <canvas id="expenseDonut"></canvas>
        </div>
        <div class="mt-3 space-y-1 max-h-24 overflow-y-auto">
            @foreach($d['expensesByCategory'] as $cat => $amt)
            <div class="flex justify-between text-xs">
                <span class="text-gray-500 dark:text-gray-400 truncate mr-2">{{ $cat }}</span>
                <span class="font-medium text-gray-700 dark:text-gray-300 tabular-nums flex-shrink-0">OMR {{ number_format($amt,3) }}</span>
            </div>
            @endforeach
        </div>
        @else
        <div class="flex flex-col items-center justify-center h-40 text-gray-400">
            <span class="text-3xl mb-2">📊</span>
            <p class="text-xs">No expenses in this period</p>
        </div>
        @endif
    </div>

</div>

{{-- ── Revenue Waterfall Chart ─────────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 shadow-sm mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Revenue Sources</h3>
            <p class="text-xs text-gray-400 mt-0.5">Website vs Custom vs Other</p>
        </div>
    </div>
    <div style="position:relative;height:160px;">
        <canvas id="revenueChart"></canvas>
    </div>
</div>

{{-- ── P&L Statement Table ─────────────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm mb-6">
    <div class="px-5 py-4 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <div>
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Profit & Loss Statement</h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($d['from'])->format('d M Y') }} – {{ \Carbon\Carbon::parse($d['to'])->format('d M Y') }}</p>
        </div>
        <span class="text-xs px-3 py-1 rounded-full font-semibold {{ $d['netProfit']>=0?'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400':'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' }}">
            {{ $d['netProfit']>=0?'Profitable':'Loss' }}
        </span>
    </div>

    <table class="w-full text-sm">
        {{-- Revenue section --}}
        <thead>
            <tr class="bg-green-50 dark:bg-green-900/20">
                <th colspan="2" class="px-5 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-green-700 dark:text-green-400">Income / Revenue</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach(['Website Orders'=>$d['websiteRevenue'],'Custom / Bespoke Orders'=>$d['customRevenue'],'Other Income (Wholesale etc.)'=>$d['otherRevenue']] as $label=>$val)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                <td class="px-5 py-3 text-gray-600 dark:text-gray-400 pl-8">{{ $label }}</td>
                <td class="px-5 py-3 text-right font-medium text-green-600 dark:text-green-400 tabular-nums">OMR {{ number_format($val,3) }}</td>
            </tr>
            @endforeach
            <tr class="bg-green-50 dark:bg-green-900/20 font-semibold">
                <td class="px-5 py-3 text-green-800 dark:text-green-300">Total Revenue</td>
                <td class="px-5 py-3 text-right text-green-700 dark:text-green-300 tabular-nums text-base">OMR {{ number_format($d['totalRevenue'],3) }}</td>
            </tr>
        </tbody>

        {{-- COGS section --}}
        <thead>
            <tr class="bg-yellow-50 dark:bg-yellow-900/20">
                <th colspan="2" class="px-5 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-yellow-700 dark:text-yellow-400">Cost of Goods Sold</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                <td class="px-5 py-3 text-gray-600 dark:text-gray-400 pl-8">Purchases received in period</td>
                <td class="px-5 py-3 text-right font-medium text-yellow-600 dark:text-yellow-400 tabular-nums">OMR {{ number_format($d['purchaseCost'],3) }}</td>
            </tr>
            <tr class="font-semibold {{ $d['grossProfit']>=0?'bg-emerald-50 dark:bg-emerald-900/20':'bg-red-50 dark:bg-red-900/20' }}">
                <td class="px-5 py-3 {{ $d['grossProfit']>=0?'text-emerald-800 dark:text-emerald-300':'text-red-700' }}">
                    Gross Profit
                    <span class="ml-2 text-xs font-normal opacity-70">({{ $d['grossMargin'] }}% margin)</span>
                </td>
                <td class="px-5 py-3 text-right tabular-nums text-base {{ $d['grossProfit']>=0?'text-emerald-700 dark:text-emerald-300':'text-red-600' }}">
                    OMR {{ number_format($d['grossProfit'],3) }}
                </td>
            </tr>
        </tbody>

        {{-- Expenses section --}}
        <thead>
            <tr class="bg-red-50 dark:bg-red-900/20">
                <th colspan="2" class="px-5 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-red-700 dark:text-red-400">Operating Expenses</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($d['expensesByCategory'] as $catName => $amount)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                <td class="px-5 py-3 text-gray-600 dark:text-gray-400 pl-8">{{ $catName }}</td>
                <td class="px-5 py-3 text-right font-medium text-red-500 tabular-nums">OMR {{ number_format($amount,3) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="px-5 py-4 text-center text-gray-400 text-xs italic pl-8">No expenses recorded in this period</td>
            </tr>
            @endforelse
            <tr class="bg-red-50 dark:bg-red-900/20 font-semibold">
                <td class="px-5 py-3 text-red-800 dark:text-red-300">Total Expenses</td>
                <td class="px-5 py-3 text-right text-red-700 dark:text-red-300 tabular-nums text-base">OMR {{ number_format($d['totalExpenses'],3) }}</td>
            </tr>
        </tbody>

        {{-- NET PROFIT footer --}}
        <tfoot>
            <tr class="border-t-4 {{ $d['netProfit']>=0?'border-emerald-400 dark:border-emerald-600 bg-emerald-50 dark:bg-emerald-900/30':'border-red-400 dark:border-red-600 bg-red-50 dark:bg-red-900/30' }}">
                <td class="px-5 py-4 font-bold text-lg {{ $d['netProfit']>=0?'text-emerald-800 dark:text-emerald-300':'text-red-800 dark:text-red-300' }}">
                    NET PROFIT / (LOSS)
                    <span class="block text-xs font-normal opacity-70 mt-0.5">Net margin: {{ $d['netMargin'] }}%</span>
                </td>
                <td class="px-5 py-4 text-right font-bold text-2xl tabular-nums {{ $d['netProfit']>=0?'text-emerald-700 dark:text-emerald-300':'text-red-700 dark:text-red-400' }}">
                    OMR {{ number_format($d['netProfit'],3) }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- Chart.js scripts --}}
<script>
document.addEventListener('livewire:navigated', initCharts);
document.addEventListener('DOMContentLoaded', initCharts);

function initCharts() {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor  = isDark ? '#9ca3af' : '#6b7280';
    const gridColor  = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';

    // ── 1. Financial Overview Bar Chart ─────────────────────────────────
    const overviewCtx = document.getElementById('overviewChart');
    if (overviewCtx) {
        if (overviewCtx._chartInstance) overviewCtx._chartInstance.destroy();
        overviewCtx._chartInstance = new Chart(overviewCtx, {
            type: 'bar',
            data: {
                labels: ['Revenue', 'Purchases', 'Expenses', 'Net Profit'],
                datasets: [{
                    data: [
                        {{ $d['totalRevenue'] }},
                        {{ $d['purchaseCost'] }},
                        {{ $d['totalExpenses'] }},
                        {{ abs($d['netProfit']) }},
                    ],
                    backgroundColor: [
                        'rgba(34,197,94,0.8)',
                        'rgba(234,179,8,0.8)',
                        'rgba(239,68,68,0.8)',
                        '{{ $d['netProfit'] >= 0 ? "rgba(16,185,129,0.85)" : "rgba(239,68,68,0.85)" }}',
                    ],
                    borderColor: [
                        'rgba(34,197,94,1)',
                        'rgba(234,179,8,1)',
                        'rgba(239,68,68,1)',
                        '{{ $d['netProfit'] >= 0 ? "rgba(16,185,129,1)" : "rgba(239,68,68,1)" }}',
                    ],
                    borderWidth: 2,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => 'OMR ' + ctx.parsed.y.toFixed(3),
                        }
                    }
                },
                scales: {
                    x: { ticks: { color: textColor, font: { size: 11 } }, grid: { display: false } },
                    y: {
                        ticks: { color: textColor, font: { size: 10 }, callback: v => 'OMR ' + v.toFixed(0) },
                        grid: { color: gridColor },
                        beginAtZero: true,
                    }
                }
            }
        });
    }

    // ── 2. Expense Donut ─────────────────────────────────────────────────
    const donutCtx = document.getElementById('expenseDonut');
    @if($d['expensesByCategory']->count() > 0)
    if (donutCtx) {
        if (donutCtx._chartInstance) donutCtx._chartInstance.destroy();
        const palette = ['#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899','#10b981','#f97316','#6366f1','#14b8a6','#a855f7','#64748b','#84cc16'];
        donutCtx._chartInstance = new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: @json($d['expensesByCategory']->keys()),
                datasets: [{
                    data: @json($d['expensesByCategory']->values()),
                    backgroundColor: palette.slice(0, {{ $d['expensesByCategory']->count() }}),
                    borderWidth: 0,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ctx.label + ': OMR ' + ctx.parsed.toFixed(3) } }
                }
            }
        });
    }
    @endif

    // ── 3. Revenue Sources Horizontal Bar ───────────────────────────────
    const revCtx = document.getElementById('revenueChart');
    if (revCtx) {
        if (revCtx._chartInstance) revCtx._chartInstance.destroy();
        revCtx._chartInstance = new Chart(revCtx, {
            type: 'bar',
            data: {
                labels: ['Website Orders', 'Custom Orders', 'Other Income'],
                datasets: [{
                    data: [{{ $d['websiteRevenue'] }}, {{ $d['customRevenue'] }}, {{ $d['otherRevenue'] }}],
                    backgroundColor: ['rgba(34,197,94,0.75)','rgba(16,185,129,0.75)','rgba(59,130,246,0.75)'],
                    borderColor:     ['rgba(34,197,94,1)','rgba(16,185,129,1)','rgba(59,130,246,1)'],
                    borderWidth: 2, borderRadius: 5,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => 'OMR ' + ctx.parsed.x.toFixed(3) } }
                },
                scales: {
                    x: {
                        ticks: { color: textColor, font: { size: 10 }, callback: v => 'OMR '+v.toFixed(0) },
                        grid: { color: gridColor }, beginAtZero: true,
                    },
                    y: { ticks: { color: textColor, font: { size: 11 } }, grid: { display: false } }
                }
            }
        });
    }
}

// Re-init charts when Livewire updates the page
document.addEventListener('livewire:updated', () => setTimeout(initCharts, 100));
</script>

</x-filament-panels::page>
