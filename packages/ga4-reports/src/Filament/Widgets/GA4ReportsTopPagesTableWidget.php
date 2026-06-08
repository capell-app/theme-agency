<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Filament\Widgets;

use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\GA4Reports\Actions\BuildTopGA4ReportsPagesAction;
use Capell\GA4Reports\Data\GA4ReportsTopPageData;
use Capell\GA4Reports\Data\GA4ReportsWindowData;
use Capell\GA4Reports\Filament\Widgets\Concerns\BuildsGA4ReportsDashboardWindow;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Collection;
use Override;

final class GA4ReportsTopPagesTableWidget extends BaseWidget
{
    use BuildsGA4ReportsDashboardWindow;
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['admin', 'super_admin'];

    protected static string $settingsKey = 'ga4_reports_top_pages';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 30;

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): Collection => $this->getRecords())
            ->queryStringIdentifier('ga4-reports-page-top-pages')
            ->paginated([10, 25, 50])
            ->searchable(false)
            ->heading(__('capell-ga4-reports::widgets.top_pages'))
            ->columns([
                TextColumn::make('page_path')
                    ->label(__('capell-ga4-reports::widgets.page_path'))
                    ->searchable(),
                TextColumn::make('page_title')
                    ->label(__('capell-ga4-reports::widgets.page_title'))
                    ->searchable(),
                TextColumn::make('screen_page_views')
                    ->label(__('capell-ga4-reports::widgets.screen_page_views'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('comparison')
                    ->label(__('capell-ga4-reports::widgets.comparison')),
                TextColumn::make('sessions')
                    ->label(__('capell-ga4-reports::widgets.sessions'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_users')
                    ->label(__('capell-ga4-reports::widgets.total_users'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('conversions')
                    ->label(__('capell-ga4-reports::widgets.conversions'))
                    ->numeric()
                    ->sortable(),
            ]);
    }

    /**
     * @return Collection<int, array{id: string, page_path: string, page_title: string|null, screen_page_views: int, comparison: string, sessions: int, total_users: int, conversions: int}>
     */
    private function getRecords(): Collection
    {
        $window = $this->getGA4ReportsWindow();
        $previousPages = $window instanceof GA4ReportsWindowData
            ? collect(BuildTopGA4ReportsPagesAction::run($this->getPreviousGA4ReportsWindow($window), 100))
                ->keyBy(fn (GA4ReportsTopPageData $page): string => $page->pagePath)
            : collect();

        return collect(BuildTopGA4ReportsPagesAction::run($window, 100))
            ->map(fn (GA4ReportsTopPageData $page, int $index): array => [
                'id' => 'ga4-reports-page-table-' . $index,
                'page_path' => $page->pagePath,
                'page_title' => $page->pageTitle,
                'screen_page_views' => $page->screenPageViews,
                'comparison' => $this->formatDelta($page->screenPageViews, $previousPages->get($page->pagePath)->screenPageViews ?? 0),
                'sessions' => $page->sessions,
                'total_users' => $page->totalUsers,
                'conversions' => $page->conversions,
            ])
            ->values();
    }

    private function formatDelta(int $current, int $previous): string
    {
        if ($previous === 0) {
            return $current === 0
                ? (string) __('capell-ga4-reports::widgets.no_change')
                : __('capell-ga4-reports::widgets.new_since_previous', ['value' => number_format($current)]);
        }

        $change = (($current - $previous) / $previous) * 100;

        return sprintf('%+0.1f%%', $change);
    }
}
