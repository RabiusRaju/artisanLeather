@php
$d          = $this->getData();
$prodLabels = $d['thisMonth']->pluck('product_name')
                ->map(fn($n) => mb_strlen($n) > 22 ? mb_substr($n,0,22).'…' : $n)
                ->values()->toArray();
$prodValues = $d['thisMonth']->pluck('revenue')->map(fn($v) => round((float)$v,3))->values()->toArray();
$collLabels = $d['byCollection']->pluck('name')->toArray();
$collValues = $d['byCollection']->pluck('revenue')->map(fn($v) => round((float)$v,3))->toArray();
$weekLabels = collect($d['weeks'])->pluck('label')->toArray();
$weekValues = collect($d['weeks'])->pluck('revenue')->toArray();
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Left 2/3: Top Products bar + Weekly Trend --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Top Products Horizontal Bar --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm">🏆 Top Products — This Month</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Revenue by product (OMR)</p>
                </div>
                <a href="/admin/product-performance" class="text-xs text-amber-500 hover:underline">Full report →</a>
            </div>
            @if($d['thisMonth']->count() > 0)
            <div data-alchart="topProducts"
                 style="position:relative;height:{{ max(160, $d['thisMonth']->count() * 42) }}px;"
                 data-labels='@json($prodLabels)'
                 data-values='@json($prodValues)'>
                <canvas></canvas>
            </div>
            @else
            <div class="flex flex-col items-center justify-center h-32 text-gray-400">
                <span class="text-3xl mb-2">📦</span>
                <p class="text-xs">No product sales this month yet</p>
            </div>
            @endif
        </div>

        {{-- 4-Week Revenue Trend --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1">📅 Weekly Revenue Trend</h3>
            <p class="text-xs text-gray-400 mb-3">Last 4 weeks</p>
            <div data-alchart="weeklyTrend"
                 style="position:relative;height:100px;"
                 data-labels='@json($weekLabels)'
                 data-values='@json($weekValues)'>
                <canvas></canvas>
            </div>
        </div>

    </div>

    {{-- Right 1/3: Collections Donut --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">🏷️ Collections — This Month</h3>
                <p class="text-xs text-gray-400 mt-0.5">Revenue by collection</p>
            </div>
            <a href="/admin/sales-by-brand" class="text-xs text-amber-500 hover:underline">Details →</a>
        </div>

        @if($d['byCollection']->where('revenue','>',0)->count() > 0)
        <div data-alchart="collections"
             style="position:relative;height:200px;"
             data-labels='@json($collLabels)'
             data-values='@json($collValues)'
             data-colors='["#f59e0b","#8b5cf6","#10b981","#ef4444","#3b82f6"]'>
            <canvas></canvas>
        </div>

        <div class="mt-4 space-y-2">
            @php $palette = ['#f59e0b','#8b5cf6','#10b981','#ef4444','#3b82f6']; @endphp
            @foreach($d['byCollection'] as $i => $c)
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <div class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $palette[$i % 5] }}"></div>
                    <span class="text-gray-600 dark:text-gray-400 truncate">{{ $c->name }}</span>
                </div>
                <span class="font-semibold tabular-nums text-amber-600 dark:text-amber-400 ml-2 shrink-0">
                    OMR {{ number_format($c->revenue,3) }}
                </span>
            </div>
            @endforeach
        </div>
        @else
        <div class="flex flex-col items-center justify-center h-48 text-gray-400">
            <span class="text-3xl mb-2">🏷️</span>
            <p class="text-xs">No collection sales this month</p>
        </div>
        @endif
    </div>

</div>
