<x-filament-panels::page>
<script src="/js/chart.umd.min.js"></script>
@php $d = $this->getData(); @endphp

{{-- Period Selector --}}
<div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;padding:16px 20px;background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:20px">
    <span style="font-size:13px;font-weight:600;color:#374151">Reporting Period:</span>
    <div style="display:flex;gap:6px">
        @foreach(['Q1','Q2','Q3','Q4'] as $q)
        <button wire:click="$set('quarter','{{ $q }}')"
            style="padding:6px 16px;font-size:12px;font-weight:700;border-radius:8px;border:none;cursor:pointer;background:{{ $quarter===$q?'#f59e0b':'#f3f4f6' }};color:{{ $quarter===$q?'#fff':'#6b7280' }}">
            {{ $q }}
        </button>
        @endforeach
    </div>
    <select wire:model.live="year" style="font-size:13px;border:1px solid #d1d5db;border-radius:8px;padding:6px 12px;background:#fff;color:#374151;outline:none">
        @foreach(range(now()->year, now()->year - 3) as $y)
        <option value="{{ $y }}">{{ $y }}</option>
        @endforeach
    </select>
    <span style="font-size:11px;color:#9ca3af">{{ $d['start']->format('d M Y') }} — {{ $d['end']->format('d M Y') }}</span>
    @if($d['setting']?->registration_number)
    <span style="margin-left:auto;font-size:11px;color:#6b7280">VAT Reg: <strong>{{ $d['setting']->registration_number }}</strong></span>
    @endif
</div>

{{-- VAT Banner --}}
<div style="padding:12px 18px;border-radius:10px;background:#fffbeb;border:1px solid #fde68a;font-size:12px;color:#92400e;margin-bottom:20px;display:flex;align-items:center;gap:8px">
    <span style="font-size:16px">📋</span>
    <span><strong>Oman VAT Rate: {{ $d['vatRatePct'] }}%</strong> —
    @if(!$d['setting']?->is_registered)
        Not yet VAT registered. Register when annual revenue exceeds <strong>OMR 38,500</strong>.
    @else
        Registered. File quarterly returns by the <strong>28th</strong> of the month following quarter end.
    @endif
    </span>
