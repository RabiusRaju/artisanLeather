<?php
namespace App\Filament\Pages\Analytics;

use App\Models\Brand;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class SalesByBrand extends Page
{
    protected string $view = 'filament.pages.analytics.sales-by-brand';
    public static function getNavigationIcon(): string  { return 'heroicon-o-bookmark'; }
    public static function getNavigationGroup(): string { return 'Analytics'; }
    public static function getNavigationSort(): int     { return 4; }
    public function getTitle(): string                  { return 'Sales by Collection'; }

    public function getData(): array
    {
        $brands = DB::table('brands')
            ->leftJoin('products','brands.id','=','products.brand_id')
            ->leftJoin('order_items','products.id','=','order_items.product_id')
            ->leftJoin('orders','order_items.order_id','=','orders.id')
            ->where(fn($q) => $q->whereNull('orders.status')->orWhereNotIn('orders.status',['cancelled']))
            ->where('brands.is_active',true)
            ->groupBy('brands.id','brands.name','brands.name_ar','brands.slug')
            ->selectRaw('
                brands.id, brands.name, brands.name_ar, brands.slug,
                COUNT(DISTINCT products.id) as product_count,
                COALESCE(SUM(order_items.quantity),0) as units_sold,
                COALESCE(SUM(order_items.total_price_omr),0) as total_revenue,
                COALESCE(COUNT(DISTINCT orders.id),0) as order_count
            ')
            ->orderByDesc('total_revenue')
            ->get();

        $total = $brands->sum('total_revenue');

        // Top product per brand
        $topPerBrand = [];
        foreach ($brands as $brand) {
            $top = DB::table('order_items')
                ->join('orders','order_items.order_id','=','orders.id')
                ->join('products','order_items.product_id','=','products.id')
                ->where('products.brand_id',$brand->id)
                ->whereNotIn('orders.status',['cancelled'])
                ->groupBy('order_items.product_name')
                ->selectRaw('order_items.product_name, SUM(order_items.total_price_omr) as rev')
                ->orderByDesc('rev')->first();
            $topPerBrand[$brand->id] = $top?->product_name ?? '—';
        }

        return compact('brands','total','topPerBrand');
    }
}
