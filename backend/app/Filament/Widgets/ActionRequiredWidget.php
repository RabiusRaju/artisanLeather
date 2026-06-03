<?php
namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use Filament\Widgets\Widget;

class ActionRequiredWidget extends Widget
{
    protected string $view = 'filament.widgets.action-required';
    protected static ?int $sort = 40;
    protected int|string|array $columnSpan = 1;

    public function getData(): array
    {
        // Unread messages
        $messages = ContactMessage::where('status','unread')
            ->latest()->limit(3)->get(['name','message','created_at']);

        // Orders pending 3+ days
        $stalePending = Order::where('status','pending')
            ->where('created_at','<',now()->subDays(3))
            ->count();

        // Overdue supplier invoices
        $overdueSuppliers = PurchaseOrder::whereNotIn('payment_status',['paid'])
            ->whereNotIn('status',['cancelled','draft'])
            ->where('order_date','<',now()->subDays(30))
            ->with('supplier')
            ->limit(2)->get();

        // Out-of-stock products
        $outOfStock = ProductStock::with('product')
            ->where('quantity','<=',0)->get();

        $totalActions = $messages->count() + ($stalePending > 0 ? 1 : 0) + $overdueSuppliers->count() + $outOfStock->count();

        return compact('messages','stalePending','overdueSuppliers','outOfStock','totalActions');
    }
}
