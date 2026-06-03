<?php
namespace App\Filament\Widgets;

use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopProductsWidget extends ChartWidget
{
    protected ?string $heading     = '🏆 Top Products — This Month';
    protected ?string $description = 'Revenue generated per product (OMR)';
    protected static ?int $sort    = 50;
    protected int|string|array $columnSpan = 2;
    protected ?string $maxHeight   = '310px';
    protected string $color = 'warning';

    protected function getData(): array
    {
        $products = DB::table('order_items')
            ->join('orders','order_items.order_id','=','orders.id')
            ->whereNotIn('orders.status',['cancelled'])
            ->whereMonth('orders.created_at',now()->month)
            ->whereYear('orders.created_at',now()->year)
            ->groupBy('order_items.product_name')
            ->selectRaw('order_items.product_name, SUM(order_items.total_price_omr) as revenue')
            ->orderByDesc('revenue')->limit(6)->get();

        $maxRev   = $products->max('revenue') ?: 1;
        $labels   = $products->pluck('product_name')->map(fn($n) => mb_strlen($n) > 22 ? mb_substr($n,0,22).'…' : $n)->toArray();
        $revenues = $products->pluck('revenue')->map(fn($v) => round((float)$v, 3))->toArray();
        $bgColors = array_map(fn($v) => sprintf('rgba(245,158,11,%.2f)', 0.35 + ($v / $maxRev) * 0.6), $revenues);

        return [
            'datasets' => [[
                'label'           => 'Revenue (OMR)',
                'data'            => $revenues,
                'backgroundColor' => $bgColors,
                'borderColor'     => '#f59e0b',
                'borderWidth'     => 1.5,
                'borderRadius'    => 5,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string { return 'bar'; }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
        {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => 'OMR ' + c.parsed.x.toFixed(3) } }
            },
            scales: {
                x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { callback: (v) => 'OMR ' + v.toFixed(0), font: { size: 10 } } },
                y: { grid: { display: false }, ticks: { font: { size: 12, weight: '500' } } }
            }
        }
        JS);
    }
}
