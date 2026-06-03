<?php
namespace App\Filament\Pages;

use App\Models\CustomOrder;
use Filament\Pages\Page;

class AccountsReceivable extends Page
{
    protected string $view = 'filament.pages.accounts-receivable';
    public static function getNavigationIcon(): string  { return 'heroicon-o-inbox-arrow-down'; }
    public static function getNavigationGroup(): string { return 'Operations'; }
    public static function getNavigationSort(): int     { return 4; }
    public function getTitle(): string                  { return 'Accounts Receivable'; }
    public static function getNavigationBadge(): ?string
    {
        $pending = CustomOrder::whereNotIn('status',['cancelled','delivered'])
            ->where('deposit_paid', false)->count();
        return $pending > 0 ? (string)$pending : null;
    }
    public static function getNavigationBadgeColor(): string { return 'warning'; }

    public function getData(): array
    {
        $orders = CustomOrder::with('customer')
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('created_at')
            ->get();

        $totalAgreed   = $orders->sum('agreed_price_omr');
        $totalDeposit  = $orders->where('deposit_paid', true)->sum('deposit_amount_omr');
        $totalBalance  = $orders->sum(fn($o) => $o->agreed_price_omr - ($o->deposit_paid ? $o->deposit_amount_omr : 0));
        $pendingDeposit= $orders->where('deposit_paid', false)->count();

        return compact('orders','totalAgreed','totalDeposit','totalBalance','pendingDeposit');
    }
}
