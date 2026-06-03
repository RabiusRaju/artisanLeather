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
            ->whereNotIn('payment_status', ['paid'])
            ->whereNotIn('status', ['cancelled', 'draft'])
            ->orderBy('order_date')
            ->get();

        $totalOwed    = $orders->sum(fn($o) => $o->total_omr - $o->paid_amount_omr);
        $overdueCount = $orders->filter(fn($o) => $o->order_date->lt(now()->subDays(30)))->count();
        $totalPaid    = PurchaseOrder::where('payment_status','paid')->sum('total_omr');

        return compact('orders','totalOwed','overdueCount','totalPaid');
    }
}
