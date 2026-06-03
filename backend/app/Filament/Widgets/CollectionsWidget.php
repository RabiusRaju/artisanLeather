<?php
namespace App\Filament\Widgets;

use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CollectionsWidget extends ChartWidget
{
    protected ?string $heading     = '🏷️ Collections Revenue';
    protected ?string $description = 'This month by collection (OMR)';
    protected static ?int $sort    = 51;
    protected int|string|array $columnSpan = 1;
    protected ?string $maxHeight   = '310px';
    protected string $color = 'primary';

    protected function getData(): array
    {
        $rows = DB::table('order_items')
            ->join('orders','order_items.order_id','=','orders.id')
            ->join('products','order_items.product_id','=','products.id')
            ->join('brands','products.brand_id','=','brands.id')
            ->whereNotIn('orders.status',['cancelled'])
            ->whereMonth('orders.created_at',now()->month)
            ->groupBy('brands.id','brands.name')
            ->selectRaw('brands.name, SUM(order_items.total_price_omr) as revenue')
            ->orderByDesc('revenue')->limit(5)->get();

        return [
            'datasets' => [[ 'data' => $rows->pluck('revenue')->map(fn($v)=>round((float)$v,3))->toArray(), 'backgroundColor' => ['#f59e0b','#8b5cf6','#10b981','#ef4444','#3b82f6'], 'borderWidth' => 0, 'hoverOffset' => 6 ]],
            'labels'   => $rows->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string { return 'doughnut'; }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
        { cutout:'65%', plugins:{ legend:{ display:true, position:'bottom', labels:{ boxWidth:10, padding:8, font:{size:10} } }, tooltip:{ callbacks:{ label:(c)=>c.label+': OMR '+c.parsed.toFixed(3) } } } }
        JS);
    }
}
