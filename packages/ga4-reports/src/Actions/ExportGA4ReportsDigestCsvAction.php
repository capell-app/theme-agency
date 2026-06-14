<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Actions;

use Capell\GA4Reports\Data\GA4ReportsDigestData;
use Capell\GA4Reports\Data\GA4ReportsTopPageData;
use Capell\GA4Reports\Data\GA4ReportsTrendPointData;
use Capell\GA4Reports\Data\GA4ReportsWindowData;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

/**
 * @method static string run(?GA4ReportsWindowData $window = null, int $topPageLimit = 10)
 */
final class ExportGA4ReportsDigestCsvAction
{
    use AsAction;

    public function handle(?GA4ReportsWindowData $window = null, int $topPageLimit = 10): string
    {
        $digest = BuildGA4ReportsDigestAction::run($window, $topPageLimit);

        $rows = [[
            'section',
            'label',
            'value',
            'sessions',
            'total_users',
            'conversions',
            'extra',
        ]];

        if ($digest instanceof GA4ReportsDigestData) {
            $rows = [
                ...$rows,
                ...$this->overviewRows($digest),
                ...$this->trendRows($digest),
                ...$this->topPageRows($digest),
            ];
        }

        return $this->toCsv($rows);
    }

    /**
     * @return list<list<string>>
     */
    private function overviewRows(GA4ReportsDigestData $digest): array
    {
        return [
            ['overview', 'total_users', (string) $digest->overview->totalUsers, '', '', '', ''],
            ['overview', 'sessions', (string) $digest->overview->sessions, '', '', '', ''],
            ['overview', 'screen_page_views', (string) $digest->overview->screenPageViews, '', '', '', ''],
            ['overview', 'conversions', (string) $digest->overview->conversions, '', '', '', ''],
            ['overview', 'event_count', (string) $digest->overview->eventCount, '', '', '', ''],
            ['overview', 'engagement_rate', (string) $digest->overview->engagementRate, '', '', '', ''],
            ['overview', 'average_session_duration', (string) $digest->overview->averageSessionDuration, '', '', '', ''],
        ];
    }

    /**
     * @return list<list<string>>
     */
    private function trendRows(GA4ReportsDigestData $digest): array
    {
        return array_map(
            static fn (GA4ReportsTrendPointData $point): array => [
                'trend',
                $point->label,
                (string) $point->screenPageViews,
                (string) $point->sessions,
                (string) $point->totalUsers,
                '',
                '',
            ],
            $digest->trend,
        );
    }

    /**
     * @return list<list<string>>
     */
    private function topPageRows(GA4ReportsDigestData $digest): array
    {
        return array_map(
            static fn (GA4ReportsTopPageData $page): array => [
                'top_page',
                $page->pagePath,
                (string) $page->screenPageViews,
                (string) $page->sessions,
                (string) $page->totalUsers,
                (string) $page->conversions,
                $page->pageTitle ?? '',
            ],
            $digest->topPages,
        );
    }

    /**
     * @param  list<list<string>>  $rows
     */
    private function toCsv(array $rows): string
    {
        $stream = fopen('php://temp', 'r+');

        throw_if($stream === false, RuntimeException::class, 'Unable to open temporary CSV stream.');

        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }

        rewind($stream);

        $csv = stream_get_contents($stream);
        fclose($stream);

        throw_if($csv === false, RuntimeException::class, 'Unable to read temporary CSV stream.');

        return $csv;
    }
}
