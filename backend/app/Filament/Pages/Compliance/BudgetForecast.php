<?php
namespace App\Filament\Pages\Compliance;

use App\Models\Budget;
use App\Models\CustomOrder;
use App\Models\Order;
use App\Models\Expense;
use App\Enums\NavigationGroupEnum;
use Filament\Pages\Page;

class BudgetForecast extends Page
{
    protected string $view = 'filament.pages.compliance.budget-forecast';
    public static function getNavigationIcon(): string  { return 'heroicon-o-presentation-chart-line'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Compliance->value; }
    public static function getNavigationSort(): int     { return 3; }
    public function getTitle(): string                  { return 'Budget & Forecast'; }

    public function getData(): array
    {
        $now    = now();
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $m    = $now->copy()->subMonths($i);
            $mS   = $m->copy()->startOfMonth();
            $mE   = $m->copy()->endOfMonth();
            $rev  = (float)(Order::whereNotIn('status',['cancelled'])->whereBetween('created_at',[$mS,$mE])->sum('total_omr')
                  + CustomOrder::whereIn('status',['ready','delivered'])->whereBetween('created_at',[$mS,$mE])->sum('agreed_price_omr'));
            $exp  = (float) Expense::whereBetween('expense_date',[$mS,$mE])->sum('amount_omr');
            $bdg  = Budget::where('year',$m->year)->where('month',$m->month)->first();
            $months[] = [
                'label'         => $m->format('M y'),
                'year'          => $m->year,
                'month'         => $m->month,
                'actual_revenue'=> round($rev,3),
                'actual_expense'=> round($exp,3),
                'target_revenue'=> round($bdg?->revenue_target ?? 0,3),
                'expense_budget'=> round($bdg?->expense_budget ?? 0,3),
                'revenue_gap'   => round(($bdg?->revenue_target ?? 0) - $rev,3),
                'has_budget'    => $bdg !== null,
            ];
        }

        // 3-month forecast (simple average of last 3 months)
        $last3 = array_slice($months, -3);
        $avgRev = collect($last3)->avg('actual_revenue');
        $avgExp = collect($last3)->avg('actual_expense');
        $forecast = [];
        for ($i = 1; $i <= 3; $i++) {
            $fm = $now->copy()->addMonths($i);
            $forecast[] = ['label'=>$fm->format('M y'),'revenue'=>round($avgRev*(1+($i*0.02)),3),'expense'=>round($avgExp,3)];
        }

        $totalActual  = collect($months)->sum('actual_revenue');
        $totalTarget  = collect($months)->sum('target_revenue');
        $achievement  = $totalTarget > 0 ? round(($totalActual/$totalTarget)*100,1) : 0;

        return compact('months','forecast','totalActual','totalTarget','achievement');
    }
}
