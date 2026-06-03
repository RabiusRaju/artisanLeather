<x-filament-panels::page>
@php $d = $this->getData(); @endphp

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label'=>'Total Contract Value','value'=>'OMR '.number_format($d['totalAgreed'],3),'icon'=>'📋','color'=>'text-blue-600 dark:text-blue-400','bg'=>'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800'],
        ['label'=>'Deposits Received','value'=>'OMR '.number_format($d['totalDeposit'],3),'icon'=>'✅','color'=>'text-green-600 dark:text-green-400','bg'=>'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800'],
        ['label'=>'Balance Outstanding','value'=>'OMR '.number_format($d['totalBalance'],3),'icon'=>'💰','color'=>'text-amber-600 dark:text-amber-400','bg'=>'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800'],
        ['label'=>'Pending Deposits','value'=>$d['pendingDeposit'].' orders','icon'=>'⏳','color'=>'text-orange-600 dark:text-orange-400','bg'=>'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800'],
    ] as $kpi)
    <div class="rounded-xl border {{ $kpi['bg'] }} p-4 shadow-sm">
        <div class="flex items-center gap-1.5 mb-1"><span>{{ $kpi['icon'] }}</span><span class="text-[10px] uppercase tracking-widest text-gray-500 font-medium leading-tight">{{ $kpi['label'] }}</span></div>
        <p class="text-lg font-bold {{ $kpi['color'] }} tabular-nums break-all">{{ $kpi['value'] }}</p>
    </div>
    @endforeach
</div>

<div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
    <div class="px-5 py-4 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Custom Orders — Payment Tracking</h3>
        <a href="/admin/custom-orders/create" class="text-xs text-amber-500 hover:underline">+ New Custom Order</a>
    </div>
    @if($d['orders']->isEmpty())
    <div class="py-16 text-center text-gray-400"><span class="text-4xl">📭</span><p class="mt-3 text-sm">No custom orders yet.</p></div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Reference</th>
                    <th class="px-4 py-3 text-left">Customer</th>
                    <th class="px-4 py-3 text-left">Product</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-center">Deposit</th>
                    <th class="px-4 py-3 text-right">Balance Due</th>
                    <th class="px-4 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($d['orders'] as $order)
                @php $balance = $order->agreed_price_omr - ($order->deposit_paid ? $order->deposit_amount_omr : 0); @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                    <td class="px-4 py-3 font-mono text-xs font-medium">{{ $order->reference_number }}</td>
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $order->customer_name }}</div>
                        <div class="text-xs text-gray-400">{{ $order->customer_phone }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $order->product_name ?: ucfirst($order->product_type) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ match($order->status){
                            'in_production'=>'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
                            'ready'=>'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
                            'delivered'=>'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                            default=>'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400'
                        } }}">{{ ucfirst(str_replace('_',' ',$order->status)) }}</span>
                    </td>
                    <td class="px-4 py-3 text-right tabular-nums font-medium">OMR {{ number_format($order->agreed_price_omr,3) }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($order->deposit_paid)
                            <span class="text-xs text-green-600 font-medium">✅ OMR {{ number_format($order->deposit_amount_omr,3) }}</span>
                        @else
                            <span class="text-xs text-orange-500 font-medium">⏳ Pending</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right tabular-nums font-bold {{ $balance>0?'text-amber-600 dark:text-amber-400':'text-green-600 dark:text-green-400' }}">
                        {{ $balance > 0 ? 'OMR '.number_format($balance,3) : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <a href="/admin/custom-orders/{{ $order->id }}/edit" class="text-xs text-amber-500 hover:underline">Update</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
</x-filament-panels::page>
