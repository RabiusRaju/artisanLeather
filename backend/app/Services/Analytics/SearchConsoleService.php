<?php

namespace App\Services\Analytics;

use Carbon\Carbon;
use Google\Client;
use Google\Service\SearchConsole;
use Google\Service\SearchConsole\SearchAnalyticsQueryRequest;

class SearchConsoleService
{
    protected SearchConsole $service;

    protected string $siteUrl;

    public function __construct()
    {
        $client = new Client();
        $client->setAuthConfig(config('services.google.credentials_path'));
        $client->addScope(SearchConsole::WEBMASTERS_READONLY);

        $this->service = new SearchConsole($client);
        $this->siteUrl = config('services.google.search_console_site_url');
    }

    public function getDailyMetrics(Carbon $from, Carbon $to): array
    {
        $response = $this->service->searchanalytics->query($this->siteUrl, new SearchAnalyticsQueryRequest([
            'startDate' => $from->toDateString(),
            'endDate' => $to->toDateString(),
            'dimensions' => ['date'],
            'rowLimit' => 25000,
        ]));

        return array_map(fn ($row) => [
            'date' => $row->getKeys()[0],
            'clicks' => (int) $row->getClicks(),
            'impressions' => (int) $row->getImpressions(),
            'ctr' => round((float) $row->getCtr() * 100, 3),
            'position' => round((float) $row->getPosition(), 2),
        ], $response->getRows() ?? []);
    }

    public function getTopQueries(Carbon $from, Carbon $to, int $limit = 1000): array
    {
        $response = $this->service->searchanalytics->query($this->siteUrl, new SearchAnalyticsQueryRequest([
            'startDate' => $from->toDateString(),
            'endDate' => $to->toDateString(),
            'dimensions' => ['date', 'query'],
            'rowLimit' => $limit,
        ]));

        return array_map(fn ($row) => [
            'date' => $row->getKeys()[0],
            'query' => $row->getKeys()[1],
            'clicks' => (int) $row->getClicks(),
            'impressions' => (int) $row->getImpressions(),
            'ctr' => round((float) $row->getCtr() * 100, 3),
            'position' => round((float) $row->getPosition(), 2),
        ], $response->getRows() ?? []);
    }
}
