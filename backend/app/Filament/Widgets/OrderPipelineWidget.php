<?php
namespace App\Filament\Widgets;
use App\Models\Order;
use Filament\Widgets\Widget;

class OrderPipelineWidget extends Widget
{
    protected string $view = 'filament.widgets.order-pipeline';
    protected static ?int $sort = 42;
    protected int|string|array $columnSpan = 1;

    public function getData(): array
    {
        $pipeline = collect(['pending','confirmed','processing','shipped','delivered','cancelled'])
            ->mapWithKeys(fn($s) => [$s => Order::where('status',$s)->count()]);

        $total = $pipeline->except('cancelled')->sum();
        $todayRevenue = (float) Order::whereNotIn('status',['cancelled'])->whereDate('created_at',today())->sum('total_omr');

        return compact('pipeline','total','todayRevenue');
    }
}
