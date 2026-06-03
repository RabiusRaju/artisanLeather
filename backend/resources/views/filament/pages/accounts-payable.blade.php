<x-filament-panels::page>
<script src="/js/chart.umd.min.js"></script>
@php $d = $this->getData(); @endphp

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">

    <div style="border-radius:14px;border:1px solid #fecaca;background:linear-gradient(135deg,#fef2f2,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Total Outstanding</span>
            <span style="font-size:18px">💳</span>
        </div>
        <div style="font-size:22px;font-weight:900;color:#dc2626;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalOwed'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Owed to suppliers</div>
    </div>

    <div style="border-radius:14px;border:1px solid #fed7aa;background:linear-gradient(135deg,#fff7ed,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Overdue (30+ days)</span>
            <span style="font-size:18px">⚠️</span>
        </div>
        <div style="font-size:32px;font-weight:900;color:#ea580c">{{ $d['overdueCount'] }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Purchase orders overdue</div>
    </div>

    <div style="border-radius:14px;border:1px solid #a7f3d0;background:linear-gradient(135deg,#ecfdf5,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Total Paid (All Time)</span>
            <span style="font-size:18px">✅</span>
        </div>
        <div style="font-size:22px;font-weight:900;color:#059669;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalPaid'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Settled with suppliers</div>
    </div>

    <div style="border-radius:14px;border:1px solid #dbeafe;background:linear-gradient(135deg,#eff6ff,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Open POs</span>
            <span style="font-size:18px">📋</span>
        </div>
        <div style="font-size:32px;font-weight:900;color:#2563eb">{{ $d['orders']->count() }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Awaiting full payment</div>
    </div>

</div>

{{-- Aging + Supplier Chart Row --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">

    {{-- Aging Buckets --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">⏱️ Payable Aging Analysis</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Outstanding balance by age</div>
        @php
            $agingColors = ['#22c55e','#f59e0b','#f97316','#ef4444'];
            $agingLabels = array_keys($d['aging']);
            $agingVals   = array_values($d['aging']);
        @endphp
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                el._ci = new Chart(el, {
                    type:'bar',
                    data:{
                        labels:{{ json_encode($agingLabels) }},
                        datasets:[{
                            data:{{ json_encode($agingVals) }},
                            backgroundColor:{{ json_encode($agingColors) }},
                            borderWidth:0,borderRadius:8
                        }]
                    },
                    options:{
                        responsive:true,maintainAspectRatio:false,
                        plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>'OMR '+c.parsed.y.toFixed(3)}}},
                        scales:{x:{grid:{display:false},ticks:{font:{size:11}}},y:{beginAtZero:true,ticks:{callback:v=>'OMR '+v.toFixed(0)},grid:{color:'rgba(0,0,0,.05)'}}}
                    }
                });
             })"
             style="position:relative;height:180px"><canvas></canvas></div>

        {{-- Aging buckets summary --}}
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-top:16px">
            @foreach($d['aging'] as $bucket => $amount)
            @php $bi = $loop->index; $bc = $agingColors[$bi]; @endphp
            <div style="padding:10px 12px;border-radius:8px;background:#f9fafb;border-left:3px solid {{ $bc }}">
                <div style="font-size:10px;color:#9ca3af;margin-bottom:3px">{{ $bucket }}</div>
                <div style="font-size:14px;font-weight:800;color:{{ $bc }};font-variant-numeric:tabular-nums">OMR {{ number_format($amount,3) }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- By Supplier --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">🏭 Outstanding by Supplier</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Balance owed per supplier</div>
        @if($d['bySupplier']->count() > 0)
        @php
            $supLabels = $d['bySupplier']->pluck('name')->toJson();
            $supVals   = $d['bySupplier']->pluck('balance')->toJson();
        @endphp
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                const vals = {{ $supVals }};
                const maxV = Math.max(...vals)||1;
                el._ci = new Chart(el, {
                    type:'bar',
                    data:{
                        labels:{{ $supLabels }},
                        datasets:[{
                            data:vals,
                            backgroundColor:vals.map(v=>`rgba(239,68,68,${(0.4+v/maxV*0.55).toFixed(2)})`),
                            borderColor:'#ef4444',borderWidth:1.5,borderRadius:{topRight:6,bottomRight:6},borderSkipped:'left'
                        }]
                    },
                    options:{
                        indexAxis:'y',responsive:true,maintainAspectRatio:false,
                        plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>'OMR '+c.parsed.x.toFixed(3)}}},
                        scales:{x:{beginAtZero:true,ticks:{callback:v=>'OMR '+v.toFixed(0)}},y:{grid:{display:false},ticks:{font:{size:12}}}}
                    }
                });
             })"
             style="position:relative;height:{{ max(140, $d['bySupplier']->count()*52) }}px"><canvas></canvas></div>
        @else
        <div style="display:flex;flex-direction:column;align-items:center;padding:60px 0;color:#9ca3af">
            <span style="font-size:40px;margin-bottom:12px">🏭</span>
            <div style="font-size:14px;font-weight:600;color:#059669">All suppliers paid!</div>
            <div style="font-size:12px;color:#9ca3af;margin-top:4px">No outstanding balances</div>
        </div>
        @endif
    </div>

