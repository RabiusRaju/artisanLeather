<?php
namespace App\Filament\Pages\Compliance;

use App\Models\CustomOrder;
use App\Models\Expense;
use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Models\VatSetting;
use Filament\Actions\Action;
use Filament\Pages\Page;

class VATReport extends Page
{
    protected string $view = 'filament.pages.compliance.vat-report';
    public static function getNavigationIcon(): string  { return 'heroicon-o-receipt-percent'; }
    public static function getNavigationGroup(): string { return 'Compliance'; }
    public static function getNavigationSort(): int     { return 1; }
    public function getTitle(): string                  { return 'VAT Report'; }

    public string $quarter = '';
    public int    $year    = 0;

    public function mount(): void
    {
        $this->year    = now()->year;
        $this->quarter = 'Q' . ceil(now()->month / 3);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('settings')
                ->label('VAT Settings')
                ->icon('heroicon-o-cog')
                ->url('/admin/vat-settings')
                ->color('gray'),
        ];
    }

    public function getQuarterDates(): array
    {
        $q = (int) ltrim($this->quarter, 'Q');
        $startMonth = ($q - 1) * 3 + 1;
        $endMonth   = $q * 3;
        $start      = \Carbon\Carbon::create($this->year, $startMonth, 1)->startOfMonth();
        $end        = \Carbon\Carbon::create($this->year, $endMonth, 1)->endOfMonth();
        return [$start, $end];
    }

    public function getData(): array
    {
        [$start, $end] = $this->getQuarterDates();
        $rate = VatSetting::currentRate() / 100;
        $setting = VatSetting::latest()->first();

        // Output VAT (collected on sales)
        $salesRev   = (float) Order::whereNotIn('status',['cancelled'])->whereBetween('created_at',[$start,$end])->sum('total_omr');
        $customRev  = (float) CustomOrder::whereIn('status',['ready','delivered'])->whereBetween('created_at',[$start,$end])->sum('agreed_price_omr');
        $totalSales = $salesRev + $customRev;
        $outputVAT  = round($totalSales * $rate, 3);

        // Input VAT (paid on purchases)
        $purchaseTotal = (float) PurchaseOrder::whereIn('status',['received','partial'])->whereBetween('order_date',[$start,$end])->sum('total_omr');
        $expenseTotal  = (float) Expense::whereBetween('expense_date',[$start,$end])->sum('amount_omr');
        $inputVAT      = round($purchaseTotal * $rate, 3);

        // Net VAT
        $netVAT   = round($outputVAT - $inputVAT, 3);
        $vatRatePct = VatSetting::currentRate();

        // Monthly breakdown
        [$qStart, $qEnd] = $this->getQuarterDates();
        $months = [];
        $cur = $qStart->copy();
        while ($cur <= $qEnd) {
            $ms = $cur->copy()->startOfMonth();
            $me = $cur->copy()->endOfMonth();
            $rev = (float)(Order::whereNotIn('status',['cancelled'])->whereBetween('created_at',[$ms,$me])->sum('total_omr')
                 + CustomOrder::whereIn('status',['ready','delivered'])->whereBetween('created_at',[$ms,$me])->sum('agreed_price_omr'));
            $pur = (float) PurchaseOrder::whereIn('status',['received','partial'])->whereBetween('order_date',[$ms,$me])->sum('total_omr');
            $months[] = [
                'month'     => $cur->format('F'),
                'sales'     => $rev,
                'purchases' => $pur,
                'output'    => round($rev * $rate, 3),
                'input'     => round($pur * $rate, 3),
                'net'       => round(($rev - $pur) * $rate, 3),
            ];
            $cur->addMonth();
        }

        return compact('totalSales','outputVAT','purchaseTotal','expenseTotal','inputVAT','netVAT',
                       'vatRatePct','setting','months','start','end');
    }
}
