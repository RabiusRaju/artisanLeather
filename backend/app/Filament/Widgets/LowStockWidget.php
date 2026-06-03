<?php
namespace App\Filament\Widgets;
use App\Models\ProductStock;
use Filament\Widgets\Widget;

class LowStockWidget extends Widget
{
    protected string $view = 'filament.widgets.low-stock';
    protected static ?int $sort = 41;
    protected int|string|array $columnSpan = 1;

    public function getData(): array
    {
        $out  = ProductStock::with('product')->where('quantity','<=',0)->get();
        $low  = ProductStock::with('product')->where('quantity','>',0)->whereColumn('quantity','<=','minimum_alert')->get();
        $ok   = ProductStock::whereColumn('quantity','>','minimum_alert')->count();
        $total= ProductStock::count();
        return compact('out','low','ok','total');
    }
}
