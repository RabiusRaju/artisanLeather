<x-filament-panels::page>
@php $d = $this->getData(); @endphp

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    @foreach([
        ['Total Cash In','OMR '.number_format($d['totalIn'],3),'text-green-600 dark:text-green-400','border-green-200 dark:border-green-800','bg-green-50 dark:bg-green-900/20'],
        ['Total Cash Out','OMR '.number_format($d['totalOut'],3),'text-red-600 dark:text-red-400','border-red-200 dark:border-red-800','bg-red-50 dark:bg-red-900/20'],
        ['Net Balance','OMR '.number_format($d['netBalance'],3),$d['netBalance']>=0?'text-emerald-600 dark:text-emerald-400':'text-red-600',$d['netBalance']>=0?'border-emerald-200 dark:border-emerald-800':'border-red-200 dark:border-red-800',$d['netBalance']>=0?'bg-emerald-50 dark:bg-emerald-900/20':'bg-red-50 dark:bg-red-900/20'],
        ['Unreconciled',$d['unreconciledEntries']->count().' entries','text-amber-600 dark:text-amber-400','border-amber-200 dark:border-amber-800','bg-amber-50 dark:bg-amber-900/20'],
    ] as [$label,$val,$color,$border,$bg])
    <div class="rounded-xl border {{ $border }} {{ $bg }} p-4 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest text-gray-500 mb-1">{{ $label }}</p>
        <p class="text-xl font-bold {{ $color }} tabular-nums break-all">{{ $val }}</p>
    </div>
    @endforeach
</div>

<div class="rounded-xl border border-amber-200 dark:border-amber-800 overflow-hidden shadow-sm">
    <div class="px-5 py-3 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-800 flex items-center justify-between">
        <h3 class="font-semibold text-amber-800 dark:text-amber-300 text-sm">⏳ Unreconciled Entries — verify against bank statement</h3>
        <a href="/admin/operations/cash-flow/cash-flows/create" class="text-xs text-amber-600 hover:underline">+ Log Entry →</a>
    </div>
    @if($d['unreconciledEntries']->isEmpty())
    <div class="py-12 text-center"><span class="text-3xl">🎉</span><p class="mt-3 text-sm text-gray-400">All entries reconciled! Your books match the bank statement.</p></div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-xs uppercase tracking-wider text-gray-500 bg-gray-50 dark:bg-gray-800">
                <tr><th class="px-4 py-2.5 text-left">Date</th><th class="px-4 py-2.5 text-left">Direction</th><th class="px-4 py-2.5 text-left">Description</th><th class="px-4 py-2.5 text-left">Category</th><th class="px-4 py-2.5 text-right">Amount</th><th class="px-4 py-2.5 text-left">Method</th><th class="px-4 py-2.5 text-center">Action</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($d['unreconciledEntries'] as $e)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $e->entry_date->format('d M Y') }}</td>
                    <td class="px-4 py-2.5"><span class="text-xs font-bold {{ $e->type==='in'?'text-green-600 dark:text-green-400':'text-red-500' }}">{{ $e->type==='in'?'↓ IN':'↑ OUT' }}</span></td>
                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 max-w-xs truncate">{{ $e->description }}</td>
                    <td class="px-4 py-2.5"><span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_',' ',$e->category)) }}</span></td>
                    <td class="px-4 py-2.5 text-right font-semibold tabular-nums {{ $e->type==='in'?'text-green-600 dark:text-green-400':'text-red-500' }}">{{ $e->type==='in'?'+':'-' }} OMR {{ number_format($e->amount_omr,3) }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-500">{{ ucfirst(str_replace('_',' ',$e->payment_method)) }}</td>
                    <td class="px-4 py-2.5 text-center">
                        <button wire:click="markReconciled({{ $e->id }})" class="text-xs bg-green-100 hover:bg-green-200 text-green-700 dark:bg-green-900/40 dark:text-green-400 px-3 py-1 rounded-md transition-colors">✓ Reconcile</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
<p class="mt-3 text-xs text-gray-400 text-center">Compare each entry against your Bank Muscat statement. Click "Reconcile" when confirmed.</p>
</x-filament-panels::page>
