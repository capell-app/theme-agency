<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Actions;

use Capell\GA4Reports\Data\GA4ReportsPageMetricData;
use Capell\GA4Reports\Models\GA4ReportsPageMetric;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static GA4ReportsPageMetric run(GA4ReportsPageMetricData $metric)
 */
final class PersistGA4ReportsPageMetricAction
{
    use AsAction;

    public function handle(GA4ReportsPageMetricData $metric): GA4ReportsPageMetric
    {
        GA4ReportsPageMetric::query()->upsert(
            [[
                'property_id' => $metric->propertyId,
                'metric_date' => $metric->metricDate->toDateString(),
                'page_path' => $metric->pagePath,
                'page_title' => $metric->pageTitle,
                'total_users' => $metric->totalUsers,
                'sessions' => $metric->sessions,
                'screen_page_views' => $metric->screenPageViews,
                'event_count' => $metric->eventCount,
                'conversions' => $metric->conversions,
                'created_at' => now(),
                'updated_at' => now(),
            ]],
            ['property_id', 'metric_date', 'page_path'],
            [
                'page_title',
                'total_users',
                'sessions',
                'screen_page_views',
                'event_count',
                'conversions',
                'updated_at',
            ],
        );

        return GA4ReportsPageMetric::query()
            ->where('property_id', $metric->propertyId)
            ->whereDate('metric_date', $metric->metricDate->toDateString())
            ->where('page_path', $metric->pagePath)
            ->firstOrFail();
    }
}
