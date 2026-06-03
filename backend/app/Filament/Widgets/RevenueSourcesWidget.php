<?php
namespace App\Filament\Widgets;

use App\Models\CustomOrder;
use App\Models\Order;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class RevenueSourcesWidget extends ChartWidget
{
    protected ?string $heading     = '💰 Revenue Sources';
    protected ?string $description = 'This month — by channel (OMR)';
    protected static ?int $sort    = 30;
    protected int|string|array $columnSpan = 1;
    protected ?string $maxHeight   = '240px';
    protected string $color = 'success';

    protected function getData(): array
    {
        $now = now();
        $web    = round((float) Order::whereNotIn('status',['cancelled'])->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->sum('total_omr'), 3);
        $custom = round((float) CustomOrder::whereIn('status',['ready','delivered'])->whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->sum('agreed_price_omr'), 3);
        return [
            'datasets' => [[ 'data' => [$web,$custom], 'backgroundColor' => ['#22c55e','#f59e0b'], 'borderWidth' => 0, 'hoverOffset' => 6 ]],
            'labels'   => ['Website Orders','Custom Orders'],
        ];
    }

    protected function getType(): string { return 'doughnut'; }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
        { cutout:'70%', plugins:{ legend:{ display:true, position:'bottom', labels:{ boxWidth:10, padding:10, font:{size:11} } }, tooltip:{ callbacks:{ label:(c)=>c.label+': OMR '+c.parsed.toFixed(3) } } } }
        JS);
    }
}
