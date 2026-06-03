<?php
namespace App\Filament\Pages\Analytics;

use App\Models\CustomOrder;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class SalesDashboard extends Page
{
    protected string $view = 'filament.pages.analytics.sales-dashboard';
    public static function getNavigationIcon(): string  { return 'heroicon-o-chart-pie'; }
    public static function getNavigationGroup(): string { return 'Analytics'; }
    public static function getNavigationSort(): int     { return 1; }
    public function getTitle(): string                  { return 'Sales Dashboard'; }

    public function getData(): array
    {
        $now  = now();
        $cmS  = $now->copy()->startOfMonth();
        $cmE  = $now->copy()->endOfMonth();
        $lmS  = $now->copy()->subMonth()->startOfMonth();
        $lmE  = $now->copy()->subMonth()->endOfMonth();

        $revCM   = (float)(Order::whereNotIn('status',['cancelled'])->whereBetween('created_at',[$cmS,$cmE])->sum('total_omr')
                 + CustomOrder::whereIn('status',['ready','delivered'])->whereBetween('created_at',[$cmS,$cmE])->sum('agreed_price_omr'));
        $revLM   = (float)(Order::whereNotIn('status',['cancelled'])->whereBetween('created_at',[$lmS,$lmE])->sum('total_omr')
                 + CustomOrder::whereIn('status',['ready','delivered'])->whereBetween('created_at',[$lmS,$lmE])->sum('agreed_price_omr'));
        $ordCM   = Order::whereNotIn('status',['cancelled'])->whereBetween('created_at',[$cmS,$cmE])->count();
        $ordLM   = Order::whereNotIn('status',['cancelled'])->whereBetween('created_at',[$lmS,$lmE])->count();
        $avgVal  = $ordCM > 0 ? round($revCM / $ordCM, 3) : 0;
        $growth  = $revLM > 0 ? round((($revCM - $revLM) / $revLM) * 100, 1) : 0;

        // Monthly revenue — last 12 months
        $monthly = [];
        for ($i = 11; $i >= 0; $i--) {
            $s = $now->copy()->subMonths($i)->startOfMonth();
            $e = $now->copy()->subMonths($i)->endOfMonth();
            $r = (float)(Order::whereNotIn('status',['cancelled'])->whereBetween('created_at',[$s,$e])->sum('total_omr')
               + CustomOrder::whereIn('status',['ready','delivered'])->whereBetween('created_at',[$s,$e])->sum('agreed_price_omr'));
            $monthly[] = ['month' => $s->format('M y'), 'revenue' => round($r, 3)];
        }

        // Top 5 products by revenue
        $topProducts = DB::table('order_items')
            ->join('orders','order_items.order_id','=','orders.id')
            ->whereNotIn('orders.status',['cancelled'])
            ->groupBy('order_items.product_name')
            ->selectRaw('order_items.product_name, SUM(order_items.quantity) as total_qty, SUM(order_items.total_price_omr) as total_revenue')
            ->orderByDesc('total_revenue')
            ->limit(5)->get();

        // Revenue by payment method
        $byPayment = Order::whereNotIn('status',['cancelled'])
            ->groupBy('payment_method')
            ->selectRaw('payment_method, SUM(total_omr) as total, COUNT(*) as cnt')
            ->orderByDesc('total')->get();

        // Revenue by governorate (top 8)
        $byGov = Order::whereNotIn('status',['cancelled'])
            ->whereNotNull('governorate')
            ->groupBy('governorate')
            ->selectRaw('governorate, SUM(total_omr) as total, COUNT(*) as cnt')
            ->orderByDesc('total')->limit(8)->get();

        // Recent orders (last 5)
        $recent = Order::with('items')->whereNotIn('status',['cancelled'])
            ->latest()->limit(5)->get();

        return compact('revCM','revLM','ordCM','ordLM','avgVal','growth',
                       'monthly','topProducts','byPayment','byGov','recent');
    }
}