</div>

{{-- Outstanding PO Table --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div>
            <div style="font-size:14px;font-weight:700;color:#111827">📋 Outstanding Payments to Suppliers</div>
            <div style="font-size:11px;color:#9ca3af;margin-top:2px">{{ $d['orders']->count() }} purchase orders awaiting payment</div>
        </div>
        <a href="/admin/finance/purchase-orders/create"
           style="font-size:12px;font-weight:600;color:#f59e0b;text-decoration:none;padding:6px 14px;border:1px solid #fde68a;border-radius:8px;background:#fffbeb">
            + New PO
        </a>
    </div>

    @if($d['orders']->isEmpty())
    <div style="display:flex;flex-direction:column;align-items:center;padding:70px 20px;color:#9ca3af">
        <span style="font-size:56px;margin-bottom:16px">🎉</span>
        <div style="font-size:18px;font-weight:700;color:#059669;margin-bottom:6px">All suppliers paid!</div>
        <div style="font-size:13px;color:#9ca3af">No outstanding purchase orders. Great financial health!</div>
        <a href="/admin/finance/purchase-orders/create"
           style="margin-top:20px;font-size:13px;font-weight:600;color:#fff;background:#f59e0b;padding:10px 24px;border-radius:10px;text-decoration:none">
            + Create Purchase Order
        </a>
    </div>
    @else
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f9fafb">
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;white-space:nowrap">PO Number</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Supplier</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Order Date</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Total</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Paid</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Balance Due</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Age</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Status</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($d['orders'] as $order)
                @php
                    $balance = $order->total_omr - $order->paid_amount_omr;
                    $days    = $order->order_date->diffInDays(now());
                    $isOld   = $days > 30;
                    $ageColor = $days <= 30 ? '#22c55e' : ($days <= 60 ? '#f59e0b' : ($days <= 90 ? '#f97316' : '#ef4444'));
                @endphp
                <tr style="border-top:1px solid #f3f4f6;background:{{ $isOld?'#fff7ed':'transparent' }}"
                    onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='{{ $isOld?'#fff7ed':'transparent' }}'">
                    <td style="padding:12px 16px;font-family:monospace;font-size:12px;font-weight:600;color:#374151">{{ $order->reference_number }}</td>
                    <td style="padding:12px 16px;font-size:13px;font-weight:600;color:#111827">{{ $order->supplier?->name ?? '—' }}</td>
                    <td style="padding:12px 16px;font-size:12px;color:#6b7280;white-space:nowrap">
                        {{ $order->order_date->format('d M Y') }}
                        @if($isOld)
                        <div style="font-size:10px;font-weight:700;color:#ea580c;margin-top:2px">⚠️ Overdue</div>
                        @endif
                    </td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;color:#374151;font-variant-numeric:tabular-nums">OMR {{ number_format($order->total_omr,3) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;color:#059669;font-variant-numeric:tabular-nums">OMR {{ number_format($order->paid_amount_omr,3) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:14px;font-weight:800;color:#dc2626;font-variant-numeric:tabular-nums">OMR {{ number_format($balance,3) }}</td>
                    <td style="padding:12px 16px;text-align:center">
                        <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;background:{{ $ageColor }}20;color:{{ $ageColor }}">
                            {{ $days }}d
                        </span>
                    </td>
                    <td style="padding:12px 16px;text-align:center">
                        <span style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:99px;background:{{ $order->payment_status==='partial'?'#fef3c7':'#fee2e2' }};color:{{ $order->payment_status==='partial'?'#92400e':'#991b1b' }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </td>
                    <td style="padding:12px 16px;text-align:center">
                        <a href="/admin/finance/purchase-orders/{{ $order->id }}/edit"
                           style="font-size:12px;font-weight:600;color:#f59e0b;text-decoration:none;padding:4px 12px;border:1px solid #fde68a;border-radius:6px;background:#fffbeb">
                            Pay →
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#fef2f2;border-top:2px solid #fecaca">
                    <td colspan="5" style="padding:12px 16px;font-size:13px;font-weight:700;color:#991b1b">Total Outstanding</td>
                    <td style="padding:12px 16px;text-align:right;font-size:15px;font-weight:900;color:#dc2626;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalOwed'],3) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
</div>

</x-filament-panels::page>
