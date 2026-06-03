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
            ->where('deposit_paid',false)->count();
        return $pending > 0 ? (string)$pending : null;
    }
    public static function getNavigationBadgeColor(): string { return 'warning'; }

    public function getData(): array
    {
        $orders = CustomOrder::with('customer')
            ->whereNotIn('status',['cancelled'])
            ->orderBy('created_at')
            ->get();

        $totalAgreed    = round((float)$orders->sum('agreed_price_omr'), 3);
        $totalDeposit   = round((float)$orders->where('deposit_paid',true)->sum('deposit_amount_omr'), 3);
        $totalBalance   = round((float)$orders->sum(fn($o) => $o->agreed_price_omr - ($o->deposit_paid ? $o->deposit_amount_omr : 0)), 3);
        $pendingDeposit = $orders->where('deposit_paid',false)->count();
        $collectionRate = $totalAgreed > 0 ? round(($totalDeposit/$totalAgreed)*100,1) : 0;

        // Pipeline by status
        $stages = ['inquiry','confirmed','in_production','quality_check','ready','delivered'];
        $pipeline = [];
        foreach ($stages as $s) {
            $group = $orders->where('status',$s);
            $pipeline[$s] = [
                'count'   => $group->count(),
                'value'   => round((float)$group->sum('agreed_price_omr'),3),
                'balance' => round((float)$group->sum(fn($o) => $o->agreed_price_omr - ($o->deposit_paid ? $o->deposit_amount_omr : 0)),3),
            ];
        }

        return compact('orders','totalAgreed','totalDeposit','totalBalance',
                       'pendingDeposit','collectionRate','pipeline');
    }
}
