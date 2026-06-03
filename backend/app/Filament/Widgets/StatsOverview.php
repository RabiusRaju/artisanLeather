<?php
namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use App\Models\Customer;
use App\Models\CustomOrder;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalOrders    = Order::count();
        $pendingOrders  = Order::where('status', 'pending')->count();
        $revenue        = Order::whereNotIn('status', ['cancelled'])->sum('total_omr');
        $totalCustomers = Customer::count();
        $vipCustomers   = Customer::where('status', 'vip')->count();
        $customInProd   = CustomOrder::where('status', 'in_production')->count();
        $unreadMessages = ContactMessage::where('status', 'unread')->count();

        // Phase 2 additions
        $lowStock       = ProductStock::where('quantity', '>', 0)->whereColumn('quantity', '<=', 'minimum_alert')->count();
        $outOfStock     = ProductStock::where('quantity', '<=', 0)->count();
        $unpaidSuppliers= PurchaseOrder::whereNotIn('payment_status', ['paid'])->whereNotIn('status', ['cancelled','draft'])->sum(\DB::raw('total_omr - paid_amount_omr'));
        $pendingReceivable = CustomOrder::whereNotIn('status', ['cancelled'])->where('deposit_paid', false)->count();

        return [
            Stat::make('Total Orders', $totalOrders)
                ->description("{$pendingOrders} pending")
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('Revenue (OMR)', number_format($revenue, 3))
                ->description('All completed orders')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Customers', $totalCustomers)
                ->description("{$vipCustomers} VIP")
                ->color('info')
                ->icon('heroicon-o-users'),

            Stat::make('Custom Orders in Production', $customInProd)
                ->description('Bespoke pieces being crafted')
                ->color('warning')
                ->icon('heroicon-o-scissors'),

            Stat::make('Stock Alert', ($lowStock + $outOfStock) > 0 ? "{$lowStock} low · {$outOfStock} out" : 'All stocked ✅')
                ->description($outOfStock > 0 ? "{$outOfStock} products out of stock" : "{$lowStock} products running low")
                ->color(($lowStock + $outOfStock) > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-archive-box'),

            Stat::make('Supplier Balance Due', 'OMR ' . number_format($unpaidSuppliers, 3))
                ->description("{$pendingReceivable} custom orders awaiting deposit")
                ->color($unpaidSuppliers > 0 ? 'warning' : 'gray')
                ->icon('heroicon-o-credit-card'),
        ];
    }
}
