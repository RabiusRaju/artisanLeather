<?php
namespace App\Filament\Pages\Compliance;

use App\Models\CashFlowEntry;
use App\Enums\NavigationGroupEnum;
use Filament\Pages\Page;

class BankReconciliation extends Page
{
    protected string $view = 'filament.pages.compliance.bank-reconciliation';
    public static function getNavigationIcon(): string  { return 'heroicon-o-building-library'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Compliance->value; }
    public static function getNavigationSort(): int     { return 5; }
    public function getTitle(): string                  { return 'Bank Reconciliation'; }

    public function getData(): array
    {
        $reconciled   = CashFlowEntry::where('is_reconciled',true);
        $unreconciled = CashFlowEntry::where('is_reconciled',false);

        $totalIn  = (float) CashFlowEntry::where('type','in')->sum('amount_omr');
        $totalOut = (float) CashFlowEntry::where('type','out')->sum('amount_omr');
        $netBalance   = $totalIn - $totalOut;
        $reconciledIn = (float) $reconciled->clone()->where('type','in')->sum('amount_omr');
        $unreconciledEntries = $unreconciled->clone()->orderByDesc('entry_date')->get();

        return compact('totalIn','totalOut','netBalance','reconciledIn','unreconciledEntries');
    }

    public function markReconciled(int $id): void
    {
        CashFlowEntry::find($id)?->update(['is_reconciled' => true]);
        $this->dispatch('$refresh');
    }

    public function markUnreconciled(int $id): void
    {
        CashFlowEntry::find($id)?->update(['is_reconciled' => false]);
        $this->dispatch('$refresh');
    }
}
