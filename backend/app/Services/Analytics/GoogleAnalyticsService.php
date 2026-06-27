<?php

namespace App\Services\Analytics;

use Carbon\Carbon;
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy;
use Google\Analytics\Data\V1beta\RunReportRequest;

class GoogleAnalyticsService
{
    protected BetaAnalyticsDataClient $client;

    protected string $property;

    public function __construct()
    {
        $this->client = new BetaAnalyticsDataClient([
            'credentials' => config('services.google.credentials_path'),
        ]);

        $this->property = 'properties/' . config('services.google.ga4_property_id');
    }

    public function getDailySummary(Carbon $from, Carbon $to): array
    {
        $response = $this->client->runReport(new RunReportRequest([
            'property' => $this->property,
            'dateRanges' => [new DateRange([
                'startDate' => $from->toDateString(),
                'endDate' => $to->toDateString(),
            ])],
            'dimensions' => [new Dimension(['name' => 'date'])],
            'metrics' => [
                new Metric(['name' => 'sessions']),
                new Metric(['name' => 'totalUsers']),
                new Metric(['name' => 'newUsers']),
                new Metric(['name' => 'screenPageViews']),
                new Metric(['name' => 'bounceRate']),
                new Metric(['name' => 'averageSessionDuration']),
                new Metric(['name' => 'conversions']),
            ],
        ]));

        return $this->mapRows($response, function (array $dims, array $metrics) {
            return [
                'date' => Carbon::createFromFormat('Ymd', $dims[0])->toDateString(),
                'sessions' => (int) $metrics[0],
                'users' => (int) $metrics[1],
                'new_users' => (int) $metrics[2],
                'page_views' => (int) $metrics[3],
                'bounce_rate' => round((float) $metrics[4] * 100, 2),
                'avg_engagement_time' => (int) round((float) $metrics[5]),
                'conversions' => (int) $metrics[6],
            ];
        });
    }

    public function getTopPages(Carbon $from, Carbon $to, int $limit = 20): array
    {
        $response = $this->client->runReport(new RunReportRequest([
            'property' => $this->property,
            'dateRanges' => [new DateRange([
                'startDate' => $from->toDateString(),
                'endDate' => $to->toDateString(),
            ])],
            'dimensions' => [
                new Dimension(['name' => 'date']),
                new Dimension(['name' => 'pagePath']),
            ],
            'metrics' => [
                new Metric(['name' => 'screenPageViews']),
                new Metric(['name' => 'totalUsers']),
                new Metric(['name' => 'bounceRate']),
                new Metric(['name' => 'averageSessionDuration']),
                new Metric(['name' => 'conversions']),
            ],
            'orderBys' => [new OrderBy([
                'metric' => new MetricOrderBy(['metric_name' => 'screenPageViews']),
                'desc' => true,
            ])],
            'limit' => $limit,
        ]));

        return $this->mapRows($response, function (array $dims, array $metrics) {
            return [
                'date' => Carbon::createFromFormat('Ymd', $dims[0])->toDateString(),
                'page_path' => $dims[1],
                'views' => (int) $metrics[0],
                'users' => (int) $metrics[1],
                'bounce_rate' => round((float) $metrics[2] * 100, 2),
                'avg_engagement_time' => (int) round((float) $metrics[3]),
                'conversions' => (int) $metrics[4],
            ];
        });
    }

    public function getDeviceBreakdown(Carbon $from, Carbon $to): array
    {
        $response = $this->client->runReport(new RunReportRequest([
            'property' => $this->property,
            'dateRanges' => [new DateRange([
                'startDate' => $from->toDateString(),
                'endDate' => $to->toDateString(),
            ])],
            'dimensions' => [
                new Dimension(['name' => 'date']),
                new Dimension(['name' => 'deviceCategory']),
            ],
            'metrics' => [
                new Metric(['name' => 'sessions']),
                new Metric(['name' => 'totalUsers']),
            ],
        ]));

        return $this->mapRows($response, function (array $dims, array $metrics) {
            return [
                'date' => Carbon::createFromFormat('Ymd', $dims[0])->toDateString(),
                'device_category' => $dims[1],
                'sessions' => (int) $metrics[0],
                'users' => (int) $metrics[1],
            ];
        });
    }

    public function getCountryBreakdown(Carbon $from, Carbon $to): array
    {
        $response = $this->client->runReport(new RunReportRequest([
            'property' => $this->property,
            'dateRanges' => [new DateRange([
                'startDate' => $from->toDateString(),
                'endDate' => $to->toDateString(),
            ])],
            'dimensions' => [
                new Dimension(['name' => 'date']),
                new Dimension(['name' => 'country']),
            ],
            'metrics' => [
                new Metric(['name' => 'sessions']),
                new Metric(['name' => 'totalUsers']),
            ],
        ]));

        return $this->mapRows($response, function (array $dims, array $metrics) {
            return [
                'date' => Carbon::createFromFormat('Ymd', $dims[0])->toDateString(),
                'country' => $dims[1],
                'sessions' => (int) $metrics[0],
                'users' => (int) $metrics[1],
            ];
        });
    }

    protected function mapRows(\Google\Analytics\Data\V1beta\RunReportResponse $response, callable $mapper): array
    {
        $rows = [];

        foreach ($response->getRows() as $row) {
            $dims = [];
            foreach ($row->getDimensionValues() as $value) {
                $dims[] = $value->getValue();
            }

            $metrics = [];
            foreach ($row->getMetricValues() as $value) {
                $metrics[] = $value->getValue();
            }

            $rows[] = $mapper($dims, $metrics);
        }

        return $rows;
    }
}
