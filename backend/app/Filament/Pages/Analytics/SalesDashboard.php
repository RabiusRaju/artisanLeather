<?php
namespace App\Filament\Pages\Analytics;

use App\Models\Customer;
use App\Models\CustomOrder;
use App\Models\Order;
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
        $now = now();
        $cmS = $now->copy()->startOfMonth();
        $cmE = $now->copy()->endOfMonth();
        $lmS = $now->copy()->subMonth()->startOfMonth();
        $lmE = $now->copy()->subMonth()->endOfMonth();

        // ── KPIs ──────────────────────────────────────────────────────────────
        $revCM  = (float)(Order::whereNotIn('status',['cancelled'])->whereBetween('created_at',[$cmS,$cmE])->sum('total_omr')
                + CustomOrder::whereIn('status',['ready','delivered'])->whereBetween('created_at',[$cmS,$cmE])->sum('agreed_price_omr'));
        $revLM  = (float)(Order::whereNotIn('status',['cancelled'])->whereBetween('created_at',[$lmS,$lmE])->sum('total_omr')
                + CustomOrder::whereIn('status',['ready','delivered'])->whereBetween('created_at',[$lmS,$lmE])->sum('agreed_price_omr'));
        $revToday = (float) Order::whereNotIn('status',['cancelled'])->whereDate('created_at',$now->toDateString())->sum('total_omr');
        $ordCM  = Order::whereNotIn('status',['cancelled'])->whereBetween('created_at',[$cmS,$cmE])->count();
        $ordLM  = Order::whereNotIn('status',['cancelled'])->whereBetween('created_at',[$lmS,$lmE])->count();
        $avgVal = $ordCM > 0 ? round($revCM / $ordCM, 3) : 0;
        $growth = $revLM > 0 ? round((($revCM - $revLM) / $revLM) * 100, 1) : ($revCM > 0 ? 100 : 0);
        $newCustomers = Customer::whereBetween('created_at',[$cmS,$cmE])->count();

        // ── 30-day daily revenue ───────────────────────────────────────────────
        $daily30 = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i);
            $daily30[] = [
                'day'     => $d->format('d M'),
                'revenue' => round((float) Order::whereNotIn('status',['cancelled'])->whereDate('created_at',$d->toDateString())->sum('total_omr'), 3),
            ];
        }

        // ── 12-month trend ────────────────────────────────────────────────────
        $monthly = [];
        for ($i = 11; $i >= 0; $i--) {
            $s = $now->copy()->subMonths($i)->startOfMonth();
            $e = $now->copy()->subMonths($i)->endOfMonth();
            $r = (float)(Order::whereNotIn('status',['cancelled'])->whereBetween('created_at',[$s,$e])->sum('total_omr')
               + CustomOrder::whereIn('status',['ready','delivered'])->whereBetween('created_at',[$s,$e])->sum('agreed_price_omr'));
            $monthly[] = ['month' => $s->format('M y'), 'revenue' => round($r, 3)];
        }

        // ── Top 6 products (all time) ─────────────────────────────────────────
        $topProducts = DB::table('order_items')
            ->join('orders','order_items.order_id','=','orders.id')
            ->whereNotIn('orders.status',['cancelled'])
            ->groupBy('order_items.product_name')
            ->selectRaw('order_items.product_name, SUM(order_items.quantity) as total_qty, COUNT(DISTINCT orders.id) as total_orders, SUM(order_items.total_price_omr) as total_revenue')
            ->orderByDesc('total_revenue')->limit(6)->get();

        // ── Revenue by collection this month ──────────────────────────────────
        $byCollection = DB::table('order_items')
            ->join('orders','order_items.order_id','=','orders.id')
            ->join('products','order_items.product_id','=','products.id')
            ->join('brands','products.brand_id','=','brands.id')
            ->whereNotIn('orders.status',['cancelled'])
            ->whereBetween('orders.created_at',[$cmS,$cmE])
            ->groupBy('brands.id','brands.name')
            ->selectRaw('brands.name, SUM(order_items.total_price_omr) as revenue')
            ->orderByDesc('revenue')->limit(5)->get();

        // ── Payment method ────────────────────────────────────────────────────
        $byPayment = Order::whereNotIn('status',['cancelled'])
            ->groupBy('payment_method')
            ->selectRaw('payment_method, SUM(total_omr) as total, COUNT(*) as cnt')
            ->orderByDesc('total')->get();

        // ── Sales by governorate ──────────────────────────────────────────────
        $byGov = Order::whereNotIn('status',['cancelled'])
            ->whereNotNull('governorate')
            ->groupBy('governorate')
            ->selectRaw('governorate, SUM(total_omr) as total, COUNT(*) as cnt')
            ->orderByDesc('total')->limit(8)->get();

        // ── Order status breakdown ────────────────────────────────────────────
        $statusCounts = [];
        foreach (['pending','confirmed','processing','shipped','delivered','cancelled'] as $s) {
            $statusCounts[$s] = Order::where('status',$s)->count();
        }

        // ── Recent orders ─────────────────────────────────────────────────────
        $recent = Order::with('items')->latest()->limit(8)->get();

        return compact(
            'revCM','revLM','revToday','ordCM','ordLM','avgVal','growth','newCustomers',
            'daily30','monthly','topProducts','byCollection',
            'byPayment','byGov','statusCounts','recent'
        );
    }
}
