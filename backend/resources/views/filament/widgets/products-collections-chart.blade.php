@php
$d          = $this->getData();
$prodLabels = $d['topProducts']->pluck('product_name')
                ->map(fn($n) => mb_strlen($n) > 25 ? mb_substr($n,0,25).'…' : $n)
                ->values()->toArray();
$prodValues = $d['topProducts']->pluck('revenue')->values()->toArray();
$collLabels = $d['collections']->pluck('name')->toArray();
$collValues = $d['collections']->pluck('revenue')->toArray();
$weekLabels = collect($d['weeks'])->pluck('label')->toArray();
$weekValues = collect($d['weeks'])->pluck('revenue')->toArray();
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- 1. Top Products Horizontal Bar --}}
    <div class="lg:col-span-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">🏆 Top Products — This Month</h3>
                <p class="text-xs text-gray-400 mt-0.5">Revenue generated per product</p>
            </div>
            <a href="/admin/product-performance" class="text-xs text-amber-500 hover:underline">Full report →</a>
        </div>
        @if($d['topProducts']->count() > 0)
        <div wire:ignore
             x-data="{}"
             x-init="$nextTick(() => window.AlChart && window.AlChart.topProducts($el))"
             style="position:relative;height:240px;"
             data-labels='@json($prodLabels)'
             data-values='@json($prodValues)'>
            <canvas></canvas>
        </div>
        @else
        <div class="flex flex-col items-center justify-center h-48 text-gray-400">
            <span class="text-4xl mb-3">📦</span>
            <p class="text-sm">No product sales this month yet</p>
            <a href="/admin/orders/create" class="mt-3 text-xs text-amber-500 hover:underline">+ Create first order</a>
        </div>
        @endif
    </div>

    {{-- 2. Collections + Weekly Trend --}}
    <div class="space-y-4">

        {{-- Collections Donut --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1">🏷️ Collections Revenue</h3>
            <p class="text-xs text-gray-400 mb-3">All time by collection</p>
            @if($d['collections']->where('revenue','>',0)->count() > 0)
            <div wire:ignore
                 x-data="{}"
                 x-init="$nextTick(() => window.AlChart && window.AlChart.collections($el))"
                 style="position:relative;height:160px;"
                 data-labels='@json($collLabels)'
                 data-values='@json($collValues)'
                 data-colors='["#f59e0b","#8b5cf6","#10b981","#ef4444"]'>
                <canvas></canvas>
            </div>
            <div class="mt-3 space-y-1">
                @php $palette = ['#f59e0b','#8b5cf6','#10b981','#ef4444']; @endphp
                @foreach($d['collections'] as $i => $c)
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-1.5 flex-1 min-w-0">
                        <div class="w-2 h-2 rounded-full shrink-0" style="background:{{ $palette[$i % 4] }}"></div>
                        <span class="text-gray-600 dark:text-gray-400 truncate">{{ $c->name }}</span>
                    </div>
                    <span class="font-semibold tabular-nums ml-2 shrink-0">OMR {{ number_format($c->revenue,0) }}</span>
                </div>
                @endforeach
            </div>
            @else
            <div class="flex flex-col items-center justify-center h-28 text-gray-400">
                <p class="text-xs">No collection sales yet</p>
            </div>
            @endif
        </div>

        {{-- 4-Week Trend Bar --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1">📅 Weekly Trend</h3>
            <p class="text-xs text-gray-400 mb-3">Last 4 weeks revenue</p>
            <div wire:ignore
                 x-data="{}"
                 x-init="$nextTick(() => window.AlChart && window.AlChart.weeklyTrend($el))"
                 style="position:relative;height:90px;"
                 data-labels='@json($weekLabels)'
                 data-values='@json($weekValues)'>
                <canvas></canvas>
            </div>
        </div>

    </div>

</div>
