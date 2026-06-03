<?php
namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class RevenueTrendWidget extends ChartWidget
{
    protected ?string $heading     = '📊 Revenue Trend — Last 30 Days';
    protected ?string $description = 'Gold = above average  ·  Green dashed line = daily average';
    protected static ?int $sort    = 20;
    protected int|string|array $columnSpan = 'full';
    protected ?string $maxHeight   = '260px';
    protected string $color = 'warning';

    protected function getData(): array
    {
        $now = now();
        $days = $rev = [];

        for ($i = 29; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i);
            $days[] = $d->format('d M');
            $rev[]  = round((float) Order::whereNotIn('status',['cancelled'])
                ->whereDate('created_at',$d)->sum('total_omr'), 3);
        }

        $avg = count($rev) ? round(array_sum($rev) / count($rev), 3) : 0;

        return [
            'datasets' => [
                [
                    'label'           => 'Revenue (OMR)',
                    'data'            => $rev,
                    'backgroundColor' => array_map(fn($v) => $v >= $avg ? 'rgba(245,158,11,0.75)' : 'rgba(245,158,11,0.2)', $rev),
                    'borderColor'     => array_map(fn($v) => $v >= $avg ? '#f59e0b' : 'rgba(245,158,11,0.3)', $rev),
                    'borderWidth'     => 1,
                    'borderRadius'    => 4,
                    'order'           => 2,
                ],
                [
                    'label'       => 'Daily Avg (OMR ' . number_format($avg, 3) . ')',
                    'data'        => array_fill(0, 30, $avg),
                    'type'        => 'line',
                    'borderColor' => 'rgba(34,197,94,0.8)',
                    'borderWidth' => 2,
                    'borderDash'  => [6, 4],
                    'pointRadius' => 0,
                    'fill'        => false,
                    'tension'     => 0,
                    'order'       => 1,
                ],
            ],
            'labels' => $days,
        ];
    }

    protected function getType(): string { return 'bar'; }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
        {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: true, position: 'top', align: 'end', labels: { boxWidth: 12, padding: 12, usePointStyle: true } },
                tooltip: { callbacks: { label: (c) => c.dataset.label.split(' (')[0] + ': OMR ' + (c.parsed.y||0).toFixed(3) } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { maxTicksLimit: 10, font: { size: 9 } } },
                y: { beginAtZero: true, ticks: { callback: (v) => 'OMR ' + v.toFixed(0) } }
            }
        }
        JS);
    }
}
