<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Actions\Dashboard;

use Capell\Admin\Data\Dashboard\ContentHealthIssueData;
use Lorisleiva\Actions\Concerns\AsObject;
use RuntimeException;

final class ExportContentHealthCsvAction
{
    use AsObject;

    public function handle(int $staleDays = BuildDefaultContentHealthAction::DEFAULT_STALE_DAYS): string
    {
        $data = BuildDefaultContentHealthAction::run($staleDays);

        $rows = [
            [
                __('capell-dashboard-reports::dashboard.export_column_issue_id'),
                __('capell-dashboard-reports::dashboard.export_column_label'),
                __('capell-dashboard-reports::dashboard.export_column_count'),
                __('capell-dashboard-reports::dashboard.export_column_filter_url'),
            ],
        ];

        foreach ($data->issues as $issue) {
            if (! $issue instanceof ContentHealthIssueData) {
                continue;
            }

            $rows[] = [
                $issue->id,
                $issue->label,
                (string) $issue->count,
                $issue->filterUrl ?? '',
            ];
        }

        return $this->toCsv($rows);
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
