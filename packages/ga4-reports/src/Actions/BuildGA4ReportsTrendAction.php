<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Actions;

use Capell\GA4Reports\Data\GA4ReportsTrendPointData;
use Capell\GA4Reports\Data\GA4ReportsWindowData;
use Capell\GA4Reports\Models\GA4ReportsDailyMetric;
use Capell\GA4Reports\Support\GA4ReportsDashboardCache;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static list<GA4ReportsTrendPointData> run(?GA4ReportsWindowData $window = null)
 */
final class BuildGA4ReportsTrendAction
{
    use AsAction;

    /**
     * @return list<GA4ReportsTrendPointData>
     */
    public function handle(?GA4ReportsWindowData $window = null): array
    {
        $resolvedWindow = $window ?? BuildGA4ReportsWindowAction::run();

        if ($resolvedWindow === null) {
            return [];
        }

        return GA4ReportsDashboardCache::rememberTrend(
            $resolvedWindow,
            fn (): array => $this->build($resolvedWindow),
        );
    }

    /**
     * @return list<GA4ReportsTrendPointData>
     */
    private function build(GA4ReportsWindowData $window): array
    {
        return array_values(GA4ReportsDailyMetric::query()
            ->where('property_id', $window->propertyId)
            ->whereDate('metric_date', '>=', $window->startsAt->toDateString())
            ->whereDate('metric_date', '<=', $window->endsAt->toDateString())
            ->oldest('metric_date')
            ->get()
            ->map(fn (GA4ReportsDailyMetric $metric): GA4ReportsTrendPointData => new GA4ReportsTrendPointData(
                label: $metric->metric_date->format('j M'),
                screenPageViews: $metric->screen_page_views,
                sessions: $metric->sessions,
                totalUsers: $metric->total_users,
            ))
            ->all());
    }
}
