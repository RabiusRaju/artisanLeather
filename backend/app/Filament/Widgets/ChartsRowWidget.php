<?php
namespace App\Filament\Widgets;

use App\Models\CustomOrder;
use App\Models\Order;
use Filament\Widgets\Widget;

class ChartsRowWidget extends Widget
{
    protected string $view    = 'filament.widgets.charts-row';
    protected static ?int $sort = 3;
    public static function canView(): bool { return false; }
    protected int|string|array $columnSpan = 'full';

    public function getData(): array
    {
        // Revenue sources (pie)
        $websiteRev = (float) Order::whereNotIn('status',['cancelled'])
            ->whereMonth('created_at', now()->month)->sum('total_omr');
        $customRev  = (float) CustomOrder::whereIn('status',['ready','delivered'])
            ->whereMonth('created_at', now()->month)->sum('agreed_price_omr');
        $otherRev   = 0;

        // Order status distribution
        $statuses = ['pending','confirmed','processing','shipped','delivered','cancelled'];
        $orderCounts = array_map(fn($s) => Order::where('status',$s)->count(), $statuses);

        // Payment method breakdown
        $payMethods = ['cod','bank','whatsapp'];
        $payRevenue = array_map(fn($m) =>
            (float) Order::whereNotIn('status',['cancelled'])->where('payment_method',$m)->sum('total_omr'),
            $payMethods
        );

        return compact('websiteRev','customRev','otherRev','orderCounts','statuses','payRevenue','payMethods');
    }
}
