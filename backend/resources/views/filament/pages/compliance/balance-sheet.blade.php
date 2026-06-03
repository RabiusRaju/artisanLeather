<x-filament-panels::page>
<script src="/js/chart.umd.min.js"></script>
@php $d = $this->getData(); @endphp

{{-- Date Selector --}}
<div style="display:flex;flex-wrap:wrap;align-items:center;gap:12px;padding:16px 20px;background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:20px">
    <span style="font-size:13px;font-weight:600;color:#374151">Balance Sheet as of:</span>
    <input type="date" wire:model.live="asOf"
        style="font-size:13px;border:1px solid #d1d5db;border-radius:8px;padding:6px 12px;background:#fff;color:#374151;outline:none">
    <span style="font-size:12px;font-weight:600;color:#374151;background:#f3f4f6;padding:4px 10px;border-radius:6px">{{ $d['date']->format('d M Y') }}</span>
    @if(abs($d['check']) < 1)
    <span style="margin-left:auto;font-size:12px;font-weight:700;padding:4px 14px;border-radius:99px;background:#d1fae5;color:#065f46">✅ Balanced</span>
    @else
    <span style="margin-left:auto;font-size:12px;font-weight:700;padding:4px 14px;border-radius:99px;background:#fee2e2;color:#991b1b">⚠️ Diff: OMR {{ number_format($d['check'],3) }}</span>
    @endif
</div>

{{-- Accounting Equation Banner --}}
<div style="padding:14px 20px;border-radius:12px;background:#f9fafb;border:1px solid #e5e7eb;margin-bottom:20px;display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap">
    <div style="text-align:center">
        <div style="font-size:22px;font-weight:900;color:#059669;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalAssets'],3) }}</div>
        <div style="font-size:11px;font-weight:600;color:#059669;text-transform:uppercase;letter-spacing:.05em">Assets</div>
    </div>
    <div style="font-size:24px;color:#9ca3af;font-weight:300">=</div>
    <div style="text-align:center">
        <div style="font-size:22px;font-weight:900;color:#dc2626;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalLiabilities'],3) }}</div>
        <div style="font-size:11px;font-weight:600;color:#dc2626;text-transform:uppercase;letter-spacing:.05em">Liabilities</div>
    </div>
    <div style="font-size:24px;color:#9ca3af;font-weight:300">+</div>
    <div style="text-align:center">
        <div style="font-size:22px;font-weight:900;color:#2563eb;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalEquity'],3) }}</div>
        <div style="font-size:11px;font-weight:600;color:#2563eb;text-transform:uppercase;letter-spacing:.05em">Equity</div>
    </div>
</div>

