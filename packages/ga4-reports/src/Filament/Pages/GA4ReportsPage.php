<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Filament\Pages;

use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Capell\GA4Reports\Actions\ResolveGA4ReportsConfigAction;
use Capell\GA4Reports\Actions\SyncGA4ReportsMetricsAction;
use Capell\GA4Reports\Filament\Widgets\GA4ReportsOverviewStatsWidget;
use Capell\GA4Reports\Filament\Widgets\GA4ReportsSetupStatusWidget;
use Capell\GA4Reports\Filament\Widgets\GA4ReportsTopPagesTableWidget;
use Capell\GA4Reports\Filament\Widgets\GA4ReportsTopPagesWidget;
use Capell\GA4Reports\Filament\Widgets\GA4ReportsTrafficTrendWidget;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;
use Override;

final class GA4ReportsPage extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ChartBarSquare;

    protected static ?int $navigationSort = 2;

    protected string $view = 'capell-ga4-reports::filament.pages.ga4-reports';

    protected static ?string $slug = 'ga4-reports';

    #[Override]
    public static function getSlug(?Panel $panel = null): string
    {
        return ResolveGA4ReportsConfigAction::run()->routeSlug;
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('capell-ga4-reports::widgets.ga4_reports');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('capell-admin::navigation.group_monitoring');
    }

    #[Override]
    public function getTitle(): string
    {
        return __('capell-ga4-reports::widgets.ga4_reports');
    }

    #[Override]
    public function getSubheading(): string
    {
        return __('capell-ga4-reports::widgets.ga4_reports_hint');
    }

    #[Override]
    protected function getHeaderWidgets(): array
    {
        return [
            GA4ReportsOverviewStatsWidget::class,
            GA4ReportsTrafficTrendWidget::class,
            GA4ReportsTopPagesWidget::class,
            GA4ReportsSetupStatusWidget::class,
        ];
    }

    #[Override]
    protected function getFooterWidgets(): array
    {
        return [
            GA4ReportsTopPagesTableWidget::class,
        ];
    }

    /**
     * @return array<int, Action>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncNow')
                ->label(__('capell-ga4-reports::sync.sync_now'))
                ->icon('heroicon-o-arrow-path')
                ->action(fn (): null => $this->syncNow()),
        ];
    }

    private function syncNow(): null
    {
        $result = SyncGA4ReportsMetricsAction::run();

        $notification = Notification::make()
            ->title($result->message);

        ($result->synced ? $notification->success() : $notification->danger())
            ->send();

        return null;
    }
}
