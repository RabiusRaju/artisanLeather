<x-filament-panels::page>
<script src="/js/chart.umd.min.js"></script>
@php $d = $this->getData(); @endphp

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">
    <div style="border-radius:14px;border:1px solid #dbeafe;background:linear-gradient(135deg,#eff6ff,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:10px">💰 Actual Revenue (6M)</div>
        <div style="font-size:26px;font-weight:900;color:#2563eb;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalActual'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Last 6 months combined</div>
    </div>
    <div style="border-radius:14px;border:1px solid #fde68a;background:linear-gradient(135deg,#fffbeb,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:10px">🎯 Revenue Target (6M)</div>
        <div style="font-size:26px;font-weight:900;color:#d97706;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalTarget'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">{{ $d['totalTarget']>0?'Budget set':'No budgets set yet' }}</div>
    </div>
    <div style="border-radius:14px;border:1px solid {{ $d['achievement']>=100?'#a7f3d0':($d['achievement']>=70?'#fde68a':'#fecaca') }};background:linear-gradient(135deg,{{ $d['achievement']>=100?'#ecfdf5':($d['achievement']>=70?'#fffbeb':'#fef2f2') }},#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:10px">{{ $d['achievement']>=100?'✅':'📊' }} Target Achievement</div>
        <div style="font-size:32px;font-weight:900;color:{{ $d['achievement']>=100?'#059669':($d['achievement']>=70?'#d97706':'#dc2626') }}">{{ $d['achievement'] }}%</div>
        <div style="height:6px;background:#f3f4f6;border-radius:99px;overflow:hidden;margin-top:10px">
            <div style="height:100%;width:{{ min($d['achievement'],100) }}%;background:{{ $d['achievement']>=100?'#22c55e':($d['achievement']>=70?'#f59e0b':'#ef4444') }};border-radius:99px"></div>
        </div>
    </div>
</div>

{{-- Main Chart --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:16px">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px">
        <div>
            <div style="font-size:14px;font-weight:700;color:#111827">📈 Revenue: Actual vs Target + 3-Month Forecast</div>
            <div style="font-size:11px;color:#9ca3af;margin-top:2px">Green = Actual · Amber dashed = Target · Purple dotted = Forecast</div>
        </div>
        <a href="/admin/compliance/budgets/create" style="font-size:12px;font-weight:600;color:#f59e0b;text-decoration:none;padding:6px 14px;border:1px solid #fde68a;border-radius:8px;background:#fffbeb">+ Set Budget</a>
    </div>
    @php
        $allLabels  = [...collect($d['months'])->pluck('label')->toArray(), ...collect($d['forecast'])->pluck('label')->toArray()];
        $actual     = [...collect($d['months'])->pluck('actual_revenue')->toArray(), ...array_fill(0,3,null)];
        $target     = [...collect($d['months'])->pluck('target_revenue')->toArray(), ...array_fill(0,3,null)];
        $forecastArr= [...array_fill(0,6,null), ...collect($d['forecast'])->pluck('revenue')->toArray()];
        $expenses   = [...collect($d['months'])->pluck('actual_expense')->toArray(), ...array_fill(0,3,null)];
    @endphp
    <div x-data="{}"
         x-init="$nextTick(() => {
            const el = $el.querySelector('canvas');
            if (!el || !window.Chart) return;
            if (el._ci) { el._ci.destroy(); el._ci = null; }
            el._ci = new Chart(el, {
                type:'line',
                data:{
                    labels:{{ json_encode($allLabels) }},
                    datasets:[
                        {label:'Actual Revenue',data:{{ json_encode($actual) }},borderColor:'#22c55e',backgroundColor:'rgba(34,197,94,.1)',borderWidth:2.5,pointRadius:4,pointBackgroundColor:'#22c55e',fill:true,tension:0.3,spanGaps:false},
                        {label:'Revenue Target',data:{{ json_encode($target) }},borderColor:'#f59e0b',backgroundColor:'transparent',borderWidth:2,borderDash:[6,3],pointRadius:3,tension:0.3,spanGaps:false},
                        {label:'Forecast',data:{{ json_encode($forecastArr) }},borderColor:'#8b5cf6',backgroundColor:'rgba(139,92,246,.08)',borderWidth:2,borderDash:[3,3],pointRadius:5,pointStyle:'diamond',pointBackgroundColor:'#8b5cf6',fill:true,tension:0.3,spanGaps:false},
                        {label:'Expenses',data:{{ json_encode($expenses) }},borderColor:'#ef4444',backgroundColor:'rgba(239,68,68,.08)',borderWidth:1.5,borderDash:[4,2],pointRadius:3,fill:true,tension:0.3,spanGaps:false}
                    ]
                },
                options:{
                    responsive:true,maintainAspectRatio:false,
                    interaction:{mode:'index',intersect:false},
                    plugins:{
                        legend:{display:true,position:'top',align:'end',labels:{boxWidth:12,padding:10,usePointStyle:true,font:{size:10}}},
                        tooltip:{callbacks:{label:c=>c.dataset.label+': OMR '+(c.parsed.y||0).toFixed(3)}}
                    },
                    scales:{
                        x:{grid:{display:false},ticks:{font:{size:10}}},
                        y:{beginAtZero:true,ticks:{callback:v=>'OMR '+v.toFixed(0)},grid:{color:'rgba(0,0,0,.05)'}}
                    }
                }
            });
         })"
         style="position:relative;height:260px"><canvas></canvas></div>
