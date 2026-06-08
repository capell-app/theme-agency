<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Filament\Widgets;

use Capell\Admin\Contracts\CapellWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\GA4Reports\Actions\BuildGA4ReportsOverviewAction;
use Capell\GA4Reports\Data\GA4ReportsOverviewData;
use Capell\GA4Reports\Data\GA4ReportsWindowData;
use Capell\GA4Reports\Filament\Widgets\Concerns\BuildsGA4ReportsDashboardWindow;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Collection;
use Override;

final class GA4ReportsOverviewStatsWidget extends BaseWidget implements CapellWidgetContract
{
    use BuildsGA4ReportsDashboardWindow;
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['admin', 'super_admin'];

    protected static string $settingsKey = 'ga4_reports_overview';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 21;

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): Collection => $this->getRecords())
            ->queryStringIdentifier('ga4-reports-overview')
            ->paginated(false)
            ->searchable(false)
            ->heading(__('capell-ga4-reports::widgets.overview'))
            ->columns([
                TextColumn::make('label')
                    ->label(__('capell-ga4-reports::widgets.metric')),
                TextColumn::make('value')
                    ->label(__('capell-ga4-reports::widgets.value')),
                TextColumn::make('comparison')
                    ->label(__('capell-ga4-reports::widgets.comparison')),
            ]);
    }

    /**
     * @return Collection<array-key, mixed>
     */
    private function getRecords(): Collection
    {
        $window = $this->getGA4ReportsWindow();
        $overview = BuildGA4ReportsOverviewAction::run($window);
        $previousOverview = $window instanceof GA4ReportsWindowData
            ? BuildGA4ReportsOverviewAction::run($this->getPreviousGA4ReportsWindow($window))
            : new GA4ReportsOverviewData(0, 0, 0, 0, 0.0, 0.0);

        return collect([
            [
                'id' => 'screen-page-views',
                'label' => (string) __('capell-ga4-reports::widgets.screen_page_views'),
                'value' => number_format($overview->screenPageViews),
                'comparison' => $this->formatDelta($overview->screenPageViews, $previousOverview->screenPageViews),
            ],
            [
                'id' => 'sessions',
                'label' => (string) __('capell-ga4-reports::widgets.sessions'),
                'value' => number_format($overview->sessions),
                'comparison' => $this->formatDelta($overview->sessions, $previousOverview->sessions),
            ],
            [
                'id' => 'total-users',
                'label' => (string) __('capell-ga4-reports::widgets.total_users'),
                'value' => number_format($overview->totalUsers),
                'comparison' => $this->formatDelta($overview->totalUsers, $previousOverview->totalUsers),
            ],
            [
                'id' => 'engagement-rate',
                'label' => (string) __('capell-ga4-reports::widgets.engagement_rate'),
                'value' => number_format($overview->engagementRate * 100, 1) . '%',
                'comparison' => $this->formatPercentagePointDelta($overview->engagementRate, $previousOverview->engagementRate),
            ],
            [
                'id' => 'conversions',
                'label' => (string) __('capell-ga4-reports::widgets.conversions'),
                'value' => number_format($overview->conversions),
                'comparison' => $this->formatDelta($overview->conversions, $previousOverview->conversions),
            ],
        ]);
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

    private function formatPercentagePointDelta(float $current, float $previous): string
    {
        $change = ($current - $previous) * 100;

        return sprintf('%+0.1f pp', $change);
    }
}
