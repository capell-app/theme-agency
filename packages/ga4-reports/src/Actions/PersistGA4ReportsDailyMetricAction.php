<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Actions;

use Capell\GA4Reports\Data\GA4ReportsDailyMetricData;
use Capell\GA4Reports\Models\GA4ReportsDailyMetric;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static GA4ReportsDailyMetric run(GA4ReportsDailyMetricData $metric)
 */
final class PersistGA4ReportsDailyMetricAction
{
    use AsAction;

    public function handle(GA4ReportsDailyMetricData $metric): GA4ReportsDailyMetric
    {
        GA4ReportsDailyMetric::query()->upsert(
            [[
                'property_id' => $metric->propertyId,
                'metric_date' => $metric->metricDate->toDateString(),
                'total_users' => $metric->totalUsers,
                'sessions' => $metric->sessions,
                'screen_page_views' => $metric->screenPageViews,
                'engaged_sessions' => $metric->engagedSessions,
                'engagement_rate' => $metric->engagementRate,
                'average_session_duration' => $metric->averageSessionDuration,
                'event_count' => $metric->eventCount,
                'conversions' => $metric->conversions,
                'created_at' => now(),
                'updated_at' => now(),
            ]],
            ['property_id', 'metric_date'],
            [
                'total_users',
                'sessions',
                'screen_page_views',
                'engaged_sessions',
                'engagement_rate',
                'average_session_duration',
                'event_count',
                'conversions',
                'updated_at',
            ],
        );

        return GA4ReportsDailyMetric::query()
            ->where('property_id', $metric->propertyId)
            ->whereDate('metric_date', $metric->metricDate->toDateString())
            ->firstOrFail();
    }
}
