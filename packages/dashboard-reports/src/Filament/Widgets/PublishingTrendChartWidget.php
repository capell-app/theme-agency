<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Filament\Widgets;

use Capell\Admin\Contracts\CapellWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\Admin\Filament\Concerns\HasDashboardDateRange;
use Capell\Admin\Filament\Concerns\HasLineChartOptions;
use Capell\DashboardReports\Actions\Dashboard\BuildPublishingTrendAction;
use Capell\DashboardReports\Data\Dashboard\PublishingTrendPointData;
use Filament\Widgets\ChartWidget;
use Override;

final class PublishingTrendChartWidget extends ChartWidget implements CapellWidgetContract
{
    use GatedByRoleAndSettings;
    use HasDashboardDateRange;
    use HasLineChartOptions;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['editor', 'admin', 'super_admin'];

    protected static string $settingsKey = 'publishing_trend';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = ['md' => 2];

    protected static ?int $sort = 1;

    private const string PUBLISHED_BORDER_COLOR = 'rgb(var(--primary-600) / 1)';

    private const string PUBLISHED_BACKGROUND_COLOR = 'rgb(var(--primary-500) / 0.12)';

    private const string SCHEDULED_BORDER_COLOR = 'rgb(var(--warning-600) / 1)';

    private const string SCHEDULED_BACKGROUND_COLOR = 'rgb(var(--warning-500) / 0.12)';

    #[Override]
    public function getHeading(): string
    {
        return __('capell-dashboard-reports::dashboard.widget_publishing_trend');
    }

    #[Override]
    protected function getData(): array
    {
        [$rangeStart, $rangeEnd] = $this->getDashboardDateRange();
        $data = BuildPublishingTrendAction::run($rangeStart, $rangeEnd);

        return [
            'datasets' => [
                [
                    'label' => __('capell-dashboard-reports::dashboard.chart_published_pages'),
                    'data' => array_map(
                        fn (PublishingTrendPointData $point): int => $point->publishedCount,
                        $data->points,
                    ),
                    'borderColor' => self::PUBLISHED_BORDER_COLOR,
                    'backgroundColor' => self::PUBLISHED_BACKGROUND_COLOR,
                    'tension' => 0.35,
                ],
                [
                    'label' => __('capell-dashboard-reports::dashboard.chart_scheduled_pages'),
                    'data' => array_map(
                        fn (PublishingTrendPointData $point): int => $point->scheduledCount,
                        $data->points,
                    ),
                    'borderColor' => self::SCHEDULED_BORDER_COLOR,
                    'backgroundColor' => self::SCHEDULED_BACKGROUND_COLOR,
                    'tension' => 0.35,
                ],
            ],
            'labels' => array_map(
                fn (PublishingTrendPointData $point): string => $point->label,
                $data->points,
            ),
        ];
    }
}
