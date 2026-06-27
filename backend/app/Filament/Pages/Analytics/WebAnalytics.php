<?php

namespace App\Filament\Pages\Analytics;

use App\Enums\NavigationGroupEnum;
use App\Services\Analytics\AnalyticsAggregatorService;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class WebAnalytics extends Page
{
    protected string $view = 'filament.pages.analytics.web-analytics';

    public static function getNavigationIcon(): string  { return 'heroicon-o-globe-alt'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Analytics->value; }
    public static function getNavigationSort(): int     { return 0; }
    public function getTitle(): string                  { return 'Web Analytics'; }

    public string $range = '7d';

    public ?string $customFrom = null;

    public ?string $customTo = null;

    protected function resolveRange(): array
    {
        $today = Carbon::today();

        return match ($this->range) {
            'today' => [$today->copy(), $today->copy()],
            'yesterday' => [$today->copy()->subDay(), $today->copy()->subDay()],
            '30d' => [$today->copy()->subDays(29), $today->copy()],
            '90d' => [$today->copy()->subDays(89), $today->copy()],
            'year' => [$today->copy()->startOfYear(), $today->copy()],
            'custom' => [
                $this->customFrom ? Carbon::parse($this->customFrom) : $today->copy()->subDays(6),
                $this->customTo ? Carbon::parse($this->customTo) : $today->copy(),
            ],
            default => [$today->copy()->subDays(6), $today->copy()],
        };
    }

    public function getData(): array
    {
        [$from, $to] = $this->resolveRange();

        return app(AnalyticsAggregatorService::class)->getDashboard($from, $to);
    }
}
