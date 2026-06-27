<?php

namespace App\Jobs\Analytics;

use App\Models\Analytics\AnalyticsKeyword;
use App\Models\Analytics\AnalyticsSearchConsole;
use App\Models\Setting;
use App\Services\Analytics\SearchConsoleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncSearchConsoleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function handle(SearchConsoleService $gsc): void
    {
        // Search Console data typically lags 2-3 days, so re-pull a window
        // wide enough to catch rows that finalize after the previous run.
        $from = Carbon::today()->subDays(4);
        $to = Carbon::today();

        try {
            AnalyticsSearchConsole::upsert(
                $gsc->getDailyMetrics($from, $to),
                ['date'],
                ['clicks', 'impressions', 'ctr', 'position']
            );

            AnalyticsKeyword::upsert(
                $gsc->getTopQueries($from, $to),
                ['date', 'query'],
                ['clicks', 'impressions', 'ctr', 'position']
            );

            Setting::set('analytics.gsc_last_sync', now()->toDateTimeString());
            Setting::set('analytics.gsc_last_error', null);
        } catch (Throwable $e) {
            Log::channel('analytics')->error('Search Console sync failed: ' . $e->getMessage());
            Setting::set('analytics.gsc_last_error', $e->getMessage());

            throw $e;
        }
    }
}
