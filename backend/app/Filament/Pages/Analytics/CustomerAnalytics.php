<?php
namespace App\Filament\Pages\Analytics;

use App\Models\Customer;
use App\Models\Order;
use App\Enums\NavigationGroupEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class CustomerAnalytics extends Page
{
    protected string $view = 'filament.pages.analytics.customer-analytics';
    public static function getNavigationIcon(): string  { return 'heroicon-o-users'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Analytics->value; }
    public static function getNavigationSort(): int     { return 3; }
    public function getTitle(): string                  { return 'Customer Analytics'; }

    public function getData(): array
    {
        $now   = now();
        $total = Customer::count();
        $vip   = Customer::where('status','vip')->count();
        $newThisMonth = Customer::whereMonth('created_at',$now->month)->whereYear('created_at',$now->year)->count();

        // Repeat buyers (2+ orders)
        $repeat = DB::table('orders')
            ->whereNotIn('status',['cancelled'])
            ->select('email')->groupBy('email')
            ->havingRaw('COUNT(*) > 1')->get()->count();

        // Inactive 60+ days
        $inactive = DB::table('orders')
            ->whereNotIn('status',['cancelled'])
            ->select('email')->groupBy('email')
            ->havingRaw('MAX(created_at) < ?',[now()->subDays(60)->toDateTimeString()])
            ->get()->count();

        // Total revenue from orders
        $totalRevenue = (float) Order::whereNotIn('status',['cancelled'])->sum('total_omr');
        $avgLifetimeValue = $total > 0 ? round($totalRevenue / max($total,1), 3) : 0;

        // Top 10 customers by lifetime spend
        $topCustomers = DB::table('orders')
            ->whereNotIn('status',['cancelled'])
            ->whereNotNull('email')
            ->groupBy('email','first_name','last_name')
            ->selectRaw('email, first_name, last_name, SUM(total_omr) as lifetime_spend, COUNT(*) as order_count, MAX(created_at) as last_order, MIN(created_at) as first_order')
            ->orderByDesc('lifetime_spend')
            ->limit(10)->get();

        // 6-month new customers + orders trend
        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $monthly[] = [
                'month'  => $m->format('M y'),
                'new'    => Customer::whereMonth('created_at',$m->month)->whereYear('created_at',$m->year)->count(),
                'orders' => Order::whereNotIn('status',['cancelled'])->whereMonth('created_at',$m->month)->whereYear('created_at',$m->year)->count(),
                'revenue'=> round((float) Order::whereNotIn('status',['cancelled'])->whereMonth('created_at',$m->month)->whereYear('created_at',$m->year)->sum('total_omr'), 3),
            ];
        }

        // Revenue by governorate (customers)
        $byGov = Order::whereNotIn('status',['cancelled'])
            ->whereNotNull('governorate')
            ->groupBy('governorate')
            ->selectRaw('governorate, COUNT(DISTINCT email) as customers, SUM(total_omr) as revenue, COUNT(*) as orders')
            ->orderByDesc('revenue')->limit(8)->get();

        // Spending tiers
        $spendData = DB::table('orders')
            ->whereNotIn('status',['cancelled'])
            ->whereNotNull('email')
            ->groupBy('email')
            ->selectRaw('email, SUM(total_omr) as total_spend')
            ->get();

        $tiers = ['Under OMR 50'=>0,'OMR 50–100'=>0,'OMR 100–200'=>0,'OMR 200–500'=>0,'Over OMR 500'=>0];
        foreach ($spendData as $row) {
            $s = (float)$row->total_spend;
            if      ($s < 50)  $tiers['Under OMR 50']++;
            elseif  ($s < 100) $tiers['OMR 50–100']++;
            elseif  ($s < 200) $tiers['OMR 100–200']++;
            elseif  ($s < 500) $tiers['OMR 200–500']++;
            else               $tiers['Over OMR 500']++;
        }

        return compact('total','vip','newThisMonth','repeat','inactive',
                       'totalRevenue','avgLifetimeValue','topCustomers',
                       'monthly','byGov','tiers');
    }
}
