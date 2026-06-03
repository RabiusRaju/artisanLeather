<?php
namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class PaymentMethodsWidget extends ChartWidget
{
    protected ?string $heading     = '💳 Payment Methods';
    protected ?string $description = 'Revenue breakdown by channel (OMR)';
    protected static ?int $sort    = 32;
    protected int|string|array $columnSpan = 1;
    protected ?string $maxHeight   = '240px';
    protected string $color = 'warning';

    protected function getData(): array
    {
        $methods  = ['cod','bank','whatsapp'];
        $labels   = ['Cash on Delivery','Bank Transfer','WhatsApp'];
        $revenues = array_map(fn($m) => round((float) Order::where('payment_method',$m)->whereNotIn('status',['cancelled'])->sum('total_omr'), 3), $methods);
        return [
            'datasets' => [[ 'data' => $revenues, 'backgroundColor' => ['#f59e0b','#3b82f6','#22c55e'], 'borderWidth' => 0, 'hoverOffset' => 6 ]],
            'labels'   => $labels,
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
