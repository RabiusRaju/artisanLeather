<?php
namespace App\Filament\Pages\Analytics;

use App\Models\Customer;
use App\Models\Order;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class CustomerAnalytics extends Page
{
    protected string $view = 'filament.pages.analytics.customer-analytics';
    public static function getNavigationIcon(): string  { return 'heroicon-o-users'; }
    public static function getNavigationGroup(): string { return 'Analytics'; }
    public static function getNavigationSort(): int     { return 3; }
    public function getTitle(): string                  { return 'Customer Analytics'; }

    public function getData(): array
    {
        $total      = Customer::count();
        $vip        = Customer::where('status','vip')->count();
        $newThisMonth = Customer::whereMonth('created_at',now()->month)->whereYear('created_at',now()->year)->count();

        // Repeat buyers — select only email to satisfy ONLY_FULL_GROUP_BY
        $repeat = DB::table('orders')
            ->whereNotIn('status', ['cancelled'])
            ->select('email')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->get()->count();

        // Inactive customers (no order in 60+ days)
        $inactive = DB::table('orders')
            ->whereNotIn('status', ['cancelled'])
            ->select('email')
            ->groupBy('email')
            ->havingRaw('MAX(created_at) < ?', [now()->subDays(60)->toDateTimeString()])
            ->get()->count();

        // Top 10 customers by lifetime spend
        $topCustomers = DB::table('orders')
            ->whereNotIn('status',['cancelled'])
            ->whereNotNull('email')
            ->groupBy('email','first_name','last_name')
            ->selectRaw('email, first_name, last_name, SUM(total_omr) as lifetime_spend, COUNT(*) as order_count, MAX(created_at) as last_order')
            ->orderByDesc('lifetime_spend')
            ->limit(10)->get();

        // Orders per month (new customers)
        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $monthly[] = [
                'month'     => $m->format('M y'),
                'new'       => Customer::whereMonth('created_at',$m->month)->whereYear('created_at',$m->year)->count(),
                'orders'    => Order::whereNotIn('status',['cancelled'])->whereMonth('created_at',$m->month)->whereYear('created_at',$m->year)->count(),
            ];
        }

        return compact('total','vip','newThisMonth','repeat','inactive','topCustomers','monthly');
    }
}
