<?php
namespace App\Filament\Pages\Analytics;

use App\Enums\NavigationGroupEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ProductPerformance extends Page
{
    protected string $view = 'filament.pages.analytics.product-performance';
    public static function getNavigationIcon(): string  { return 'heroicon-o-shopping-bag'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Analytics->value; }
    public static function getNavigationSort(): int     { return 2; }
    public function getTitle(): string                  { return 'Product Performance'; }

    public function getData(): array
    {
        // All-time product performance
        $products = DB::table('order_items')
            ->join('orders','order_items.order_id','=','orders.id')
            ->leftJoin('products','order_items.product_id','=','products.id')
            ->leftJoin('categories','products.category_id','=','categories.id')
            ->whereNotIn('orders.status',['cancelled'])
            ->groupBy('order_items.product_name','categories.name')
            ->selectRaw('
                order_items.product_name,
                categories.name as category_name,
                SUM(order_items.quantity) as units_sold,
                SUM(order_items.total_price_omr) as total_revenue,
                AVG(order_items.unit_price_omr) as avg_price,
                COUNT(DISTINCT orders.id) as order_count
            ')
            ->orderByDesc('total_revenue')
            ->get();

        $totalRevenue  = $products->sum('total_revenue');
        $totalUnits    = $products->sum('units_sold');
        $totalOrders   = $products->sum('order_count');
        $avgRevPerProd = $products->count() > 0 ? round($totalRevenue / $products->count(), 3) : 0;

        // Top 8 for bar chart
        $top8 = $products->take(8);

        // Revenue by category
        $byCategory = $products->whereNotNull('category_name')
            ->groupBy('category_name')
            ->map(fn($g) => ['name' => $g->first()->category_name, 'revenue' => round($g->sum('total_revenue'), 3), 'units' => $g->sum('units_sold')])
            ->values();

        // Monthly units sold — last 6 months per top product
        $now = now();
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $rev = (float) DB::table('order_items')
                ->join('orders','order_items.order_id','=','orders.id')
                ->whereNotIn('orders.status',['cancelled'])
                ->whereMonth('orders.created_at',$m->month)
                ->whereYear('orders.created_at',$m->year)
                ->sum('order_items.total_price_omr');
            $units = (int) DB::table('order_items')
                ->join('orders','order_items.order_id','=','orders.id')
                ->whereNotIn('orders.status',['cancelled'])
                ->whereMonth('orders.created_at',$m->month)
                ->whereYear('orders.created_at',$m->year)
                ->sum('order_items.quantity');
            $monthlyTrend[] = ['month' => $m->format('M y'), 'revenue' => round($rev,3), 'units' => $units];
        }

        // Stock levels for all products
        $stockLevels = DB::table('products')
            ->leftJoin('product_stock','products.id','=','product_stock.product_id')
            ->select('products.name','product_stock.quantity','product_stock.minimum_alert')
            ->orderBy('product_stock.quantity')
            ->get();

        return compact('products','totalRevenue','totalUnits','totalOrders','avgRevPerProd',
                       'top8','byCategory','monthlyTrend','stockLevels');
    }
}
