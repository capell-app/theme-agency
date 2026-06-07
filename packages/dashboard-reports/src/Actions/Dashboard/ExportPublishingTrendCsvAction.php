<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Actions\Dashboard;

use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsObject;
use RuntimeException;

final class ExportPublishingTrendCsvAction
{
    use AsObject;

    public function handle(CarbonImmutable $rangeStart, CarbonImmutable $rangeEnd): string
    {
        $data = BuildPublishingTrendAction::run($rangeStart, $rangeEnd);

        $rows = [
            [
                __('capell-dashboard-reports::dashboard.export_column_bucket'),
                __('capell-dashboard-reports::dashboard.export_column_published_pages'),
                __('capell-dashboard-reports::dashboard.export_column_scheduled_pages'),
            ],
        ];

        foreach ($data->points as $point) {
            $rows[] = [
                $point->label,
                (string) $point->publishedCount,
                (string) $point->scheduledCount,
            ];
        }

        $rows[] = [
            __('capell-dashboard-reports::dashboard.export_total_row'),
            (string) $data->totalPublished,
            (string) $data->totalScheduled,
        ];

        return $this->toCsv($rows);
    }

    /**
     * @param  list<list<string>>  $rows
     */
    private function toCsv(array $rows): string
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            throw new RuntimeException('Unable to open temporary CSV stream.');
        }

        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }

        rewind($stream);

        $csv = stream_get_contents($stream);
        fclose($stream);

        if ($csv === false) {
            throw new RuntimeException('Unable to read temporary CSV stream.');
        }

        return $csv;
    }
}
