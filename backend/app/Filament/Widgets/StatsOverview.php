<?php
namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use App\Models\Customer;
use App\Models\CustomOrder;
use App\Models\Expense;
use App\Models\Order;
use App\Models\ProductStock;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 10;

    protected function getStats(): array
    {
        $now = now();

        // ── Build 7-day sparklines ────────────────────────────────────────────
        $revenueSparkline  = [];
        $ordersSparkline   = [];
        $customersSparkline= [];

        for ($i = 6; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->toDateString();
            $revenueSparkline[]   = (float)(Order::whereNotIn('status',['cancelled'])->whereDate('created_at',$d)->sum('total_omr')
                                          + CustomOrder::whereIn('status',['ready','delivered'])->whereDate('created_at',$d)->sum('agreed_price_omr'));
            $ordersSparkline[]    = Order::whereDate('created_at',$d)->count();
            $customersSparkline[] = Customer::whereDate('created_at',$d)->count();
        }

        // ── Revenue ───────────────────────────────────────────────────────────
        $revToday     = $revenueSparkline[6];
        $revYesterday = $revenueSparkline[5];
        $revThisMonth = (float)(Order::whereNotIn('status',['cancelled'])->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->sum('total_omr')
                      + CustomOrder::whereIn('status',['ready','delivered'])->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->sum('agreed_price_omr'));
        $revLastMonth = (float)(Order::whereNotIn('status',['cancelled'])->whereMonth('created_at',$now->copy()->subMonth()->month)->whereYear('created_at',$now->copy()->subMonth()->year)->sum('total_omr')
                      + CustomOrder::whereIn('status',['ready','delivered'])->whereMonth('created_at',$now->copy()->subMonth()->month)->whereYear('created_at',$now->copy()->subMonth()->year)->sum('agreed_price_omr'));
        $revenueGrowth = $revLastMonth > 0 ? round((($revThisMonth - $revLastMonth) / $revLastMonth) * 100, 1) : 0;

        // 6-month revenue sparkline for monthly card
        $monthSparkline = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $monthSparkline[] = round((float)(Order::whereNotIn('status',['cancelled'])->whereMonth('created_at',$m->month)->whereYear('created_at',$m->year)->sum('total_omr')
                              + CustomOrder::whereIn('status',['ready','delivered'])->whereMonth('created_at',$m->month)->whereYear('created_at',$m->year)->sum('agreed_price_omr')),3);
        }

        // ── Orders ────────────────────────────────────────────────────────────
        $ordersToday    = $ordersSparkline[6];
        $ordersThisMonth= Order::whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->whereNotIn('status',['cancelled'])->count();
        $pendingOrders  = Order::where('status','pending')->count();

        // ── Profit ────────────────────────────────────────────────────────────
        $expensesThisMonth = (float) Expense::whereMonth('expense_date',$now->month)->whereYear('expense_date',$now->year)->sum('amount_omr');
        $profitThisMonth   = $revThisMonth - $expensesThisMonth;
        $marginPct         = $revThisMonth > 0 ? round(($profitThisMonth / $revThisMonth) * 100, 1) : 0;

        // 6-month profit sparkline
        $profitSparkline = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $r = (float)(Order::whereNotIn('status',['cancelled'])->whereMonth('created_at',$m->month)->whereYear('created_at',$m->year)->sum('total_omr'));
            $e = (float) Expense::whereMonth('expense_date',$m->month)->whereYear('expense_date',$m->year)->sum('amount_omr');
            $profitSparkline[] = max(0, $r - $e);
        }

        // ── Customers ─────────────────────────────────────────────────────────
        $newCustomers   = Customer::whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->count();
        $totalCustomers = Customer::count();
        $unread         = ContactMessage::where('status','unread')->count();

        // ── Stock ─────────────────────────────────────────────────────────────
        $lowStock = ProductStock::where('quantity','>',0)->whereColumn('quantity','<=','minimum_alert')->count();
        $outStock = ProductStock::where('quantity','<=',0)->count();
        $customInProd = CustomOrder::where('status','in_production')->count();
        $customReady  = CustomOrder::where('status','ready')->count();

        return [
            Stat::make('Revenue Today', 'OMR ' . number_format($revToday, 3))
                ->description(($revToday >= $revYesterday ? '↑ ' : '↓ ') . 'Yesterday: OMR ' . number_format($revYesterday, 3))
                ->color($revToday >= $revYesterday ? 'success' : 'warning')
                ->chart($revenueSparkline)
                ->chartColor($revToday >= $revYesterday ? 'success' : 'warning')
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('Revenue This Month', 'OMR ' . number_format($revThisMonth, 3))
                ->description(($revenueGrowth >= 0 ? '↑ ' : '↓ ') . abs($revenueGrowth) . '% vs last month')
                ->color($revenueGrowth >= 0 ? 'success' : 'danger')
                ->chart($monthSparkline)
                ->chartColor($revenueGrowth >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Profit This Month', 'OMR ' . number_format($profitThisMonth, 3))
                ->description("{$marginPct}% margin — exp: OMR " . number_format($expensesThisMonth, 3))
                ->color($profitThisMonth >= 0 ? 'success' : 'danger')
                ->chart($profitSparkline)
                ->chartColor($profitThisMonth >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-arrow-trending-up'),

            Stat::make('Orders This Month', $ordersThisMonth . ' orders')
                ->description("{$ordersToday} today · {$pendingOrders} pending")
                ->color($pendingOrders > 0 ? 'warning' : 'success')
                ->chart($ordersSparkline)
                ->chartColor('info')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('Customers', $totalCustomers . ' total')
                ->description("{$newCustomers} new this month" . ($unread > 0 ? " · {$unread} unread" : ''))
                ->color($unread > 0 ? 'warning' : 'info')
                ->chart($customersSparkline)
                ->chartColor('info')
                ->icon('heroicon-o-users'),

            Stat::make('Needs Attention', ($lowStock + $outStock) > 0 ? "{$outStock} out · {$lowStock} low" : 'All stocked ✅')
                ->description($customInProd > 0 ? "{$customInProd} in production · {$customReady} ready" : 'No custom orders in production')
                ->color(($outStock + $lowStock) > 0 ? 'danger' : ($customInProd > 0 ? 'warning' : 'success'))
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
