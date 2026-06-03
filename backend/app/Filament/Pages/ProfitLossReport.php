<?php

namespace App\Filament\Pages;

use App\Models\CustomOrder;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OtherIncome;
use App\Models\PurchaseOrder;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;

class ProfitLossReport extends Page
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.profit-loss-report';

    public static function getNavigationIcon(): string  { return 'heroicon-o-chart-bar'; }
    public static function getNavigationGroup(): string { return 'Finance'; }
    public static function getNavigationSort(): int     { return 10; }
    public function getTitle(): string                  { return 'Profit & Loss Report'; }

    public string  $period   = 'this_month';
    public ?string $dateFrom = null;
    public ?string $dateTo   = null;

    public function mount(): void
    {
        $this->period   = 'this_month';
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->endOfMonth()->format('Y-m-d');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action('downloadCsv'),
        ];
    }

    public function downloadCsv()
    {
        $d    = $this->getReportData();
        $from = $d['from'];
        $to   = $d['to'];

        $rows = [];
        $rows[] = ['ARTISAN LEATHER — PROFIT & LOSS REPORT'];
        $rows[] = ['Period', "{$from} to {$to}"];
        $rows[] = ['Generated', now()->format('d M Y H:i')];
        $rows[] = [];

        $rows[] = ['=== REVENUE ==='];
        $rows[] = ['Website Orders', 'OMR ' . number_format($d['websiteRevenue'], 3)];
        $rows[] = ['Custom / Bespoke Orders', 'OMR ' . number_format($d['customRevenue'], 3)];
        $rows[] = ['Other Income', 'OMR ' . number_format($d['otherRevenue'], 3)];
        $rows[] = ['TOTAL REVENUE', 'OMR ' . number_format($d['totalRevenue'], 3)];
        $rows[] = [];

        $rows[] = ['=== COST OF GOODS ==='];
        $rows[] = ['Purchases (COGS)', 'OMR ' . number_format($d['purchaseCost'], 3)];
        $rows[] = ['GROSS PROFIT', 'OMR ' . number_format($d['grossProfit'], 3)];
        $rows[] = ['Gross Margin', $d['grossMargin'] . '%'];
        $rows[] = [];

        $rows[] = ['=== OPERATING EXPENSES ==='];
        foreach ($d['expensesByCategory'] as $cat => $amt) {
            $rows[] = [$cat, 'OMR ' . number_format($amt, 3)];
        }
        $rows[] = ['TOTAL EXPENSES', 'OMR ' . number_format($d['totalExpenses'], 3)];
        $rows[] = [];

        $rows[] = ['=== NET PROFIT ==='];
        $rows[] = ['NET PROFIT / (LOSS)', 'OMR ' . number_format($d['netProfit'], 3)];
        $rows[] = ['Net Margin', $d['netMargin'] . '%'];

        $filename = "profit-loss-{$from}-to-{$to}.csv";

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function updatedPeriod(string $value): void
    {
        match ($value) {
            'this_month'   => [$this->dateFrom = now()->startOfMonth()->format('Y-m-d'),        $this->dateTo = now()->endOfMonth()->format('Y-m-d')],
            'last_month'   => [$this->dateFrom = now()->subMonth()->startOfMonth()->format('Y-m-d'), $this->dateTo = now()->subMonth()->endOfMonth()->format('Y-m-d')],
            'this_quarter' => [$this->dateFrom = now()->startOfQuarter()->format('Y-m-d'),      $this->dateTo = now()->endOfQuarter()->format('Y-m-d')],
            'this_year'    => [$this->dateFrom = now()->startOfYear()->format('Y-m-d'),         $this->dateTo = now()->endOfYear()->format('Y-m-d')],
            'last_year'    => [$this->dateFrom = now()->subYear()->startOfYear()->format('Y-m-d'), $this->dateTo = now()->subYear()->endOfYear()->format('Y-m-d')],
            default        => null,
        };
    }

    public function getReportData(): array
    {
        $from = $this->dateFrom;
        $to   = $this->dateTo;

        $websiteRevenue = (float) Order::whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$from, $to])->sum('total_omr');

        $customRevenue = (float) CustomOrder::whereIn('status', ['ready', 'delivered'])
            ->whereBetween('created_at', [$from, $to])->sum('agreed_price_omr');

        $otherRevenue = (float) OtherIncome::whereBetween('income_date', [$from, $to])->sum('amount_omr');

        $totalRevenue = $websiteRevenue + $customRevenue + $otherRevenue;

        $purchaseCost = (float) PurchaseOrder::whereIn('status', ['received', 'partial'])
            ->whereBetween('order_date', [$from, $to])->sum('total_omr');

        $grossProfit = $totalRevenue - $purchaseCost;
        $grossMargin = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0;

        $expensesByCategory = Expense::with('category')
            ->whereBetween('expense_date', [$from, $to])
            ->get()
            ->groupBy('category.name')
            ->map(fn($g) => (float) $g->sum('amount_omr'))
            ->sortDesc();

        $totalExpenses = (float) $expensesByCategory->sum();
        $netProfit     = $grossProfit - $totalExpenses;
        $netMargin     = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

        return compact(
            'websiteRevenue', 'customRevenue', 'otherRevenue', 'totalRevenue',
            'purchaseCost', 'grossProfit', 'grossMargin',
            'expensesByCategory', 'totalExpenses',
            'netProfit', 'netMargin',
            'from', 'to'
        );
    }
}
