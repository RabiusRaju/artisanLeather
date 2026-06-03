<x-filament-panels::page>
<script src="/js/chart.umd.min.js"></script>
@php
$d = $this->getData();
$stageConfig = [
    'inquiry'       => ['label'=>'Inquiry',    'icon'=>'📩','color'=>'#6b7280'],
    'confirmed'     => ['label'=>'Confirmed',  'icon'=>'✅','color'=>'#3b82f6'],
    'in_production' => ['label'=>'Production', 'icon'=>'🔨','color'=>'#f59e0b'],
    'quality_check' => ['label'=>'QC Check',   'icon'=>'🔍','color'=>'#8b5cf6'],
    'ready'         => ['label'=>'Ready',      'icon'=>'📦','color'=>'#10b981'],
    'delivered'     => ['label'=>'Delivered',  'icon'=>'🎉','color'=>'#22c55e'],
];
@endphp

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">

    <div style="border-radius:14px;border:1px solid #dbeafe;background:linear-gradient(135deg,#eff6ff,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Total Contract Value</span>
            <span style="font-size:18px">📋</span>
        </div>
        <div style="font-size:22px;font-weight:900;color:#2563eb;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalAgreed'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">All active custom orders</div>
    </div>

    <div style="border-radius:14px;border:1px solid #a7f3d0;background:linear-gradient(135deg,#ecfdf5,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Deposits Received</span>
            <span style="font-size:18px">✅</span>
        </div>
        <div style="font-size:22px;font-weight:900;color:#059669;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalDeposit'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">{{ $d['collectionRate'] }}% collection rate</div>
        @if($d['totalAgreed'] > 0)
        <div style="height:4px;background:#e5e7eb;border-radius:99px;overflow:hidden;margin-top:10px">
            <div style="height:100%;width:{{ $d['collectionRate'] }}%;background:#22c55e;border-radius:99px"></div>
        </div>
        @endif
    </div>

    <div style="border-radius:14px;border:1px solid #fde68a;background:linear-gradient(135deg,#fffbeb,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280">Balance Outstanding</span>
            <span style="font-size:18px">💰</span>
        </div>
        <div style="font-size:22px;font-weight:900;color:#d97706;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalBalance'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">{{ $d['pendingDeposit'] }} orders pending deposit</div>
    </div>

</div>

{{-- Pipeline Funnel + Collection Chart --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px">

    {{-- Pipeline --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">🔄 Revenue Pipeline by Stage</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Custom order value at each production stage</div>
        @php
            $pipelineStages = array_keys($stageConfig);
            $pipelineVals   = array_map(fn($s) => $d['pipeline'][$s]['value'], $pipelineStages);
            $pipelineColors = array_map(fn($s) => $stageConfig[$s]['color'], $pipelineStages);
            $pipelineLabels = array_map(fn($s) => $stageConfig[$s]['label'], $pipelineStages);
        @endphp
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                el._ci = new Chart(el, {
                    type:'bar',
                    data:{
                        labels:{{ json_encode($pipelineLabels) }},
                        datasets:[{
                            label:'Order Value (OMR)',
                            data:{{ json_encode($pipelineVals) }},
                            backgroundColor:{{ json_encode(array_map(fn($c) => $c.'cc', $pipelineColors)) }},
                            borderColor:{{ json_encode($pipelineColors) }},
                            borderWidth:2,borderRadius:8
                        }]
                    },
                    options:{
                        responsive:true,maintainAspectRatio:false,
                        plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>'OMR '+c.parsed.y.toFixed(3)}}},
                        scales:{x:{grid:{display:false},ticks:{font:{size:11}}},y:{beginAtZero:true,ticks:{callback:v=>'OMR '+v.toFixed(0)},grid:{color:'rgba(0,0,0,.05)'}}}
                    }
                });
             })"
             style="position:relative;height:200px"><canvas></canvas></div>

        {{-- Stage count grid --}}
        <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:6px;margin-top:16px">
            @foreach($stageConfig as $key => $cfg)
            <div style="text-align:center;padding:8px 4px;border-radius:8px;background:#f9fafb;border-top:3px solid {{ $cfg['color'] }}">
                <div style="font-size:14px">{{ $cfg['icon'] }}</div>
                <div style="font-size:16px;font-weight:900;color:{{ $cfg['color'] }}">{{ $d['pipeline'][$key]['count'] }}</div>
                <div style="font-size:9px;color:#9ca3af;margin-top:2px">{{ $cfg['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Collection Donut --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">💳 Collection Status</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:12px">Deposits vs Balance outstanding</div>
        @php
            $collectVals = [$d['totalDeposit'], $d['totalBalance']];
        @endphp
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                el._ci = new Chart(el, {
                    type:'doughnut',
                    data:{
                        labels:['Collected','Outstanding'],
                        datasets:[{
                            data:{{ json_encode($collectVals) }},
                            backgroundColor:['#22c55e','#f59e0b'],
                            borderWidth:0,hoverOffset:6
                        }]
                    },
                    options:{responsive:true,maintainAspectRatio:false,cutout:'70%',
                        plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>c.label+': OMR '+c.parsed.toFixed(3)}}}}
                });
             })"
             style="position:relative;height:160px"><canvas></canvas></div>

        <div style="margin-top:16px;display:flex;flex-direction:column;gap:10px">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:#ecfdf5;border-radius:8px">
                <div style="display:flex;align-items:center;gap:6px">
                    <div style="width:10px;height:10px;border-radius:50%;background:#22c55e"></div>
                    <span style="font-size:12px;color:#374151;font-weight:600">Collected</span>
                </div>
                <span style="font-size:13px;font-weight:800;color:#059669;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalDeposit'],3) }}</span>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:#fffbeb;border-radius:8px">
                <div style="display:flex;align-items:center;gap:6px">
                    <div style="width:10px;height:10px;border-radius:50%;background:#f59e0b"></div>
                    <span style="font-size:12px;color:#374151;font-weight:600">Outstanding</span>
                </div>
                <span style="font-size:13px;font-weight:800;color:#d97706;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalBalance'],3) }}</span>
            </div>
            <div style="text-align:center;margin-top:4px">
                <div style="font-size:22px;font-weight:900;color:#059669">{{ $d['collectionRate'] }}%</div>
                <div style="font-size:11px;color:#9ca3af">Collection rate</div>
            </div>
        </div>
    </div>

