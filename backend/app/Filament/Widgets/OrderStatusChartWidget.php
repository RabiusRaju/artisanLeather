<?php
namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class OrderStatusChartWidget extends ChartWidget
{
    protected ?string $heading     = '🛒 Orders by Status';
    protected ?string $description = 'All orders — current distribution';
    protected static ?int $sort    = 31;
    protected int|string|array $columnSpan = 1;
    protected ?string $maxHeight   = '240px';
    protected string $color = 'info';

    protected function getData(): array
    {
        $statuses = ['pending','confirmed','processing','shipped','delivered','cancelled'];
        $labels   = array_map(fn($s) => ucfirst(str_replace('_',' ',$s)), $statuses);
        $counts   = array_map(fn($s) => Order::where('status',$s)->count(), $statuses);
        return [
            'datasets' => [[ 'data' => $counts, 'backgroundColor' => ['#f59e0b','#3b82f6','#8b5cf6','#10b981','#22c55e','#ef4444'], 'borderWidth' => 0, 'hoverOffset' => 6 ]],
            'labels'   => $labels,
        ];
    }

    protected function getType(): string { return 'doughnut'; }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
        { cutout:'70%', plugins:{ legend:{ display:true, position:'bottom', labels:{ boxWidth:10, padding:8, font:{size:10} } }, tooltip:{ callbacks:{ label:(c)=>c.label+': '+c.parsed+' orders' } } } }
        JS);
    }
}
