@php $d = $this->getData(); @endphp

<div style="border-radius:12px;border:1px solid {{ $d['totalActions']>0 ? '#fecaca' : '#bbf7d0' }};background:#fff;overflow:hidden;height:100%">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;background:{{ $d['totalActions']>0 ? '#fef2f2' : '#f0fdf4' }};border-bottom:1px solid {{ $d['totalActions']>0 ? '#fecaca' : '#bbf7d0' }}">
        <div style="display:flex;align-items:center;gap:8px">
            <span style="font-size:16px">{{ $d['totalActions']>0 ? '⚠️' : '✅' }}</span>
            <span style="font-weight:600;font-size:14px;color:{{ $d['totalActions']>0 ? '#991b1b' : '#14532d' }}">
                {{ $d['totalActions']>0 ? 'Action Required' : 'All Clear' }}
            </span>
        </div>
        @if($d['totalActions'] > 0)
        <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;font-size:11px;font-weight:900;border-radius:50%;background:#dc2626;color:#fff">{{ $d['totalActions'] }}</span>
        @endif
    </div>

    @if($d['totalActions'] === 0)
    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 20px;color:#9ca3af">
        <span style="font-size:32px;margin-bottom:8px">🎉</span>
        <span style="font-size:14px;font-weight:500;color:#16a34a">Nothing needs attention!</span>
        <span style="font-size:12px;color:#9ca3af;margin-top:4px">Check back later</span>
    </div>
    @else

    {{-- Alert rows --}}
    @foreach($d['messages'] as $msg)
    <a href="/admin/contact-messages" style="display:flex;align-items:flex-start;gap:12px;padding:14px 20px;border-bottom:1px solid #f9fafb;text-decoration:none;background:#fff" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#fff'">
        <div style="width:34px;height:34px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:16px">✉️</div>
        <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:2px">
                <span style="font-size:12px;font-weight:600;color:#1e293b">{{ $msg->name }}</span>
                <span style="font-size:9px;padding:1px 6px;border-radius:99px;background:#dbeafe;color:#1d4ed8;font-weight:600">New</span>
            </div>
            <div style="font-size:11px;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $msg->message }}</div>
            <div style="font-size:10px;color:#94a3b8;margin-top:2px">{{ $msg->created_at->diffForHumans() }}</div>
        </div>
        <span style="color:#93c5fd;font-size:14px;margin-top:8px">→</span>
    </a>
    @endforeach

    @if($d['stalePending'] > 0)
    <a href="/admin/orders?status=pending" style="display:flex;align-items:flex-start;gap:12px;padding:14px 20px;border-bottom:1px solid #f9fafb;text-decoration:none;background:#fff" onmouseover="this.style.background='#fffbeb'" onmouseout="this.style.background='#fff'">
        <div style="width:34px;height:34px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:16px">🕐</div>
        <div style="flex:1">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:2px">
                <span style="font-size:12px;font-weight:600;color:#1e293b">{{ $d['stalePending'] }} order{{ $d['stalePending']>1?'s':'' }} stale</span>
                <span style="font-size:9px;padding:1px 6px;border-radius:99px;background:#fef3c7;color:#92400e;font-weight:600">Urgent</span>
            </div>
            <div style="font-size:11px;color:#64748b">Pending for 3+ days — needs confirmation</div>
        </div>
        <span style="color:#fbbf24;font-size:14px;margin-top:8px">→</span>
    </a>
    @endif

    @foreach($d['overdueSuppliers'] as $po)
    <a href="/admin/finance/purchase-orders/{{ $po->id }}/edit" style="display:flex;align-items:flex-start;gap:12px;padding:14px 20px;border-bottom:1px solid #f9fafb;text-decoration:none;background:#fff" onmouseover="this.style.background='#fff7ed'" onmouseout="this.style.background='#fff'">
        <div style="width:34px;height:34px;border-radius:50%;background:#ffedd5;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:16px">🧾</div>
        <div style="flex:1">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:2px">
                <span style="font-size:12px;font-weight:600;color:#1e293b">{{ $po->supplier?->name }}</span>
                <span style="font-size:9px;padding:1px 6px;border-radius:99px;background:#ffedd5;color:#c2410c;font-weight:600">Overdue</span>
            </div>
            <div style="font-size:11px;color:#64748b">OMR {{ number_format($po->total_omr - $po->paid_amount_omr, 3) }} outstanding</div>
        </div>
        <span style="color:#fdba74;font-size:14px;margin-top:8px">→</span>
    </a>
    @endforeach

    @foreach($d['outOfStock']->take(2) as $stock)
    <a href="/admin/operations/inventory/inventories" style="display:flex;align-items:flex-start;gap:12px;padding:14px 20px;border-bottom:1px solid #f9fafb;text-decoration:none;background:#fff" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff'">
        <div style="width:34px;height:34px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:16px">📦</div>
        <div style="flex:1">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:2px">
                <span style="font-size:12px;font-weight:600;color:#1e293b">{{ $stock->product?->name }}</span>
                <span style="font-size:9px;padding:1px 6px;border-radius:99px;background:#fee2e2;color:#b91c1c;font-weight:600">Out of Stock</span>
            </div>
            <div style="font-size:11px;color:#64748b">Reorder {{ $stock->reorder_qty ?? 10 }} units immediately</div>
        </div>
        <span style="color:#fca5a5;font-size:14px;margin-top:8px">→</span>
    </a>
    @endforeach

    @endif
</div>