</div>

{{-- Custom Orders Table --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div>
            <div style="font-size:14px;font-weight:700;color:#111827">✂️ Custom Orders — Payment Tracking</div>
            <div style="font-size:11px;color:#9ca3af;margin-top:2px">{{ $d['orders']->count() }} active orders · {{ $d['pendingDeposit'] }} pending deposit</div>
        </div>
        <a href="/admin/custom-orders/create"
           style="font-size:12px;font-weight:600;color:#f59e0b;text-decoration:none;padding:6px 14px;border:1px solid #fde68a;border-radius:8px;background:#fffbeb">
            + New Custom Order
        </a>
    </div>

    @if($d['orders']->isEmpty())
    <div style="display:flex;flex-direction:column;align-items:center;padding:70px 20px;color:#9ca3af">
        <span style="font-size:56px;margin-bottom:16px">✂️</span>
        <div style="font-size:18px;font-weight:700;color:#374151;margin-bottom:6px">No custom orders yet</div>
        <div style="font-size:13px;color:#9ca3af">Create your first bespoke leather order to start tracking</div>
        <a href="/admin/custom-orders/create"
           style="margin-top:20px;font-size:13px;font-weight:600;color:#fff;background:#f59e0b;padding:10px 24px;border-radius:10px;text-decoration:none">
            + Create Custom Order
        </a>
    </div>
    @else
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f9fafb">
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;white-space:nowrap">Reference</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Customer</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Product</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Stage</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Total</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Deposit</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Balance Due</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($d['orders'] as $order)
                @php
                    $balance = $order->agreed_price_omr - ($order->deposit_paid ? $order->deposit_amount_omr : 0);
                    $cfg     = $stageConfig[$order->status] ?? ['label'=>ucfirst($order->status),'icon'=>'📋','color'=>'#6b7280'];
                @endphp
                <tr style="border-top:1px solid #f3f4f6;background:{{ !$order->deposit_paid&&$order->status!=='delivered'?'#fffbeb':'transparent' }}"
                    onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='{{ !$order->deposit_paid&&$order->status!=='delivered'?'#fffbeb':'transparent' }}'">
                    <td style="padding:12px 16px;font-family:monospace;font-size:12px;font-weight:600;color:#374151">{{ $order->reference_number }}</td>
                    <td style="padding:12px 16px">
                        <div style="font-size:13px;font-weight:600;color:#111827">{{ $order->customer_name }}</div>
                        @if($order->customer_phone)
                        <div style="font-size:11px;color:#9ca3af">{{ $order->customer_phone }}</div>
                        @endif
                    </td>
                    <td style="padding:12px 16px;font-size:12px;color:#374151">{{ $order->product_name ?: ucfirst(str_replace('_',' ',$order->product_type)) }}</td>
                    <td style="padding:12px 16px;text-align:center">
                        <span style="font-size:12px;font-weight:700;padding:4px 12px;border-radius:99px;background:{{ $cfg['color'] }}20;color:{{ $cfg['color'] }};white-space:nowrap">
                            {{ $cfg['icon'] }} {{ $cfg['label'] }}
                        </span>
                    </td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:600;color:#374151;font-variant-numeric:tabular-nums">
                        OMR {{ number_format($order->agreed_price_omr,3) }}
                    </td>
                    <td style="padding:12px 16px;text-align:center">
                        @if($order->deposit_paid)
                        <div style="font-size:12px;font-weight:700;color:#059669">✅ OMR {{ number_format($order->deposit_amount_omr,3) }}</div>
                        @else
                        <div style="font-size:12px;font-weight:700;color:#f59e0b">⏳ Pending</div>
                        @endif
                    </td>
                    <td style="padding:12px 16px;text-align:right;font-size:14px;font-weight:800;font-variant-numeric:tabular-nums;color:{{ $balance>0?'#d97706':'#059669' }}">
                        {{ $balance > 0 ? 'OMR '.number_format($balance,3) : '—' }}
                    </td>
                    <td style="padding:12px 16px;text-align:center">
                        <a href="/admin/custom-orders/{{ $order->id }}/edit"
                           style="font-size:12px;font-weight:600;color:#f59e0b;text-decoration:none;padding:4px 12px;border:1px solid #fde68a;border-radius:6px;background:#fffbeb">
                            Update →
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
            @if($d['orders']->count() > 0)
            <tfoot>
                <tr style="background:#f9fafb;border-top:2px solid #e5e7eb">
                    <td colspan="4" style="padding:12px 16px;font-size:13px;font-weight:700;color:#374151">Totals</td>
                    <td style="padding:12px 16px;text-align:right;font-size:14px;font-weight:700;color:#2563eb;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalAgreed'],3) }}</td>
                    <td style="padding:12px 16px;text-align:center;font-size:13px;font-weight:700;color:#059669">OMR {{ number_format($d['totalDeposit'],3) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:14px;font-weight:800;color:#d97706;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalBalance'],3) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    @endif
</div>

</x-filament-panels::page>
