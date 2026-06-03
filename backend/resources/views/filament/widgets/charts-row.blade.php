@php
$d            = $this->getData();
$totalRev     = $d['websiteRev'] + $d['customRev'] + $d['otherRev'];
$totalOrders  = array_sum($d['orderCounts']);
$totalPay     = array_sum($d['payRevenue']);
$statusColors = ['pending'=>'#f59e0b','confirmed'=>'#3b82f6','processing'=>'#8b5cf6','shipped'=>'#10b981','delivered'=>'#22c55e','cancelled'=>'#ef4444'];
$payColors    = ['cod'=>'#f59e0b','bank'=>'#3b82f6','whatsapp'=>'#22c55e'];
$payLabels    = ['cod'=>'Cash on Del.','bank'=>'Bank Transfer','whatsapp'=>'WhatsApp'];
$statusLabels = array_map(fn($s) => ucfirst(str_replace('_',' ',$s)), $d['statuses']);
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">

    {{-- 1. Revenue Sources --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 shadow-sm">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1">💰 Revenue Sources</h3>
        <p class="text-xs text-gray-400 mb-4">This month — by channel</p>
        @if($totalRev > 0)
        <div class="flex items-center gap-4">
            <div data-alchart="revSources"
                 style="position:relative;height:150px;width:150px;flex-shrink:0;"
                 data-labels='["Website Orders","Custom Orders","Other"]'
                 data-values='@json([$d["websiteRev"],$d["customRev"],$d["otherRev"]])'
                 data-colors='["#22c55e","#f59e0b","#3b82f6"]'>
                <canvas></canvas>
            </div>
            <div class="space-y-2 flex-1">
                @foreach([['Website Orders','#22c55e',$d['websiteRev']],['Custom Orders','#f59e0b',$d['customRev']],['Other','#3b82f6',$d['otherRev']]] as [$label,$color,$val])
                @if($val > 0)
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full" style="background:{{ $color }}"></div>
                        <span class="text-gray-600 dark:text-gray-400">{{ $label }}</span>
                    </div>
                    <span class="font-semibold tabular-nums">OMR {{ number_format($val,3) }}</span>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @else
        <div class="flex flex-col items-center justify-center h-36 text-gray-400">
            <span class="text-3xl mb-2">📊</span>
            <p class="text-xs">No sales this month yet</p>
        </div>
        @endif
    </div>

    {{-- 2. Order Status --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 shadow-sm">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1">🛒 Order Status</h3>
        <p class="text-xs text-gray-400 mb-4">All orders — current distribution</p>
        @if($totalOrders > 0)
        <div class="flex items-center gap-4">
            <div data-alchart="orderStatus"
                 style="position:relative;height:150px;width:150px;flex-shrink:0;"
                 data-labels='@json($statusLabels)'
                 data-values='@json($d["orderCounts"])'
                 data-colors='["#f59e0b","#3b82f6","#8b5cf6","#10b981","#22c55e","#ef4444"]'>
                <canvas></canvas>
            </div>
            <div class="space-y-1.5 flex-1">
                @foreach($d['statuses'] as $i => $s)
                @if($d['orderCounts'][$i] > 0)
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full" style="background:{{ $statusColors[$s] ?? '#9ca3af' }}"></div>
                        <span class="text-gray-600 dark:text-gray-400 capitalize">{{ str_replace('_',' ',$s) }}</span>
                    </div>
                    <span class="font-semibold">{{ $d['orderCounts'][$i] }}</span>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @else
        <div class="flex flex-col items-center justify-center h-36 text-gray-400">
            <span class="text-3xl mb-2">📦</span><p class="text-xs">No orders yet</p>
        </div>
        @endif
    </div>

    {{-- 3. Payment Methods --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 shadow-sm">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1">💳 Payment Methods</h3>
        <p class="text-xs text-gray-400 mb-4">Revenue by payment channel</p>
        @if($totalPay > 0)
        <div class="flex items-center gap-4">
            <div data-alchart="payMethods"
                 style="position:relative;height:150px;width:150px;flex-shrink:0;"
                 data-labels='["Cash on Del.","Bank Transfer","WhatsApp"]'
                 data-values='@json($d["payRevenue"])'
                 data-colors='["#f59e0b","#3b82f6","#22c55e"]'>
                <canvas></canvas>
            </div>
            <div class="space-y-2 flex-1">
                @foreach($d['payMethods'] as $i => $m)
                @if($d['payRevenue'][$i] > 0)
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-1.5">
                        <div class="w-2.5 h-2.5 rounded-full" style="background:{{ $payColors[$m] ?? '#9ca3af' }}"></div>
                        <span class="text-gray-600 dark:text-gray-400">{{ $payLabels[$m] ?? $m }}</span>
                    </div>
                    <span class="font-semibold tabular-nums">{{ $totalPay > 0 ? round(($d['payRevenue'][$i]/$totalPay)*100,1) : 0 }}%</span>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @else
        <div class="flex flex-col items-center justify-center h-36 text-gray-400">
            <span class="text-3xl mb-2">💳</span><p class="text-xs">No payment data yet</p>
        </div>
        @endif
    </div>

</div>
