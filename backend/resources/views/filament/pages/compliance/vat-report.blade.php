<x-filament-panels::page>
@php $d = $this->getData(); @endphp

{{-- Quarter & Year selector --}}
<div class="flex flex-wrap items-center gap-3 mb-6 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Reporting Period:</span>
    <div class="flex gap-2 flex-wrap">
        @foreach(['Q1','Q2','Q3','Q4'] as $q)
        <button wire:click="$set('quarter','{{ $q }}')"
            class="px-4 py-1.5 text-xs font-semibold rounded-md transition-all {{ $quarter===$q?'bg-amber-500 text-white':'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-amber-100' }}">
            {{ $q }}
        </button>
        @endforeach
    </div>
    <select wire:model.live="year" class="text-sm border border-gray-300 dark:border-gray-600 rounded-md px-3 py-1.5 bg-white dark:bg-gray-800 dark:text-gray-300 outline-none focus:ring-2 focus:ring-amber-400">
        @foreach(range(now()->year, now()->year - 3) as $y)
        <option value="{{ $y }}">{{ $y }}</option>
        @endforeach
    </select>
    <span class="text-xs text-gray-400">{{ $d['start']->format('d M Y') }} — {{ $d['end']->format('d M Y') }}</span>
    @if($d['setting']?->registration_number)
    <span class="ml-auto text-xs text-gray-500">VAT Reg: <strong>{{ $d['setting']->registration_number }}</strong></span>
    @endif
</div>

{{-- VAT Rate banner --}}
<div class="mb-6 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-xs text-amber-700 dark:text-amber-400">
    📋 <strong>Oman VAT Rate: {{ $d['vatRatePct'] }}%</strong>
    @if(!$d['setting']?->is_registered)
    — You are not yet VAT registered. Register when annual revenue exceeds <strong>OMR 38,500</strong>.
    @else
    — Registered. File quarterly returns by the 28th of the month following the quarter end.
    @endif
</div>

{{-- Summary cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-5 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest text-gray-500 font-medium mb-1">📤 Output VAT (Collected)</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400 tabular-nums">OMR {{ number_format($d['outputVAT'],3) }}</p>
        <p class="text-xs text-gray-400 mt-1">On sales of OMR {{ number_format($d['totalSales'],3) }}</p>
    </div>
    <div class="rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-5 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest text-gray-500 font-medium mb-1">📥 Input VAT (Reclaimable)</p>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400 tabular-nums">OMR {{ number_format($d['inputVAT'],3) }}</p>
        <p class="text-xs text-gray-400 mt-1">On purchases of OMR {{ number_format($d['purchaseTotal'],3) }}</p>
    </div>
    <div class="rounded-xl border-2 {{ $d['netVAT']>=0?'border-amber-400 dark:border-amber-600 bg-amber-50 dark:bg-amber-900/20':'border-green-400 dark:border-green-600 bg-green-50 dark:bg-green-900/20' }} p-5 shadow-sm">
        <p class="text-[10px] uppercase tracking-widest text-gray-500 font-medium mb-1">{{ $d['netVAT']>=0?'💳 Net VAT Payable':'💚 VAT Refund Due' }}</p>
        <p class="text-2xl font-bold {{ $d['netVAT']>=0?'text-amber-600 dark:text-amber-400':'text-green-600 dark:text-green-400' }} tabular-nums">OMR {{ number_format(abs($d['netVAT']),3) }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $d['netVAT']>=0?'To pay to Oman Tax Authority':'Government owes you this amount' }}</p>
    </div>
</div>

{{-- Monthly breakdown --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm mb-6">
    <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Monthly VAT Breakdown — {{ $quarter }} {{ $year }}</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-xs uppercase tracking-wider text-gray-500 bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-5 py-2.5 text-left">Month</th>
                    <th class="px-5 py-2.5 text-right">Sales Revenue</th>
                    <th class="px-5 py-2.5 text-right">Output VAT ({{ $d['vatRatePct'] }}%)</th>
                    <th class="px-5 py-2.5 text-right">Purchases</th>
                    <th class="px-5 py-2.5 text-right">Input VAT ({{ $d['vatRatePct'] }}%)</th>
                    <th class="px-5 py-2.5 text-right font-bold">Net VAT</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($d['months'] as $m)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                    <td class="px-5 py-3 font-medium">{{ $m['month'] }}</td>
                    <td class="px-5 py-3 text-right tabular-nums text-green-600 dark:text-green-400">OMR {{ number_format($m['sales'],3) }}</td>
                    <td class="px-5 py-3 text-right tabular-nums font-semibold">OMR {{ number_format($m['output'],3) }}</td>
                    <td class="px-5 py-3 text-right tabular-nums text-red-500">OMR {{ number_format($m['purchases'],3) }}</td>
                    <td class="px-5 py-3 text-right tabular-nums">OMR {{ number_format($m['input'],3) }}</td>
                    <td class="px-5 py-3 text-right tabular-nums font-bold {{ $m['net']>=0?'text-amber-600 dark:text-amber-400':'text-green-600' }}">OMR {{ number_format($m['net'],3) }}</td>
                </tr>
                @endforeach
                <tr class="bg-gray-50 dark:bg-gray-800 font-bold border-t-2 border-gray-300 dark:border-gray-600">
                    <td class="px-5 py-3">QUARTER TOTAL</td>
                    <td class="px-5 py-3 text-right tabular-nums text-green-600 dark:text-green-400">OMR {{ number_format($d['totalSales'],3) }}</td>
                    <td class="px-5 py-3 text-right tabular-nums">OMR {{ number_format($d['outputVAT'],3) }}</td>
                    <td class="px-5 py-3 text-right tabular-nums text-red-500">OMR {{ number_format($d['purchaseTotal'],3) }}</td>
                    <td class="px-5 py-3 text-right tabular-nums">OMR {{ number_format($d['inputVAT'],3) }}</td>
                    <td class="px-5 py-3 text-right tabular-nums text-lg {{ $d['netVAT']>=0?'text-amber-600 dark:text-amber-400':'text-green-600' }}">OMR {{ number_format($d['netVAT'],3) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4 text-xs text-blue-700 dark:text-blue-400">
    <strong>ℹ️ Important:</strong> This report is an estimate based on your recorded orders and purchases.
    Consult a qualified accountant for official VAT filing. Oman VAT returns are filed quarterly.
    Registration mandatory when taxable supplies exceed <strong>OMR 38,500/year</strong>.
</div>
</x-filament-panels::page>