{{-- Summary Cards Row --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">
    <div style="border-radius:14px;border:1px solid #a7f3d0;background:linear-gradient(135deg,#ecfdf5,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;margin-bottom:10px">💚 Total Assets</div>
        <div style="font-size:28px;font-weight:900;color:#059669;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalAssets'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Cash + Receivables + Inventory</div>
    </div>
    <div style="border-radius:14px;border:1px solid #fecaca;background:linear-gradient(135deg,#fef2f2,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;margin-bottom:10px">🔴 Total Liabilities</div>
        <div style="font-size:28px;font-weight:900;color:#dc2626;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalLiabilities'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Payables + VAT due</div>
    </div>
    <div style="border-radius:14px;border:1px solid #bfdbfe;background:linear-gradient(135deg,#eff6ff,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;margin-bottom:10px">🔵 Owner's Equity</div>
        <div style="font-size:28px;font-weight:900;color:{{ $d['totalEquity']>=0?'#2563eb':'#dc2626' }};font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalEquity'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Retained earnings (net profit)</div>
    </div>
</div>

{{-- Assets | Liabilities | Equity Columns --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px">

    {{-- ASSETS --}}
    <div style="border-radius:14px;border:1px solid #a7f3d0;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="padding:14px 18px;background:linear-gradient(135deg,#ecfdf5,#d1fae5);border-bottom:1px solid #a7f3d0">
            <div style="font-size:13px;font-weight:800;color:#065f46;text-transform:uppercase;letter-spacing:.06em">📋 Assets</div>
        </div>
        <div style="padding:16px">
            @foreach([
                ['Cash & Bank Balance','💵',$d['cashBalance'],'#059669'],
                ['Accounts Receivable','💳',$d['receivable'],'#2563eb'],
                ['Inventory Value','📦',$d['inventory'],'#7c3aed'],
            ] as [$label,$icon,$val,$color])
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f3f4f6">
                <div style="display:flex;align-items:center;gap:8px">
                    <span style="font-size:16px">{{ $icon }}</span>
                    <span style="font-size:12px;color:#374151">{{ $label }}</span>
                </div>
                <span style="font-size:13px;font-weight:700;color:{{ $val>=0?$color:'#dc2626' }};font-variant-numeric:tabular-nums">OMR {{ number_format($val,3) }}</span>
            </div>
            @endforeach
            <div style="display:flex;justify-content:space-between;align-items:baseline;margin-top:14px;padding-top:12px;border-top:2px solid #059669">
                <span style="font-size:13px;font-weight:800;color:#065f46;text-transform:uppercase">TOTAL ASSETS</span>
                <span style="font-size:18px;font-weight:900;color:#059669;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalAssets'],3) }}</span>
            </div>
        </div>
    </div>

    {{-- LIABILITIES --}}
    <div style="border-radius:14px;border:1px solid #fecaca;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="padding:14px 18px;background:linear-gradient(135deg,#fef2f2,#fee2e2);border-bottom:1px solid #fecaca">
            <div style="font-size:13px;font-weight:800;color:#991b1b;text-transform:uppercase;letter-spacing:.06em">📋 Liabilities</div>
        </div>
        <div style="padding:16px">
            @foreach([
                ['Accounts Payable','🧾',$d['payable']],
                ['VAT Payable (Est.)','🏦',$d['vatPayable']],
            ] as [$label,$icon,$val])
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f3f4f6">
                <div style="display:flex;align-items:center;gap:8px">
                    <span style="font-size:16px">{{ $icon }}</span>
                    <span style="font-size:12px;color:#374151">{{ $label }}</span>
                </div>
                <span style="font-size:13px;font-weight:700;color:#dc2626;font-variant-numeric:tabular-nums">OMR {{ number_format($val,3) }}</span>
            </div>
            @endforeach
            <div style="display:flex;justify-content:space-between;align-items:baseline;margin-top:14px;padding-top:12px;border-top:2px solid #dc2626">
                <span style="font-size:13px;font-weight:800;color:#991b1b;text-transform:uppercase">TOTAL LIABILITIES</span>
                <span style="font-size:18px;font-weight:900;color:#dc2626;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalLiabilities'],3) }}</span>
            </div>
        </div>
    </div>

    {{-- EQUITY --}}
    <div style="border-radius:14px;border:1px solid #bfdbfe;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="padding:14px 18px;background:linear-gradient(135deg,#eff6ff,#dbeafe);border-bottom:1px solid #bfdbfe">
            <div style="font-size:13px;font-weight:800;color:#1e40af;text-transform:uppercase;letter-spacing:.06em">📋 Equity</div>
        </div>
        <div style="padding:16px">
            @foreach([
                ['Total Revenue','💰',$d['totalRevenue'],'#059669',false],
                ['Total Purchases','📦',$d['totalPurchases'],'#dc2626',true],
                ['Total Expenses','💸',$d['totalExpenses'],'#dc2626',true],
            ] as [$label,$icon,$val,$color,$neg])
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f3f4f6">
                <div style="display:flex;align-items:center;gap:8px">
                    <span style="font-size:16px">{{ $icon }}</span>
                    <div>
                        <div style="font-size:12px;color:#374151">{{ $label }}</div>
                        @if($neg)<div style="font-size:10px;color:#9ca3af">(deducted)</div>@endif
                    </div>
                </div>
                <span style="font-size:13px;font-weight:700;color:{{ $color }};font-variant-numeric:tabular-nums">{{ $neg?'–':'+' }} OMR {{ number_format($val,3) }}</span>
            </div>
            @endforeach
            <div style="display:flex;justify-content:space-between;align-items:baseline;margin-top:14px;padding-top:12px;border-top:2px solid {{ $d['retainedEarnings']>=0?'#2563eb':'#dc2626' }}">
                <span style="font-size:13px;font-weight:800;color:{{ $d['retainedEarnings']>=0?'#1e40af':'#991b1b' }};text-transform:uppercase">RETAINED EARNINGS</span>
                <span style="font-size:18px;font-weight:900;color:{{ $d['retainedEarnings']>=0?'#2563eb':'#dc2626' }};font-variant-numeric:tabular-nums">OMR {{ number_format($d['retainedEarnings'],3) }}</span>
            </div>
        </div>
    </div>

</div>

{{-- Visual Chart --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:16px">
    <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">📊 Balance Sheet Composition</div>
    <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Visual breakdown of assets, liabilities and equity</div>
    <div x-data="{}"
         x-init="$nextTick(() => {
            const el = $el.querySelector('canvas');
            if (!el || !window.Chart) return;
            if (el._ci) { el._ci.destroy(); el._ci = null; }
            el._ci = new Chart(el, {
                type:'bar',
                data:{
                    labels:['Assets','Liabilities & Equity'],
                    datasets:[
                        {label:'Cash & Bank',data:[{{ $d['cashBalance'] }},0],backgroundColor:'#22c55e',borderRadius:{topLeft:6,topRight:0,bottomLeft:6,bottomRight:0},borderWidth:0},
                        {label:'Receivables',data:[{{ $d['receivable'] }},0],backgroundColor:'#3b82f6',borderWidth:0},
                        {label:'Inventory',data:[{{ $d['inventory'] }},0],backgroundColor:'#8b5cf6',borderWidth:0},
                        {label:'Payables',data:[0,{{ $d['payable'] }}],backgroundColor:'#ef4444',borderWidth:0},
                        {label:'VAT Payable',data:[0,{{ $d['vatPayable'] }}],backgroundColor:'#f97316',borderWidth:0},
                        {label:'Equity',data:[0,{{ max(0,$d['totalEquity']) }}],backgroundColor:'#2563eb',borderRadius:{topLeft:0,topRight:6,bottomLeft:0,bottomRight:6},borderWidth:0},
                    ]
                },
                options:{
                    indexAxis:'y',responsive:true,maintainAspectRatio:false,
                    plugins:{
                        legend:{display:true,position:'bottom',labels:{boxWidth:12,padding:12,font:{size:11}}},
                        tooltip:{callbacks:{label:c=>'OMR '+c.parsed.x.toFixed(3)}}
                    },
                    scales:{
                        x:{stacked:true,beginAtZero:true,ticks:{callback:v=>'OMR '+v.toFixed(0)},grid:{color:'rgba(0,0,0,.05)'}},
                        y:{stacked:true,grid:{display:false},ticks:{font:{size:13,weight:'600'}}}
                    }
                }
            });
         })"
         style="position:relative;height:180px"><canvas></canvas></div>
</div>

{{-- Disclaimer --}}
<div style="padding:14px 18px;border-radius:10px;background:#fffbeb;border:1px solid #fde68a;font-size:11px;color:#92400e;display:flex;align-items:flex-start;gap:8px">
    <span style="font-size:16px">⚠️</span>
    <span><strong>Note:</strong> This is a simplified management balance sheet based on recorded transactions. Inventory is valued at selling price (not cost). Consult a certified accountant for audited financial statements.</span>
</div>

</x-filament-panels::page>
