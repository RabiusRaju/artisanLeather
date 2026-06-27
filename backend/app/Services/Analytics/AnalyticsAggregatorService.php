<?php

namespace App\Services\Analytics;

use App\Models\Analytics\AnalyticsCountry;
use App\Models\Analytics\AnalyticsDailySummary;
use App\Models\Analytics\AnalyticsDevice;
use App\Models\Analytics\AnalyticsKeyword;
use App\Models\Analytics\AnalyticsSearchConsole;
use App\Models\Analytics\AnalyticsTopPage;
use App\Models\Order;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AnalyticsAggregatorService
{
    public function getDashboard(Carbon $from, Carbon $to): array
    {
        $ttl = $to->isToday() ? 300 : 3600;
        $key = 'analytics:dashboard:' . $from->toDateString() . ':' . $to->toDateString();

        return Cache::remember($key, $ttl, fn () => [
            'kpis' => $this->kpis($from, $to),
            'visitorsTrend' => $this->visitorsTrend($from, $to),
            'ordersTrend' => $this->ordersTrend($from, $to),
            'revenueTrend' => $this->revenueTrend($from, $to),
            'searchTrend' => $this->searchTrend($from, $to),
            'deviceSplit' => $this->deviceSplit($from, $to),
            'countryBreakdown' => $this->countryBreakdown($from, $to),
            'topPages' => $this->topPages($from, $to),
            'topKeywords' => $this->topKeywords($from, $to),
            'sync' => $this->syncStatus(),
        ]);
    }

    protected function kpis(Carbon $from, Carbon $to): array
    {
        $ga = AnalyticsDailySummary::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('SUM(sessions) sessions, SUM(users) users, SUM(new_users) new_users, AVG(bounce_rate) bounce_rate, AVG(avg_engagement_time) avg_engagement_time')
            ->first();

        $gsc = AnalyticsSearchConsole::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('SUM(clicks) clicks, SUM(impressions) impressions, AVG(ctr) ctr, AVG(position) position')
            ->first();

        $orders = Order::whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->count();

        $revenue = (float) Order::whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$from->startOfDay(), $to->copy()->endOfDay()])
            ->sum('total_omr');

        $sessions = (int) ($ga->sessions ?? 0);

        return [
            'sessions' => $sessions,
            'users' => (int) ($ga->users ?? 0),
            'new_users' => (int) ($ga->new_users ?? 0),
            'bounce_rate' => round((float) ($ga->bounce_rate ?? 0), 2),
            'avg_engagement_time' => (int) round((float) ($ga->avg_engagement_time ?? 0)),
            'gsc_clicks' => (int) ($gsc->clicks ?? 0),
            'gsc_impressions' => (int) ($gsc->impressions ?? 0),
            'gsc_ctr' => round((float) ($gsc->ctr ?? 0), 2),
            'gsc_position' => round((float) ($gsc->position ?? 0), 2),
            'orders' => $orders,
            'revenue' => round($revenue, 3),
            'conversion_rate' => $sessions > 0 ? round(($orders / $sessions) * 100, 2) : 0,
            'aov' => $orders > 0 ? round($revenue / $orders, 3) : 0,
        ];
    }

    protected function visitorsTrend(Carbon $from, Carbon $to): array
    {
        return AnalyticsDailySummary::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')
            ->get(['date', 'sessions', 'users'])
            ->map(fn ($row) => [
                'date' => $row->date->format('d M'),
                'sessions' => $row->sessions,
                'users' => $row->users,
            ])->all();
    }

    protected function ordersTrend(Carbon $from, Carbon $to): array
    {
        return Order::whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders')
            ->groupBy('date')->orderBy('date')->get()
            ->map(fn ($row) => ['date' => Carbon::parse($row->date)->format('d M'), 'orders' => (int) $row->orders])
            ->all();
    }

    protected function revenueTrend(Carbon $from, Carbon $to): array
    {
        return Order::whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, SUM(total_omr) as revenue')
            ->groupBy('date')->orderBy('date')->get()
            ->map(fn ($row) => ['date' => Carbon::parse($row->date)->format('d M'), 'revenue' => round((float) $row->revenue, 3)])
            ->all();
    }

    protected function searchTrend(Carbon $from, Carbon $to): array
    {
        return AnalyticsSearchConsole::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')
            ->get(['date', 'clicks', 'impressions', 'ctr'])
            ->map(fn ($row) => [
                'date' => $row->date->format('d M'),
                'clicks' => $row->clicks,
                'impressions' => $row->impressions,
                'ctr' => (float) $row->ctr,
            ])->all();
    }

    protected function deviceSplit(Carbon $from, Carbon $to): array
    {
        return AnalyticsDevice::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('device_category, SUM(sessions) as sessions')
            ->groupBy('device_category')->orderByDesc('sessions')->get()
            ->map(fn ($row) => ['device' => $row->device_category, 'sessions' => (int) $row->sessions])
            ->all();
    }

    protected function countryBreakdown(Carbon $from, Carbon $to): array
    {
        return AnalyticsCountry::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('country, SUM(sessions) as sessions')
            ->groupBy('country')->orderByDesc('sessions')->limit(8)->get()
            ->map(fn ($row) => ['country' => $row->country, 'sessions' => (int) $row->sessions])
            ->all();
    }

    protected function topPages(Carbon $from, Carbon $to, int $limit = 10): array
    {
        return AnalyticsTopPage::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('page_path, SUM(views) as views, SUM(users) as users, AVG(bounce_rate) as bounce_rate, AVG(avg_engagement_time) as avg_engagement_time, SUM(conversions) as conversions, SUM(revenue_omr) as revenue_omr')
            ->groupBy('page_path')->orderByDesc('views')->limit($limit)->get()
            ->map(fn ($row) => [
                'page_path' => $row->page_path,
                'views' => (int) $row->views,
                'users' => (int) $row->users,
                'bounce_rate' => round((float) $row->bounce_rate, 2),
                'avg_engagement_time' => (int) round((float) $row->avg_engagement_time),
                'conversions' => (int) $row->conversions,
                'revenue_omr' => round((float) $row->revenue_omr, 3),
            ])->all();
    }

    protected function topKeywords(Carbon $from, Carbon $to, int $limit = 10): array
    {
        return AnalyticsKeyword::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('query, SUM(clicks) as clicks, SUM(impressions) as impressions, AVG(ctr) as ctr, AVG(position) as position')
            ->groupBy('query')->orderByDesc('clicks')->limit($limit)->get()
            ->map(fn ($row) => [
                'query' => $row->query,
                'clicks' => (int) $row->clicks,
                'impressions' => (int) $row->impressions,
                'ctr' => round((float) $row->ctr, 2),
                'position' => round((float) $row->position, 2),
            ])->all();
    }

    protected function syncStatus(): array
    {
        return [
            'ga4_last_sync' => Setting::get('analytics.ga4_last_sync'),
            'ga4_last_error' => Setting::get('analytics.ga4_last_error'),
            'gsc_last_sync' => Setting::get('analytics.gsc_last_sync'),
            'gsc_last_error' => Setting::get('analytics.gsc_last_error'),
        ];
    }
}