</div>

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">
    <div style="border-radius:14px;border:1px solid #a7f3d0;background:linear-gradient(135deg,#ecfdf5,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:10px">📤 Output VAT Collected</div>
        <div style="font-size:28px;font-weight:900;color:#059669;font-variant-numeric:tabular-nums">OMR {{ number_format($d['outputVAT'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">On sales of OMR {{ number_format($d['totalSales'],3) }}</div>
        <div style="margin-top:10px;padding:8px 12px;background:#d1fae5;border-radius:8px;font-size:11px;color:#065f46">Revenue × {{ $d['vatRatePct'] }}%</div>
    </div>
    <div style="border-radius:14px;border:1px solid #fecaca;background:linear-gradient(135deg,#fef2f2,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:10px">📥 Input VAT Reclaimable</div>
        <div style="font-size:28px;font-weight:900;color:#dc2626;font-variant-numeric:tabular-nums">OMR {{ number_format($d['inputVAT'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">On purchases of OMR {{ number_format($d['purchaseTotal'],3) }}</div>
        <div style="margin-top:10px;padding:8px 12px;background:#fee2e2;border-radius:8px;font-size:11px;color:#991b1b">Purchases × {{ $d['vatRatePct'] }}%</div>
    </div>
    <div style="border-radius:14px;border:2px solid {{ $d['netVAT']>=0?'#f59e0b':'#22c55e' }};background:{{ $d['netVAT']>=0?'linear-gradient(135deg,#fffbeb,#fff)':'linear-gradient(135deg,#f0fdf4,#fff)' }};padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.08)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:10px">{{ $d['netVAT']>=0?'💳 Net VAT Payable':'💚 VAT Refund Due' }}</div>
        <div style="font-size:32px;font-weight:900;color:{{ $d['netVAT']>=0?'#d97706':'#059669' }};font-variant-numeric:tabular-nums">OMR {{ number_format(abs($d['netVAT']),3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">{{ $d['netVAT']>=0?'To pay to Oman Tax Authority':'Government owes you this' }}</div>
        <div style="margin-top:10px;padding:8px 12px;background:{{ $d['netVAT']>=0?'#fef3c7':'#d1fae5' }};border-radius:8px;font-size:11px;color:{{ $d['netVAT']>=0?'#92400e':'#065f46' }}">Output – Input = OMR {{ number_format($d['netVAT'],3) }}</div>
    </div>
</div>

{{-- Chart --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:16px">
    <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">📊 Monthly VAT — {{ $quarter }} {{ $year }}</div>
    <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Output collected vs Input reclaimable vs Net payable</div>
    <div x-data="{}"
         x-init="$nextTick(() => {
            const el = $el.querySelector('canvas');
            if (!el || !window.Chart) return;
            if (el._ci) { el._ci.destroy(); el._ci = null; }
            el._ci = new Chart(el, {
                type:'bar',
                data:{
                    labels:{{ collect($d['months'])->pluck('month')->toJson() }},
                    datasets:[
                        {label:'Output VAT',data:{{ collect($d['months'])->pluck('output')->toJson() }},backgroundColor:'rgba(16,185,129,.75)',borderColor:'#10b981',borderWidth:1.5,borderRadius:6},
                        {label:'Input VAT',data:{{ collect($d['months'])->pluck('input')->toJson() }},backgroundColor:'rgba(239,68,68,.65)',borderColor:'#ef4444',borderWidth:1.5,borderRadius:6},
                        {label:'Net VAT',data:{{ collect($d['months'])->pluck('net')->toJson() }},type:'line',borderColor:'#f59e0b',borderWidth:2.5,pointRadius:5,pointBackgroundColor:'#f59e0b',fill:false,tension:0.3}
                    ]
                },
                options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},
                    plugins:{legend:{display:true,position:'top',align:'end',labels:{boxWidth:12,padding:10,usePointStyle:true,font:{size:11}}},
                             tooltip:{callbacks:{label:c=>c.dataset.label+': OMR '+c.parsed.y.toFixed(3)}}},
                    scales:{x:{grid:{display:false},ticks:{font:{size:11}}},y:{beginAtZero:true,ticks:{callback:v=>'OMR '+v.toFixed(2)},grid:{color:'rgba(0,0,0,.05)'}}}}
            });
         })"
         style="position:relative;height:220px"><canvas></canvas></div>
</div>

{{-- Table --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:16px">
    <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div style="font-size:14px;font-weight:700;color:#111827">Monthly VAT Breakdown — {{ $quarter }} {{ $year }}</div>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f9fafb">
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Month</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Sales Revenue</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Output VAT ({{ $d['vatRatePct'] }}%)</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Purchases</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Input VAT ({{ $d['vatRatePct'] }}%)</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Net VAT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($d['months'] as $m)
                <tr style="border-top:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:12px 16px;font-size:13px;font-weight:600;color:#374151">{{ $m['month'] }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:600;color:#059669;font-variant-numeric:tabular-nums">OMR {{ number_format($m['sales'],3) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:700;color:#111827;font-variant-numeric:tabular-nums">OMR {{ number_format($m['output'],3) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;color:#dc2626;font-variant-numeric:tabular-nums">OMR {{ number_format($m['purchases'],3) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;color:#374151;font-variant-numeric:tabular-nums">OMR {{ number_format($m['input'],3) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:14px;font-weight:800;color:{{ $m['net']>=0?'#d97706':'#059669' }};font-variant-numeric:tabular-nums">OMR {{ number_format($m['net'],3) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f9fafb;border-top:2px solid #e5e7eb">
                    <td style="padding:12px 16px;font-size:12px;font-weight:700;color:#374151">{{ $quarter }} Total</td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:700;color:#059669">OMR {{ number_format($d['totalSales'],3) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:700;color:#111827">OMR {{ number_format($d['outputVAT'],3) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:700;color:#dc2626">OMR {{ number_format($d['purchaseTotal'],3) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:700;color:#374151">OMR {{ number_format($d['inputVAT'],3) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:14px;font-weight:900;color:{{ $d['netVAT']>=0?'#d97706':'#059669' }}">OMR {{ number_format($d['netVAT'],3) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Filing Notice --}}
<div style="padding:16px 20px;border-radius:12px;background:#eff6ff;border:1px solid #bfdbfe;font-size:12px;color:#1e40af;display:flex;align-items:flex-start;gap:10px">
    <span style="font-size:20px;flex-shrink:0">📅</span>
    <div>
        <div style="font-weight:700;margin-bottom:4px">Filing Deadline</div>
        <div>{{ $quarter }} {{ $year }} return must be filed by <strong>28th of the following month</strong> after quarter end ({{ $d['end']->format('d M Y') }}).</div>
        <div style="margin-top:4px">Keep all sales invoices and purchase receipts for at least <strong>5 years</strong> as per Oman VAT law.</div>
    </div>
</div>

</x-filament-panels::page>
