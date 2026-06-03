@php
$d = $this->getData();
$stages = [
    ['key'=>'inquiry',       'label'=>'Inquiry',    'icon'=>'📩', 'color'=>'#6b7280', 'light'=>'#f9fafb'],
    ['key'=>'confirmed',     'label'=>'Confirmed',  'icon'=>'✅', 'color'=>'#2563eb', 'light'=>'#eff6ff'],
    ['key'=>'in_production', 'label'=>'Production', 'icon'=>'🔨', 'color'=>'#d97706', 'light'=>'#fffbeb'],
    ['key'=>'quality_check', 'label'=>'QC Check',   'icon'=>'🔍', 'color'=>'#7c3aed', 'light'=>'#f5f3ff'],
    ['key'=>'ready',         'label'=>'Ready',      'icon'=>'📦', 'color'=>'#059669', 'light'=>'#ecfdf5'],
];
$totalActive = $d['orders']->count();
@endphp

<div style="border-radius:12px;border:1px solid #e5e7eb;background:#fff;overflow:hidden;height:100%">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #f3f4f6">
        <div style="display:flex;align-items:center;gap:8px">
            <span style="font-size:16px">✂️</span>
            <span style="font-weight:600;font-size:14px;color:#111827">Custom Orders</span>
            @if($totalActive > 0)
            <span style="padding:2px 8px;font-size:10px;font-weight:700;border-radius:99px;background:#fef3c7;color:#92400e">{{ $totalActive }} active</span>
            @endif
        </div>
        <a href="/admin/custom-orders" style="font-size:12px;color:#f59e0b;text-decoration:none;font-weight:500">Manage →</a>
    </div>

    {{-- Stage Pipeline --}}
    <div style="padding:14px 16px 8px;display:grid;grid-template-columns:repeat(5,1fr);gap:4px">
        @foreach($stages as $i => $stage)
        @php $count = $d['summary'][$stage['key']] ?? 0; @endphp
        <div style="display:flex;flex-direction:column;align-items:center;gap:3px;padding:8px 4px;border-radius:8px;border:{{ $count>0 ? '2px solid '.$stage['color'] : '1px solid #f3f4f6' }};background:{{ $count>0 ? $stage['light'] : '#f9fafb' }}">
            <span style="font-size:16px;line-height:1">{{ $stage['icon'] }}</span>
            <span style="font-size:20px;font-weight:900;line-height:1;color:{{ $count>0 ? $stage['color'] : '#d1d5db' }};font-variant-numeric:tabular-nums">{{ $count }}</span>
            <span style="font-size:8px;font-weight:500;color:#6b7280;text-align:center;line-height:1.2">{{ $stage['label'] }}</span>
        </div>
        @endforeach
    </div>

    {{-- Active Order List --}}
    <div style="padding:4px 16px 8px;max-height:160px;overflow-y:auto">
        @forelse($d['orders'] as $o)
        @php $sm = collect($stages)->firstWhere('key', $o->status); @endphp
        <a href="/admin/custom-orders/{{ $o->id }}/edit" style="display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;border:1px solid #f3f4f6;margin-bottom:4px;text-decoration:none;background:#fff" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
            <span style="font-size:14px;flex-shrink:0">{{ $sm['icon'] ?? '📋' }}</span>
            <div style="flex:1;min-width:0">
                <div style="font-size:12px;font-weight:600;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $o->customer_name }}</div>
                <div style="font-size:10px;color:#9ca3af">{{ $o->reference_number }} · {{ ucfirst($o->product_type ?? 'Custom') }}</div>
            </div>
            <div style="text-align:right;flex-shrink:0">
                <div style="font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px;background:{{ $sm['color'] ?? '#6b7280' }};color:#fff">{{ ucfirst(str_replace('_',' ',$o->status)) }}</div>
                @if($o->agreed_price_omr)
                <div style="font-size:10px;color:#d97706;font-weight:600;margin-top:2px">OMR {{ number_format($o->agreed_price_omr,3) }}</div>
                @endif
            </div>
        </a>
        @empty
        <div style="display:flex;flex-direction:column;align-items:center;padding:20px;color:#9ca3af">
            <span style="font-size:22px;margin-bottom:4px">✂️</span>
            <span style="font-size:12px">No active custom orders</span>
        </div>
        @endforelse
    </div>

    {{-- Footer --}}
    <div style="padding:10px 20px;border-top:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center">
        <span style="font-size:11px;color:#6b7280">Total active value</span>
        <span style="font-size:14px;font-weight:700;color:#d97706;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalValue'],3) }}</span>
    </div>

</div>
