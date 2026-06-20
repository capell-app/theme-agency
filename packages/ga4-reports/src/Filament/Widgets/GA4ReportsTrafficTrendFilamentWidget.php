<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Filament\Widgets;

use Capell\Admin\Contracts\CapellFilamentWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\Admin\Filament\Concerns\HasLineChartOptions;
use Capell\GA4Reports\Actions\BuildGA4ReportsTrendAction;
use Capell\GA4Reports\Data\GA4ReportsTrendPointData;
use Capell\GA4Reports\Data\GA4ReportsWindowData;
use Capell\GA4Reports\Filament\Widgets\Concerns\BuildsGA4ReportsDashboardWindow;
use Filament\Widgets\ChartWidget;
use Override;

final class GA4ReportsTrafficTrendFilamentWidget extends ChartWidget implements CapellFilamentWidgetContract
{
    use BuildsGA4ReportsDashboardWindow;
    use GatedByRoleAndSettings;
    use HasLineChartOptions;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['admin', 'super_admin'];

    protected static string $settingsKey = 'ga4_reports_traffic_trend';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = ['md' => 2];

    protected static ?int $sort = 22;

    #[Override]
    public function getHeading(): string
    {
        return __('capell-ga4-reports::widgets.traffic_trend');
    }

    #[Override]
    protected function getData(): array
    {
        $window = $this->getGA4ReportsWindow();
        $points = BuildGA4ReportsTrendAction::run($window);
        $previousPoints = $window instanceof GA4ReportsWindowData
            ? BuildGA4ReportsTrendAction::run($this->getPreviousGA4ReportsWindow($window))
            : [];

        return [
            'datasets' => [
                [
                    'label' => __('capell-ga4-reports::widgets.screen_page_views'),
                    'data' => array_map(
                        fn (GA4ReportsTrendPointData $point): int => $point->screenPageViews,
                        $points,
                    ),
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.12)',
                    'tension' => 0.35,
                ],
                [
                    'label' => __('capell-ga4-reports::widgets.previous_screen_page_views'),
                    'data' => array_map(
                        fn (GA4ReportsTrendPointData $point): int => $point->screenPageViews,
                        $previousPoints,
                    ),
                    'borderColor' => 'rgba(37, 99, 235, 0.45)',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.05)',
                    'borderDash' => [6, 4],
                    'tension' => 0.35,
                ],
                [
                    'label' => __('capell-ga4-reports::widgets.sessions'),
                    'data' => array_map(
                        fn (GA4ReportsTrendPointData $point): int => $point->sessions,
                        $points,
                    ),
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.12)',
                    'tension' => 0.35,
                ],
                [
                    'label' => __('capell-ga4-reports::widgets.previous_sessions'),
                    'data' => array_map(
                        fn (GA4ReportsTrendPointData $point): int => $point->sessions,
                        $previousPoints,
                    ),
                    'borderColor' => 'rgba(22, 163, 74, 0.45)',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.05)',
                    'borderDash' => [6, 4],
                    'tension' => 0.35,
                ],
            ],
            'labels' => array_map(
                fn (GA4ReportsTrendPointData $point): string => $point->label,
                $points,
            ),
        ];
    }
}
