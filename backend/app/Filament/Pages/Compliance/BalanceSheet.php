<?php
namespace App\Filament\Pages\Compliance;

use App\Models\CashFlowEntry;
use App\Models\CustomOrder;
use App\Models\Order;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\VatSetting;
use App\Enums\NavigationGroupEnum;
use Filament\Pages\Page;

class BalanceSheet extends Page
{
    protected string $view = 'filament.pages.compliance.balance-sheet';
    public static function getNavigationIcon(): string  { return 'heroicon-o-scale'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Compliance->value; }
    public static function getNavigationSort(): int     { return 2; }
    public function getTitle(): string                  { return 'Balance Sheet'; }

    public string $asOf = '';
    public function mount(): void { $this->asOf = now()->format('Y-m-d'); }

    public function getData(): array
    {
        $date    = \Carbon\Carbon::parse($this->asOf);
        $vatRate = VatSetting::currentRate() / 100;

        // ── ASSETS ─────────────────────────────────────────────────────────
        // Cash & Bank (net cash flow to date)
        $cashIn  = (float) CashFlowEntry::where('type','in')->where('entry_date','<=',$date)->sum('amount_omr');
        $cashOut = (float) CashFlowEntry::where('type','out')->where('entry_date','<=',$date)->sum('amount_omr');
        $cashBalance = $cashIn - $cashOut;

        // Accounts Receivable (custom orders with balance due)
        $receivable = CustomOrder::whereNotIn('status',['cancelled','delivered'])
            ->where('created_at','<=',$date)
            ->get()
            ->sum(fn($o) => $o->agreed_price_omr - ($o->deposit_paid ? $o->deposit_amount_omr : 0));

        // Inventory value (stock qty × product price)
        $inventory = ProductStock::with('product')
            ->where('quantity','>',0)
            ->get()
            ->sum(fn($s) => $s->quantity * ($s->product?->price ?? 0));

        $totalAssets = $cashBalance + $receivable + $inventory;

        // ── LIABILITIES ─────────────────────────────────────────────────────
        $payable = PurchaseOrder::whereNotIn('payment_status',['paid'])
            ->whereNotIn('status',['cancelled','draft'])
            ->where('order_date','<=',$date)
            ->get()->sum(fn($o) => $o->total_omr - $o->paid_amount_omr);

        // Estimated VAT payable (current quarter)
        $qStart = $date->copy()->startOfQuarter();
        $rev    = (float)(Order::whereNotIn('status',['cancelled'])->whereBetween('created_at',[$qStart,$date])->sum('total_omr')
                + CustomOrder::whereIn('status',['ready','delivered'])->whereBetween('created_at',[$qStart,$date])->sum('agreed_price_omr'));
        $pur    = (float) PurchaseOrder::whereIn('status',['received','partial'])->whereBetween('order_date',[$qStart,$date])->sum('total_omr');
        $vatPayable = max(0, round(($rev - $pur) * $vatRate, 3));

        $totalLiabilities = $payable + $vatPayable;

        // ── EQUITY ──────────────────────────────────────────────────────────
        $totalRevenue   = (float)(Order::whereNotIn('status',['cancelled'])->where('created_at','<=',$date)->sum('total_omr')
                        + CustomOrder::whereIn('status',['ready','delivered'])->where('created_at','<=',$date)->sum('agreed_price_omr'));
        $totalPurchases = (float) PurchaseOrder::whereIn('status',['received','partial'])->where('order_date','<=',$date)->sum('total_omr');
        $totalExpenses  = (float) \App\Models\Expense::where('expense_date','<=',$date)->sum('amount_omr');
        $retainedEarnings = $totalRevenue - $totalPurchases - $totalExpenses;

        $totalEquity = $retainedEarnings;
        $check       = $totalAssets - ($totalLiabilities + $totalEquity);

        return compact(
            'cashBalance','receivable','inventory','totalAssets',
            'payable','vatPayable','totalLiabilities',
            'retainedEarnings','totalEquity',
            'totalRevenue','totalPurchases','totalExpenses',
            'check','date'
        );
    }
}
