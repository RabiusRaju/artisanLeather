<x-filament-panels::page>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@php $d = $this->getData(); @endphp

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    @php
    $kpis = [
        ['label'=>'Revenue This Month','value'=>'OMR '.number_format($d['revCM'],3),'sub'=>($d['growth']>=0?'↑ ':'↓ ').abs($d['growth']).'% vs last month','icon'=>'💰','color'=>'text-green-600 dark:text-green-400','border'=>'border-green-200 dark:border-green-800','bg'=>'bg-green-50 dark:bg-green-900/20'],
        ['label'=>'Orders This Month','value'=>$d['ordCM'].' orders','sub'=>'Last month: '.$d['ordLM'],'icon'=>'📦','color'=>'text-blue-600 dark:text-blue-400','border'=>'border-blue-200 dark:border-blue-800','bg'=>'bg-blue-50 dark:bg-blue-900/20'],
        ['label'=>'Avg Order Value','value'=>'OMR '.number_format($d['avgVal'],3),'sub'=>'Per completed order','icon'=>'📊','color'=>'text-amber-600 dark:text-amber-400','border'=>'border-amber-200 dark:border-amber-800','bg'=>'bg-amber-50 dark:bg-amber-900/20'],
        ['label'=>'Revenue Last Month','value'=>'OMR '.number_format($d['revLM'],3),'sub'=>'Previous period','icon'=>'📅','color'=>'text-purple-600 dark:text-purple-400','border'=>'border-purple-200 dark:border-purple-800','bg'=>'bg-purple-50 dark:bg-purple-900/20'],
    ];
    @endphp
    @foreach($kpis as $k)
    <div class="rounded-xl border {{ $k['border'] }} {{ $k['bg'] }} p-4 shadow-sm">
        <div class="flex items-center gap-1.5 mb-1"><span>{{ $k['icon'] }}</span><p class="text-[10px] uppercase tracking-widest text-gray-500 dark:text-gray-400 font-medium leading-tight">{{ $k['label'] }}</p></div>
        <p class="text-lg sm:text-xl font-bold {{ $k['color'] }} tabular-nums break-all">{{ $k['value'] }}</p>
        <p class="text-[10px] text-gray-400 mt-1">{{ $k['sub'] }}</p>
    </div>
    @endforeach
</div>