</div>

{{-- Monthly Table --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:16px">
    <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div style="font-size:14px;font-weight:700;color:#111827">Monthly Budget vs Actual — Last 6 Months</div>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f9fafb">
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Month</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Actual Rev.</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Target</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Gap</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Expenses</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Profit</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($d['months'] as $m)
                @php $profit = $m['actual_revenue'] - $m['actual_expense']; @endphp
                <tr style="border-top:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:12px 16px;font-size:13px;font-weight:600;color:#374151">{{ $m['label'] }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:700;color:#059669;font-variant-numeric:tabular-nums">OMR {{ number_format($m['actual_revenue'],3) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:12px;color:#d97706;font-variant-numeric:tabular-nums">{{ $m['has_budget']?'OMR '.number_format($m['target_revenue'],3):'—' }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:12px;font-variant-numeric:tabular-nums">
                        @if($m['has_budget'])
                        <span style="color:{{ $m['revenue_gap']<=0?'#059669':'#dc2626' }};font-weight:600">
                            {{ $m['revenue_gap']<=0?'▲ +'.number_format(abs($m['revenue_gap']),3):'▼ '.number_format($m['revenue_gap'],3) }}
                        </span>
                        @else<span style="color:#9ca3af">—</span>@endif
                    </td>
                    <td style="padding:12px 16px;text-align:right;font-size:12px;color:#dc2626;font-variant-numeric:tabular-nums">OMR {{ number_format($m['actual_expense'],3) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:700;color:{{ $profit>=0?'#059669':'#dc2626' }};font-variant-numeric:tabular-nums">
                        OMR {{ number_format($profit,3) }}
                    </td>
                    <td style="padding:12px 16px;text-align:center">
                        @if(!$m['has_budget'])
                        <span style="font-size:11px;color:#9ca3af">No budget</span>
                        @elseif($m['revenue_gap']<=0)
                        <span style="font-size:11px;font-weight:700;padding:2px 10px;border-radius:99px;background:#d1fae5;color:#065f46">On Track ✅</span>
                        @else
                        <span style="font-size:11px;font-weight:700;padding:2px 10px;border-radius:99px;background:#fee2e2;color:#991b1b">Behind ⚠️</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- 3-Month Forecast --}}
<div style="border-radius:14px;border:1px solid #e9d5ff;background:linear-gradient(135deg,#faf5ff,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
    <div style="font-size:14px;font-weight:700;color:#7c3aed;margin-bottom:4px">🔮 3-Month Revenue Forecast</div>
    <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Based on rolling 3-month average with 2% monthly growth projection</div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
        @foreach($d['forecast'] as $i => $f)
        @php $profit = $f['revenue'] - $f['expense']; @endphp
        <div style="background:#fff;border-radius:12px;padding:16px 18px;border:1px solid #e9d5ff;box-shadow:0 1px 4px rgba(0,0,0,.04)">
            <div style="font-size:12px;font-weight:600;color:#6b7280;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between">
                <span>{{ $f['label'] }}</span>
                <span style="font-size:10px;padding:2px 6px;border-radius:99px;background:#ede9fe;color:#7c3aed">+{{ $i*2+2 }}% est.</span>
            </div>
            <div style="font-size:20px;font-weight:900;color:#7c3aed;font-variant-numeric:tabular-nums;margin-bottom:4px">OMR {{ number_format($f['revenue'],3) }}</div>
            <div style="font-size:11px;color:#6b7280">Est. revenue</div>
            <div style="height:1px;background:#f3f4f6;margin:10px 0"></div>
            <div style="display:flex;justify-content:space-between;font-size:11px">
                <span style="color:#dc2626">Exp: OMR {{ number_format($f['expense'],3) }}</span>
                <span style="color:{{ $profit>=0?'#059669':'#dc2626' }};font-weight:700">P: OMR {{ number_format($profit,3) }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

</x-filament-panels::page>
