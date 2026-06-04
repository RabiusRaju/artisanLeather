<?php
namespace App\Filament\Pages\Analytics;

use App\Models\CustomOrder;
use App\Models\Order;
use App\Enums\NavigationGroupEnum;
use Filament\Pages\Page;

class SeasonalReport extends Page
{
    protected string $view = 'filament.pages.analytics.seasonal-report';
    public static function getNavigationIcon(): string  { return 'heroicon-o-calendar-days'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Analytics->value; }
    public static function getNavigationSort(): int     { return 5; }
    public function getTitle(): string                  { return 'Seasonal Report'; }

    public function getData(): array
    {
        $now    = now();
        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

        // Current & last year month-by-month
        $currentYear = [];
        $lastYear    = [];
        $orderCounts = [];

        for ($m = 1; $m <= 12; $m++) {
            $cy = (float)(Order::whereNotIn('status',['cancelled'])->whereYear('created_at',$now->year)->whereMonth('created_at',$m)->sum('total_omr')
                + CustomOrder::whereIn('status',['ready','delivered'])->whereYear('created_at',$now->year)->whereMonth('created_at',$m)->sum('agreed_price_omr'));
            $ly = (float)(Order::whereNotIn('status',['cancelled'])->whereYear('created_at',$now->year-1)->whereMonth('created_at',$m)->sum('total_omr')
                + CustomOrder::whereIn('status',['ready','delivered'])->whereYear('created_at',$now->year-1)->whereMonth('created_at',$m)->sum('agreed_price_omr'));
            $currentYear[]  = round($cy, 3);
            $lastYear[]     = round($ly, 3);
            $orderCounts[]  = Order::whereNotIn('status',['cancelled'])->whereYear('created_at',$now->year)->whereMonth('created_at',$m)->count();
        }

        // Quarterly totals
        $quarters = [
            'Q1 (Jan–Mar)' => array_sum(array_slice($currentYear,0,3)),
            'Q2 (Apr–Jun)' => array_sum(array_slice($currentYear,3,3)),
            'Q3 (Jul–Sep)' => array_sum(array_slice($currentYear,6,3)),
            'Q4 (Oct–Dec)' => array_sum(array_slice($currentYear,9,3)),
        ];
        $quartersLY = [
            'Q1' => array_sum(array_slice($lastYear,0,3)),
            'Q2' => array_sum(array_slice($lastYear,3,3)),
            'Q3' => array_sum(array_slice($lastYear,6,3)),
            'Q4' => array_sum(array_slice($lastYear,9,3)),
        ];

        // Month stats for table
        $monthStats = [];
        for ($m = 1; $m <= 12; $m++) {
            $rev    = $currentYear[$m-1];
            $lRev   = $lastYear[$m-1];
            $growth = $lRev > 0 ? round((($rev-$lRev)/$lRev)*100,1) : null;
            $monthStats[] = [
                'month'     => $months[$m-1],
                'revenue'   => $rev,
                'orders'    => $orderCounts[$m-1],
                'last_year' => $lRev,
                'growth'    => $growth,
                'is_past'   => $m <= $now->month,
            ];
        }

        $bestMonth  = collect($monthStats)->sortByDesc('revenue')->first();
        $totalYear  = array_sum($currentYear);
        $totalLY    = array_sum($lastYear);
        $yoyGrowth  = $totalLY > 0 ? round((($totalYear-$totalLY)/$totalLY)*100,1) : 0;
        $passedMonths = collect($monthStats)->where('is_past',true)->where('revenue','>',0)->count();
        $avgMonth   = $passedMonths > 0 ? round($totalYear/$passedMonths,3) : 0;

        // Oman seasonal events annotations
        $seasonalEvents = [
            1 => 'New Year', 3 => 'Ramadan*', 4 => 'Eid Al-Fitr*',
            6 => 'Eid Al-Adha*', 11 => 'National Day'
        ];

        return compact('months','currentYear','lastYear','orderCounts',
                       'monthStats','quarters','quartersLY',
                       'bestMonth','totalYear','totalLY','yoyGrowth','avgMonth',
                       'now','seasonalEvents');
    }
}
