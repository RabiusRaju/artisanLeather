<?php
namespace App\Filament\Pages;

use App\Models\PurchaseOrder;
use Filament\Pages\Page;

class AccountsPayable extends Page
{
    protected string $view = 'filament.pages.accounts-payable';
    public static function getNavigationIcon(): string  { return 'heroicon-o-credit-card'; }
    public static function getNavigationGroup(): string { return 'Operations'; }
    public static function getNavigationSort(): int     { return 3; }
    public function getTitle(): string                  { return 'Accounts Payable'; }

    public static function getNavigationBadge(): ?string
    {
        $overdue = PurchaseOrder::whereNotIn('payment_status',['paid'])
            ->whereNotIn('status',['cancelled'])
            ->where('order_date','<',now()->subDays(30)->format('Y-m-d'))
            ->count();
        return $overdue > 0 ? (string)$overdue : null;
    }
    public static function getNavigationBadgeColor(): string { return 'danger'; }

    public function getData(): array
    {
        $orders = PurchaseOrder::with('supplier')
            ->whereNotIn('payment_status',['paid'])
            ->whereNotIn('status',['cancelled','draft'])
            ->orderBy('order_date')
            ->get();

        $totalOwed    = $orders->sum(fn($o) => $o->total_omr - $o->paid_amount_omr);
        $totalPaid    = (float) PurchaseOrder::where('payment_status','paid')->sum('total_omr');
        $overdueCount = $orders->filter(fn($o) => $o->order_date->lt(now()->subDays(30)))->count();

        // Aging buckets
        $aging = ['0–30 days'=>0,'31–60 days'=>0,'61–90 days'=>0,'90+ days'=>0];
        foreach ($orders as $o) {
            $days = $o->order_date->diffInDays(now());
            $bal  = $o->total_omr - $o->paid_amount_omr;
            if      ($days <= 30) $aging['0–30 days']  += $bal;
            elseif  ($days <= 60) $aging['31–60 days'] += $bal;
            elseif  ($days <= 90) $aging['61–90 days'] += $bal;
            else                  $aging['90+ days']   += $bal;
        }
        $aging = array_map(fn($v) => round((float)$v, 3), $aging);

        // By supplier
        $bySupplier = $orders->groupBy('supplier.name')
            ->map(fn($g) => [
                'name'    => $g->first()->supplier?->name ?? 'Unknown',
                'balance' => round($g->sum(fn($o) => $o->total_omr - $o->paid_amount_omr), 3),
                'count'   => $g->count(),
            ])->sortByDesc('balance')->values();

        return compact('orders','totalOwed','totalPaid','overdueCount','aging','bySupplier');
    }
}
