<?php

namespace App\Providers\Filament;

use App\Enums\NavigationGroupEnum;
use Filament\Enums\GlobalSearchPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
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
            ->profile(isSimple: false)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandName('Artisan Leather')
            ->globalSearch(position: GlobalSearchPosition::Topbar)
            ->sidebarCollapsibleOnDesktop()

            // ── Sidebar starts minimized (icons only) — hover an icon to see
            // its label, click the header chevron to pin it open. Only seeds
            // the default for browsers that have never toggled it before;
            // an existing user preference in localStorage is left alone.
            ->renderHook(
                'panels::head.end',
                fn () => view('filament.sidebar.default-collapsed')
            )

            // ── Navigation order — guaranteed on every request type ─────────────
            ->navigation(function (NavigationBuilder $nav): NavigationBuilder {
                // bootCurrentPanel() is idempotent — safe to call on any request.
                // Required because /livewire/update bypasses SetUpPanel middleware,
                // leaving resources/pages undiscovered without this explicit boot.
                filament()->bootCurrentPanel();

                $order = array_flip([
                    'Sales', 'Customers', 'Catalogue', 'Operations', 'Finance',
                    'Analytics', 'Compliance', 'Human Resources', 'Content', 'Settings',
                ]);

                // One icon per group — lets the collapsed sidebar show a single
                // parent icon that flies out the group's items on hover, instead
                // of every item's icon stacked up individually.
                $groupIcons = [
                    'Sales'           => Heroicon::OutlinedShoppingCart,
                    'Customers'       => Heroicon::OutlinedUserGroup,
                    'Catalogue'       => Heroicon::OutlinedCube,
                    'Operations'      => Heroicon::OutlinedClipboardDocumentList,
                    'Finance'         => Heroicon::OutlinedBanknotes,
                    'Analytics'       => Heroicon::OutlinedChartBar,
                    'Compliance'      => Heroicon::OutlinedShieldCheck,
                    'Human Resources' => Heroicon::OutlinedUsers,
                    'Content'         => Heroicon::OutlinedDocumentText,
                    'Settings'        => Heroicon::OutlinedCog6Tooth,
                ];

                $items = collect();
                foreach (filament()->getResources() as $resource) {
                    $items = $items->merge($resource::getNavigationItems());
                }
                foreach (filament()->getPages() as $page) {
                    $items = $items->merge($page::getNavigationItems());
                }

                $grouped = $items
                    ->filter(fn($item) => $item->isVisible())
                    ->sortBy(fn($item) => $item->getSort())
                    ->groupBy(fn($item) => $item->getGroup() ?? '');

                $groups = collect($order)
                    ->map(fn($_, $groupName) =>
                        NavigationGroup::make($groupName)
                            ->icon($groupIcons[$groupName] ?? null)
                            ->items($grouped->get($groupName, collect())->all())
                    )
                    ->filter(fn($g) => filled($g->getItems()))
                    ->values()
                    ->all();

                return $nav->groups($groups);
            })

            // ── Topbar notification icons ─────────────────────────────────────
            ->renderHook(
                'panels::topbar.end',
                fn () => view('filament.topbar.notifications')
            )

            // ── Topbar "recently visited" dropdown — last 7 admin pages you
            // navigated to, per-browser (localStorage), clickable straight back.
            ->renderHook(
                'panels::topbar.end',
                fn () => view('filament.topbar.recent-pages')
            )
            ->renderHook(
                'panels::body.end',
                fn () => view('filament.topbar.recent-pages-tracker')
            )

            // ── Topbar recent activity dropdown — last 7 data changes
            // (orders/products/posts/customers created or edited), clickable
            // straight to that record.
            ->renderHook(
                'panels::topbar.end',
                fn () => view('filament.topbar.activity-log')
            )

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
