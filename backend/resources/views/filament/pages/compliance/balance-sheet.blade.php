<x-filament-panels::page>
@php $d = $this->getData(); @endphp

<div class="flex flex-wrap items-center gap-3 mb-6 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Balance Sheet as of:</span>
    <input type="date" wire:model.live="asOf" class="text-sm border border-gray-300 dark:border-gray-600 rounded-md px-3 py-1.5 bg-white dark:bg-gray-800 dark:text-gray-300 focus:ring-2 focus:ring-amber-400 outline-none">
    <span class="text-xs text-gray-400">{{ $d['date']->format('d M Y') }}</span>
    @if(abs($d['check']) < 1)
    <span class="ml-auto text-xs px-3 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400 font-semibold">✅ Balanced</span>
    @else
    <span class="ml-auto text-xs px-3 py-1 rounded-full bg-red-100 text-red-700 font-semibold">⚠️ Difference: OMR {{ number_format($d['check'],3) }}</span>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-5 shadow-sm text-center"><p class="text-xs uppercase tracking-widest text-gray-500 mb-1">Total Assets</p><p class="text-3xl font-bold text-green-600 dark:text-green-400 tabular-nums">OMR {{ number_format($d['totalAssets'],3) }}</p></div>
    <div class="rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-5 shadow-sm text-center"><p class="text-xs uppercase tracking-widest text-gray-500 mb-1">Total Liabilities</p><p class="text-3xl font-bold text-red-600 dark:text-red-400 tabular-nums">OMR {{ number_format($d['totalLiabilities'],3) }}</p></div>
    <div class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-5 shadow-sm text-center"><p class="text-xs uppercase tracking-widest text-gray-500 mb-1">Total Equity</p><p class="text-3xl font-bold text-blue-600 dark:text-blue-400 tabular-nums">OMR {{ number_format($d['totalEquity'],3) }}</p></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- ASSETS --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="px-5 py-3 bg-green-50 dark:bg-green-900/20 border-b border-green-200 dark:border-green-800"><h3 class="font-bold text-green-800 dark:text-green-300 text-sm uppercase tracking-wider">Assets</h3></div>
        <div class="p-5 space-y-3">
            @foreach([['Cash & Bank Balance',$d['cashBalance']],['Accounts Receivable',$d['receivable']],['Inventory Value',$d['inventory']]] as [$label,$val])
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $label }}</span>
                <span class="font-medium tabular-nums {{ $val>=0?'text-gray-700 dark:text-gray-300':'text-red-500' }}">OMR {{ number_format($val,3) }}</span>
            </div>
            @endforeach
            <div class="flex justify-between pt-2 font-bold text-green-700 dark:text-green-400 border-t-2 border-green-300 dark:border-green-700">
                <span>TOTAL ASSETS</span><span class="tabular-nums text-lg">OMR {{ number_format($d['totalAssets'],3) }}</span>
            </div>
        </div>
    </div>

    {{-- LIABILITIES --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="px-5 py-3 bg-red-50 dark:bg-red-900/20 border-b border-red-200 dark:border-red-800"><h3 class="font-bold text-red-800 dark:text-red-300 text-sm uppercase tracking-wider">Liabilities</h3></div>
        <div class="p-5 space-y-3">
            @foreach([['Accounts Payable (Suppliers)',$d['payable']],['VAT Payable (Estimated)',$d['vatPayable']]] as [$label,$val])
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $label }}</span>
                <span class="font-medium tabular-nums text-red-500">OMR {{ number_format($val,3) }}</span>
            </div>
            @endforeach
            <div class="flex justify-between pt-2 font-bold text-red-700 dark:text-red-400 border-t-2 border-red-300 dark:border-red-700">
                <span>TOTAL LIABILITIES</span><span class="tabular-nums text-lg">OMR {{ number_format($d['totalLiabilities'],3) }}</span>
            </div>
        </div>
    </div>

    {{-- EQUITY --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
        <div class="px-5 py-3 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800"><h3 class="font-bold text-blue-800 dark:text-blue-300 text-sm uppercase tracking-wider">Equity</h3></div>
        <div class="p-5 space-y-3">
            @foreach([['Total Revenue',$d['totalRevenue'],'text-green-600 dark:text-green-400'],['Total Purchases','-'.$d['totalPurchases'],'text-red-500'],['Total Expenses','-'.$d['totalExpenses'],'text-red-500']] as [$label,$val,$color])
            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $label }}</span>
                <span class="font-medium tabular-nums {{ $color }}">OMR {{ number_format(abs((float)$val),3) }}</span>
            </div>
            @endforeach
            <div class="flex justify-between pt-2 font-bold border-t-2 border-blue-300 dark:border-blue-700 {{ $d['retainedEarnings']>=0?'text-blue-700 dark:text-blue-400':'text-red-600' }}">
                <span>RETAINED EARNINGS</span><span class="tabular-nums text-lg">OMR {{ number_format($d['retainedEarnings'],3) }}</span>
            </div>
            <div class="flex justify-between pt-1 font-bold text-blue-700 dark:text-blue-400">
                <span>TOTAL EQUITY</span><span class="tabular-nums text-lg">OMR {{ number_format($d['totalEquity'],3) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 p-3 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 text-xs text-amber-700 dark:text-amber-400">
    <strong>⚠️ Note:</strong> This is a simplified balance sheet based on recorded transactions.
    Inventory is valued at selling price (not cost price). Consult an accountant for audited financial statements.
</div>
</x-filament-panels::page>
