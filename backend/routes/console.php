<?php

use App\Jobs\Analytics\SyncGoogleAnalyticsJob;
use App\Jobs\Analytics\SyncSearchConsoleJob;
use App\Jobs\News\SyncNewsFeedsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new SyncGoogleAnalyticsJob)->hourly();
Schedule::job(new SyncSearchConsoleJob)->dailyAt('03:00');
Schedule::job(new SyncNewsFeedsJob)->everySixHours();
