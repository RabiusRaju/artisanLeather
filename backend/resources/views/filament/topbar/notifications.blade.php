@php
use App\Models\ContactMessage;
use App\Models\CustomOrder;
use App\Models\Order;
use App\Models\ProductStock;

$pendingOrders  = Order::where('status', 'pending')->count();
$unreadMessages = ContactMessage::where('status', 'unread')->count();
$stockAlerts    = ProductStock::where('quantity', '<=', 0)->count()
                + ProductStock::where('quantity', '>', 0)->whereColumn('quantity', '<=', 'minimum_alert')->count();
$customOrders   = CustomOrder::whereNotIn('status', ['cancelled', 'delivered'])->count();
@endphp

<div style="display:flex;align-items:center;gap:4px;margin-right:8px">

    {{-- 📦 Pending Orders --}}
    <a href="/admin/orders?status=pending"
       title="{{ $pendingOrders }} pending orders"
       style="position:relative;display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;color:{{ $pendingOrders > 0 ? '#d97706' : '#9ca3af' }};text-decoration:none;background:{{ $pendingOrders > 0 ? 'rgba(245,158,11,0.1)' : 'transparent' }};transition:background .2s"
       onmouseover="this.style.background='rgba(245,158,11,0.15)'"
       onmouseout="this.style.background='{{ $pendingOrders > 0 ? 'rgba(245,158,11,0.1)' : 'transparent' }}'">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
        </svg>
        @if($pendingOrders > 0)
        <span style="position:absolute;top:2px;right:2px;min-width:16px;height:16px;padding:0 4px;background:#f59e0b;color:#fff;font-size:9px;font-weight:800;border-radius:99px;display:flex;align-items:center;justify-content:center;line-height:1">
            {{ $pendingOrders > 99 ? '99+' : $pendingOrders }}
        </span>
        @endif
    </a>

    {{-- ✉️ Unread Contact Messages --}}
    <a href="/admin/contact-messages"
       title="{{ $unreadMessages }} unread messages"
       style="position:relative;display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;color:{{ $unreadMessages > 0 ? '#2563eb' : '#9ca3af' }};text-decoration:none;background:{{ $unreadMessages > 0 ? 'rgba(37,99,235,0.1)' : 'transparent' }};transition:background .2s"
       onmouseover="this.style.background='rgba(37,99,235,0.15)'"
       onmouseout="this.style.background='{{ $unreadMessages > 0 ? 'rgba(37,99,235,0.1)' : 'transparent' }}'">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
        </svg>
        @if($unreadMessages > 0)
        <span style="position:absolute;top:2px;right:2px;min-width:16px;height:16px;padding:0 4px;background:#2563eb;color:#fff;font-size:9px;font-weight:800;border-radius:99px;display:flex;align-items:center;justify-content:center;line-height:1">
            {{ $unreadMessages > 99 ? '99+' : $unreadMessages }}
        </span>
        @endif
    </a>

    {{-- 📦 Stock Alerts --}}
    <a href="/admin/operations/inventory/inventories"
       title="{{ $stockAlerts }} stock alerts"
       style="position:relative;display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;color:{{ $stockAlerts > 0 ? '#dc2626' : '#9ca3af' }};text-decoration:none;background:{{ $stockAlerts > 0 ? 'rgba(220,38,38,0.1)' : 'transparent' }};transition:background .2s"
       onmouseover="this.style.background='rgba(220,38,38,0.15)'"
       onmouseout="this.style.background='{{ $stockAlerts > 0 ? 'rgba(220,38,38,0.1)' : 'transparent' }}'">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px">
            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
        </svg>
        @if($stockAlerts > 0)
        <span style="position:absolute;top:2px;right:2px;min-width:16px;height:16px;padding:0 4px;background:#dc2626;color:#fff;font-size:9px;font-weight:800;border-radius:99px;display:flex;align-items:center;justify-content:center;line-height:1">
            {{ $stockAlerts > 99 ? '99+' : $stockAlerts }}
        </span>
        @endif
    </a>

    {{-- ✂️ Custom Orders in Progress --}}
    <a href="/admin/custom-orders"
       title="{{ $customOrders }} custom orders in progress"
       style="position:relative;display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;color:{{ $customOrders > 0 ? '#7c3aed' : '#9ca3af' }};text-decoration:none;background:{{ $customOrders > 0 ? 'rgba(124,58,237,0.1)' : 'transparent' }};transition:background .2s"
       onmouseover="this.style.background='rgba(124,58,237,0.15)'"
       onmouseout="this.style.background='{{ $customOrders > 0 ? 'rgba(124,58,237,0.1)' : 'transparent' }}'">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
        </svg>
        @if($customOrders > 0)
        <span style="position:absolute;top:2px;right:2px;min-width:16px;height:16px;padding:0 4px;background:#7c3aed;color:#fff;font-size:9px;font-weight:800;border-radius:99px;display:flex;align-items:center;justify-content:center;line-height:1">
            {{ $customOrders > 99 ? '99+' : $customOrders }}
        </span>
        @endif
    </a>

    {{-- Divider --}}
    <div style="width:1px;height:20px;background:rgba(0,0,0,0.08);margin:0 4px"></div>

</div>
