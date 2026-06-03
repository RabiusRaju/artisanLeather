<x-filament-panels::page>
@php $d = $this->getData(); @endphp

{{-- KPIs --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    @foreach([
        ['label'=>'Total Outstanding','value'=>'OMR '.number_format($d['totalOwed'],3),'icon'=>'💳','color'=>'text-red-600 dark:text-red-400','bg'=>'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800'],
        ['label'=>'Overdue POs (>30 days)','value'=>$d['overdueCount'].' orders','icon'=>'⚠️','color'=>'text-orange-600 dark:text-orange-400','bg'=>'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800'],
        ['label'=>'Total Paid (all time)','value'=>'OMR '.number_format($d['totalPaid'],3),'icon'=>'✅','color'=>'text-green-600 dark:text-green-400','bg'=>'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800'],
    ] as $kpi)
    <div class="rounded-xl border {{ $kpi['bg'] }} p-4 shadow-sm">
        <div class="flex items-center gap-2 mb-1"><span>{{ $kpi['icon'] }}</span><span class="text-xs uppercase tracking-widest text-gray-500 font-medium">{{ $kpi['label'] }}</span></div>
        <p class="text-xl font-bold {{ $kpi['color'] }} tabular-nums">{{ $kpi['value'] }}</p>
    </div>
    @endforeach
</div>

{{-- Outstanding payables table --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
    <div class="px-5 py-4 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Outstanding Payments to Suppliers</h3>
        <a href="/admin/finance/purchase-orders/create" class="text-xs text-amber-500 hover:underline">+ New PO</a>
    </div>
    @if($d['orders']->isEmpty())
    <div class="py-16 text-center text-gray-400"><span class="text-4xl">🎉</span><p class="mt-3 text-sm">All suppliers paid! No outstanding balances.</p></div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">PO Number</th>
                    <th class="px-4 py-3 text-left">Supplier</th>
                    <th class="px-4 py-3 text-left">Order Date</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-right">Paid</th>
                    <th class="px-4 py-3 text-right font-bold">Balance Due</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($d['orders'] as $order)
                @php $balance = $order->total_omr - $order->paid_amount_omr; $isOld = $order->order_date->lt(now()->subDays(30)); @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 {{ $isOld ? 'bg-red-50/40 dark:bg-red-900/10' : '' }}">
                    <td class="px-4 py-3 font-medium font-mono text-xs">{{ $order->reference_number }}</td>
                    <td class="px-4 py-3 font-medium">{{ $order->supplier?->name }}</td>
                    <td class="px-4 py-3 text-gray-500">
                        {{ $order->order_date->format('d M Y') }}
                        @if($isOld)<span class="ml-1 text-xs text-red-500 font-medium">Overdue</span>@endif
                    </td>
                    <td class="px-4 py-3 text-right tabular-nums">OMR {{ number_format($order->total_omr,3) }}</td>
                    <td class="px-4 py-3 text-right tabular-nums text-green-600">OMR {{ number_format($order->paid_amount_omr,3) }}</td>
                    <td class="px-4 py-3 text-right tabular-nums font-bold text-red-600 dark:text-red-400">OMR {{ number_format($balance,3) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $order->payment_status==='partial'?'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400':'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <a href="/admin/finance/purchase-orders/{{ $order->id }}/edit?tab=payment" class="text-xs text-amber-500 hover:underline">Mark Paid</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
</x-filament-panels::page>
