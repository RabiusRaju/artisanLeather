<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandName('Artisan Leather')

            // ── PWA: meta tags + SW ───────────────────────────────────────────
            ->renderHook(
                'panels::head.end',
                fn () => view('filament.pwa.head')
            )
            ->renderHook(
                'panels::body.end',
                fn () => view('filament.pwa.install-button')
            )

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // Row 1: 6 KPI cards with 7-day sparkline charts
                \App\Filament\Widgets\StatsOverview::class,

                // Row 2: 30-day revenue trend bar chart (full width)
                \App\Filament\Widgets\RevenueTrendWidget::class,

                // Row 3: 3 donut charts — Revenue Sources | Order Status | Payment Methods
                \App\Filament\Widgets\ChartsRowWidget::class,

                // Row 4: Action Required | Low Stock | Order Pipeline
                \App\Filament\Widgets\ActionRequiredWidget::class,
                \App\Filament\Widgets\LowStockWidget::class,
                \App\Filament\Widgets\OrderPipelineWidget::class,

                // Row 5: Top Products bars + Collections donut + 4-week trend
                \App\Filament\Widgets\ProductsCollectionsChartWidget::class,

                // Row 6: Custom Orders pipeline
                \App\Filament\Widgets\CustomOrdersPipelineWidget::class,

                // Row 7: Recent Orders table
                \App\Filament\Widgets\RecentOrdersTable::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
