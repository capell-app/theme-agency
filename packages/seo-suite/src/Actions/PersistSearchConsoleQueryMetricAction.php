<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\SeoSuite\Models\SearchConsoleQueryMetric;
use Carbon\CarbonInterface;
use Lorisleiva\Actions\Concerns\AsAction;

final class PersistSearchConsoleQueryMetricAction
{
    use AsAction;

    public function handle(
        int $siteId,
        string $query,
        string $url,
        CarbonInterface $windowStart,
        CarbonInterface $windowEnd,
        int $clicks,
        int $impressions,
        float $ctr,
        float $averagePosition,
        int $previousClicks,
        int $previousImpressions,
        float $previousCtr,
        float $previousAveragePosition,
    ): SearchConsoleQueryMetric {
        $normalizedQuery = trim(mb_strtolower($query));
        $normalizedUrl = trim($url);
        $clickDelta = $clicks - $previousClicks;
        $impressionDelta = $impressions - $previousImpressions;
        $positionDelta = $averagePosition - $previousAveragePosition;

        return SearchConsoleQueryMetric::query()->updateOrCreate(
            [
                'site_id' => $siteId,
                'query_hash' => hash('sha256', $normalizedQuery),
                'url_hash' => hash('sha256', $normalizedUrl),
                'window_start' => $windowStart->toDateString(),
                'window_end' => $windowEnd->toDateString(),
            ],
            [
                'query' => $normalizedQuery,
                'url' => $normalizedUrl,
                'clicks' => $clicks,
                'impressions' => $impressions,
                'ctr' => $ctr,
                'average_position' => $averagePosition,
                'previous_clicks' => $previousClicks,
                'previous_impressions' => $previousImpressions,
                'previous_ctr' => $previousCtr,
                'previous_average_position' => $previousAveragePosition,
                'click_delta' => $clickDelta,
                'impression_delta' => $impressionDelta,
                'position_delta' => $positionDelta,
                'synced_at' => now(),
            ],
        );
    }
}