{{-- Revenue Trend + Top Products --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- 12-month revenue line chart --}}
    <div class="lg:col-span-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1">Revenue Trend — Last 12 Months</h3>
        <p class="text-xs text-gray-400 mb-4">Website orders + custom orders combined</p>
        <div style="position:relative;height:220px;"><canvas id="trendChart"></canvas></div>
    </div>

    {{-- Top 5 products --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">🏆 Top 5 Products</h3>
        <div class="space-y-3">
            @forelse($d['topProducts'] as $i => $p)
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate mr-2">
                        <span class="text-gray-400 mr-1">{{ $i+1 }}.</span>{{ $p->product_name }}
                    </span>
                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400 flex-shrink-0 tabular-nums">OMR {{ number_format($p->total_revenue,3) }}</span>
                </div>
                @php $pct = $d['topProducts'][0]->total_revenue > 0 ? ($p->total_revenue / $d['topProducts'][0]->total_revenue) * 100 : 0; @endphp
                <div class="h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full bg-amber-400 rounded-full transition-all" style="width:{{ $pct }}%"></div>
                </div>
                <p class="text-[10px] text-gray-400 mt-0.5">{{ $p->total_qty }} units sold</p>
            </div>
            @empty
            <p class="text-xs text-gray-400 py-8 text-center">No sales data yet</p>
            @endforelse
        </div>
    </div>

</div>

{{-- Payment Method + Geography --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">

    {{-- Payment method donut --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">💳 Revenue by Payment Method</h3>
        @if($d['byPayment']->isEmpty())
            <p class="text-xs text-gray-400 py-8 text-center">No orders yet</p>
        @else
        <div class="grid grid-cols-2 gap-4 items-center">
            <div style="position:relative;height:160px;"><canvas id="paymentChart"></canvas></div>
            <div class="space-y-2">
                @php $totalPay = $d['byPayment']->sum('total'); @endphp
                @foreach($d['byPayment'] as $p)
                @php
                    $pmLabel = match($p->payment_method) {
                        'cod'      => '💵 Cash on Del.',
                        'bank'     => '🏦 Bank',
                        'whatsapp' => '📱 WhatsApp',
                        default    => ucfirst($p->payment_method),
                    };
                @endphp
                <div class="flex justify-between text-xs">
                    <span class="text-gray-600 dark:text-gray-400">{{ $pmLabel }}</span>
                    <span class="font-semibold tabular-nums">{{ $totalPay > 0 ? round(($p->total/$totalPay)*100,1) : 0 }}%</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Geography --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 shadow-sm">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">🗺️ Revenue by Governorate</h3>
        @php $totalGov = $d['byGov']->sum('total'); @endphp
        <div class="space-y-2.5">
            @forelse($d['byGov'] as $g)
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-gray-600 dark:text-gray-400 font-medium">{{ $g->governorate ?: 'Unknown' }}</span>
                    <span class="tabular-nums text-gray-700 dark:text-gray-300">OMR {{ number_format($g->total,3) }} <span class="text-gray-400">({{ $g->cnt }})</span></span>
                </div>
                <div class="h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-400 rounded-full" style="width:{{ $totalGov > 0 ? ($g->total/$totalGov)*100 : 0 }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-xs text-gray-400 py-4 text-center">No location data</p>
            @endforelse
        </div>
    </div>

</div>

{{-- Recent Orders --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
    <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">🕐 Recent Orders</h3>
        <a href="/admin/orders" class="text-xs text-amber-500 hover:underline">View all →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-xs uppercase tracking-wider text-gray-500 bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-2.5 text-left">Order</th>
                    <th class="px-4 py-2.5 text-left">Customer</th>
                    <th class="px-4 py-2.5 text-left">Items</th>
                    <th class="px-4 py-2.5 text-right">Total</th>
                    <th class="px-4 py-2.5 text-center">Status</th>
                    <th class="px-4 py-2.5 text-left">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($d['recent'] as $o)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                    <td class="px-4 py-2.5 font-mono text-xs font-medium">{{ $o->order_number }}</td>
                    <td class="px-4 py-2.5">{{ $o->first_name }} {{ $o->last_name }}</td>
                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $o->items->sum('quantity') }} items</td>
                    <td class="px-4 py-2.5 text-right font-semibold tabular-nums text-amber-600 dark:text-amber-400">OMR {{ number_format($o->total_omr,3) }}</td>
                    <td class="px-4 py-2.5 text-center">
                        @php
                            $statusCss = match($o->status) {
                                'pending'   => 'bg-yellow-100 text-yellow-700',
                                'confirmed' => 'bg-blue-100 text-blue-700',
                                'delivered' => 'bg-green-100 text-green-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                                default     => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $statusCss }}">{{ ucfirst($o->status) }}</span>
                    </td>
                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $o->created_at->format('d M y') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 text-xs">No orders yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', initSalesDash);
document.addEventListener('livewire:navigated', initSalesDash);

function initSalesDash() {
    const dark = document.documentElement.classList.contains('dark');
    const grid = dark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const text = dark ? '#9ca3af' : '#6b7280';

    // Revenue trend
    const tc = document.getElementById('trendChart');
    if (tc) {
        if (tc._c) tc._c.destroy();
        tc._c = new Chart(tc, {
            type: 'line',
            data: {
                labels: @json(collect($d['monthly'])->pluck('month')),
                datasets: [{
                    label: 'Revenue (OMR)',
                    data: @json(collect($d['monthly'])->pluck('revenue')),
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245,158,11,0.1)',
                    borderWidth: 2.5, pointRadius: 4, pointBackgroundColor: '#f59e0b',
                    fill: true, tension: 0.4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend:{display:false}, tooltip:{callbacks:{label: c=>'OMR '+c.parsed.y.toFixed(3)}} },
                scales: {
                    x: { ticks:{color:text,font:{size:10}}, grid:{display:false} },
                    y: { ticks:{color:text,font:{size:10},callback:v=>'OMR '+v.toFixed(0)}, grid:{color:grid}, beginAtZero:true }
                }
            }
        });
    }

    // Payment method donut
    const pc = document.getElementById('paymentChart');
    if (pc && {{ $d['byPayment']->count() }} > 0) {
        if (pc._c) pc._c.destroy();
        pc._c = new Chart(pc, {
            type: 'doughnut',
            data: {
                labels: @json($d['byPayment']->map(fn($p) => $p->payment_method === 'cod' ? 'Cash on Del.' : ($p->payment_method === 'bank' ? 'Bank Transfer' : ($p->payment_method === 'whatsapp' ? 'WhatsApp' : ucfirst($p->payment_method))))->values()),
                datasets: [{ data: @json($d['byPayment']->pluck('total')->values()), backgroundColor:['#f59e0b','#3b82f6','#10b981','#8b5cf6'], borderWidth:0, hoverOffset:5 }]
            },
            options: { responsive:true, maintainAspectRatio:false, cutout:'68%', plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>'OMR '+c.parsed.toFixed(3)}}} }
        });
    }
}
document.addEventListener('livewire:updated', ()=>setTimeout(initSalesDash,100));
</script>
</x-filament-panels::page>
