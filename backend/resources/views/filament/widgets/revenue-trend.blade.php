@php $d = $this->getData(); @endphp

<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 shadow-sm">

    <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
        <div>
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">📊 Revenue Trend — Last 30 Days</h3>
            <p class="text-xs text-gray-400 mt-0.5">Gold bars = above daily average · Green line = daily average</p>
        </div>
        <div class="flex flex-wrap gap-5 text-right">
            <div>
                <p class="text-[10px] uppercase tracking-wider text-gray-400">30-Day Total</p>
                <p class="font-bold text-amber-600 dark:text-amber-400 tabular-nums">OMR {{ number_format($d['totalPeriod'],3) }}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-wider text-gray-400">Daily Avg</p>
                <p class="font-semibold text-gray-700 dark:text-gray-300 tabular-nums">OMR {{ number_format($d['avgDay'],3) }}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-wider text-gray-400">🏆 Peak Day</p>
                <p class="font-semibold text-green-600 dark:text-green-400 text-xs">{{ $d['peakDay'] }} — OMR {{ number_format($d['peakVal'],3) }}</p>
            </div>
        </div>
    </div>

    <div data-alchart="revTrend"
         style="position:relative;height:210px;"
         data-days='@json($d["days"])'
         data-rev='@json($d["rev"])'
         data-avg='{{ round($d["avgDay"], 3) }}'>
        <canvas></canvas>
    </div>

</div>
