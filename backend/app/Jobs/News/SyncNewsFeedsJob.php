<?php

namespace App\Jobs\News;

use App\Models\Setting;
use App\Services\NewsFeedScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncNewsFeedsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function handle(NewsFeedScraperService $scraper): void
    {
        try {
            $imported = $scraper->syncFeeds();

            Setting::set('news.last_sync', now()->toDateTimeString());
            Setting::set('news.last_error', null);
            Setting::set('news.last_imported_count', $imported);
        } catch (Throwable $e) {
            Log::channel('analytics')->error('News feed sync failed: ' . $e->getMessage());
            Setting::set('news.last_error', $e->getMessage());

            throw $e;
        }
    }
}
