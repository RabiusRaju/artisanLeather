<?php
namespace App\Filament\Pages\Analytics;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ProductPerformance extends Page
{
    protected string $view = 'filament.pages.analytics.product-performance';
    public static function getNavigationIcon(): string  { return 'heroicon-o-shopping-bag'; }
    public static function getNavigationGroup(): string { return 'Analytics'; }
    public static function getNavigationSort(): int     { return 2; }
    public function getTitle(): string                  { return 'Product Performance'; }

    public function getData(): array
    {
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

        $totalRevenue = $products->sum('total_revenue');
        $totalUnits   = $products->sum('units_sold');

        // Best & slowest
        $best    = $products->take(5);
        $slowest = $products->sortBy('total_revenue')->take(5)->values();

        return compact('products','totalRevenue','totalUnits','best','slowest');
    }
}
