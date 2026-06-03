<?php
namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class ProductsCollectionsChartWidget extends Widget
{
    protected string $view    = 'filament.widgets.products-collections-chart';
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool { return false; }

    public function getData(): array
    {
        // Top 6 products this month — horizontal bar
        $topProducts = DB::table('order_items')
            ->join('orders','order_items.order_id','=','orders.id')
            ->whereNotIn('orders.status',['cancelled'])
            ->whereMonth('orders.created_at', now()->month)
            ->whereYear('orders.created_at', now()->year)
            ->groupBy('order_items.product_name')
            ->selectRaw('order_items.product_name, SUM(order_items.quantity) as qty, SUM(order_items.total_price_omr) as revenue')
            ->orderByDesc('revenue')->limit(6)->get();

        // Collections bar comparison
        $collections = DB::table('brands')
            ->leftJoin('products','brands.id','=','products.brand_id')
            ->leftJoin('order_items','products.id','=','order_items.product_id')
            ->leftJoin('orders','order_items.order_id','=','orders.id')
            ->where(fn($q) => $q->whereNull('orders.status')->orWhereNotIn('orders.status',['cancelled']))
            ->where('brands.is_active', true)
            ->groupBy('brands.id','brands.name')
            ->selectRaw('brands.name, COALESCE(SUM(order_items.total_price_omr),0) as revenue, COALESCE(SUM(order_items.quantity),0) as units')
            ->orderByDesc('revenue')
            ->get();

        // Last 4 weeks comparison
        $weeks = [];
        for ($i = 3; $i >= 0; $i--) {
            $start = now()->copy()->subWeeks($i)->startOfWeek();
            $end   = now()->copy()->subWeeks($i)->endOfWeek();
            $rev   = (float) DB::table('orders')->whereNotIn('status',['cancelled'])->whereBetween('created_at',[$start,$end])->sum('total_omr');
            $weeks[] = ['label' => 'W' . $start->weekOfYear, 'revenue' => round($rev,3)];
        }

        return compact('topProducts','collections','weeks');
    }
}
