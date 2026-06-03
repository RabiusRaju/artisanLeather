<?php
namespace App\Filament\Pages\Analytics;

use App\Models\CustomOrder;
use App\Models\Order;
use Filament\Pages\Page;

class SeasonalReport extends Page
{
    protected string $view = 'filament.pages.analytics.seasonal-report';
    public static function getNavigationIcon(): string  { return 'heroicon-o-calendar-days'; }
    public static function getNavigationGroup(): string { return 'Analytics'; }
    public static function getNavigationSort(): int     { return 5; }
    public function getTitle(): string                  { return 'Seasonal Report'; }

    public function getData(): array
    {
        $now = now();
        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

        // Current year month-by-month
        $currentYear = [];
        $lastYear    = [];
        for ($m = 1; $m <= 12; $m++) {
            $cy = (float)(Order::whereNotIn('status',['cancelled'])->whereYear('created_at',$now->year)->whereMonth('created_at',$m)->sum('total_omr')
                + CustomOrder::whereIn('status',['ready','delivered'])->whereYear('created_at',$now->year)->whereMonth('created_at',$m)->sum('agreed_price_omr'));
            $ly = (float)(Order::whereNotIn('status',['cancelled'])->whereYear('created_at',$now->year-1)->whereMonth('created_at',$m)->sum('total_omr')
                + CustomOrder::whereIn('status',['ready','delivered'])->whereYear('created_at',$now->year-1)->whereMonth('created_at',$m)->sum('agreed_price_omr'));
            $currentYear[] = round($cy, 3);
            $lastYear[]    = round($ly, 3);
        }

        // Monthly stats table
        $monthStats = [];
        for ($m = 1; $m <= 12; $m++) {
            $rev = $currentYear[$m-1];
            $ord = Order::whereNotIn('status',['cancelled'])->whereYear('created_at',$now->year)->whereMonth('created_at',$m)->count();
            $lRev = $lastYear[$m-1];
            $growth = $lRev > 0 ? round((($rev-$lRev)/$lRev)*100,1) : null;
            $monthStats[] = ['month'=>$months[$m-1],'revenue'=>$rev,'orders'=>$ord,'last_year'=>$lRev,'growth'=>$growth];
        }

        $bestMonth  = collect($monthStats)->sortByDesc('revenue')->first();
        $totalYear  = array_sum($currentYear);
        $avgMonth   = count(array_filter($currentYear)) > 0 ? round($totalYear / max(count(array_filter($currentYear)),1),3) : 0;

        return compact('months','currentYear','lastYear','monthStats','bestMonth','totalYear','avgMonth','now');
    }
}
