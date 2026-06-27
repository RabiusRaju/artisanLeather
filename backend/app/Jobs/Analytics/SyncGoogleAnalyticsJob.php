<?php

namespace App\Jobs\Analytics;

use App\Models\Analytics\AnalyticsCountry;
use App\Models\Analytics\AnalyticsDailySummary;
use App\Models\Analytics\AnalyticsDevice;
use App\Models\Analytics\AnalyticsTopPage;
use App\Models\Setting;
use App\Services\Analytics\GoogleAnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncGoogleAnalyticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function handle(GoogleAnalyticsService $ga): void
    {
        $from = Carbon::yesterday();
        $to = Carbon::today();

        try {
            AnalyticsDailySummary::upsert(
                $ga->getDailySummary($from, $to),
                ['date'],
                ['sessions', 'users', 'new_users', 'page_views', 'bounce_rate', 'avg_engagement_time', 'conversions']
            );

            AnalyticsTopPage::upsert(
                $ga->getTopPages($from, $to),
                ['date', 'page_path'],
                ['views', 'users', 'bounce_rate', 'avg_engagement_time', 'conversions']
            );

            AnalyticsDevice::upsert(
                $ga->getDeviceBreakdown($from, $to),
                ['date', 'device_category'],
                ['sessions', 'users']
            );

            AnalyticsCountry::upsert(
                $ga->getCountryBreakdown($from, $to),
                ['date', 'country'],
                ['sessions', 'users']
            );

            Setting::set('analytics.ga4_last_sync', now()->toDateTimeString());
            Setting::set('analytics.ga4_last_error', null);
        } catch (Throwable $e) {
            Log::channel('analytics')->error('GA4 sync failed: ' . $e->getMessage());
            Setting::set('analytics.ga4_last_error', $e->getMessage());

            throw $e;
        }
    }
}
