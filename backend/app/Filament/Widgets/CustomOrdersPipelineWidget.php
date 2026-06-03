<?php
namespace App\Filament\Widgets;
use App\Models\CustomOrder;
use Filament\Widgets\Widget;

class CustomOrdersPipelineWidget extends Widget
{
    protected string $view = 'filament.widgets.custom-orders-pipeline';
    protected static ?int $sort = 60;
    protected int|string|array $columnSpan = 1;

    public function getData(): array
    {
        $orders = CustomOrder::whereNotIn('status',['delivered','cancelled'])
            ->orderByRaw("FIELD(status,'in_production','quality_check','ready','confirmed','inquiry')")
            ->latest()->limit(6)->get();

        $summary = [
            'inquiry'       => CustomOrder::where('status','inquiry')->count(),
            'confirmed'     => CustomOrder::where('status','confirmed')->count(),
            'in_production' => CustomOrder::where('status','in_production')->count(),
            'quality_check' => CustomOrder::where('status','quality_check')->count(),
            'ready'         => CustomOrder::where('status','ready')->count(),
        ];
        $totalValue = CustomOrder::whereNotIn('status',['cancelled'])->sum('agreed_price_omr');
        return compact('orders','summary','totalValue');
    }
}
