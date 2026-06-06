<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Console\Commands;

use Capell\DashboardReports\Actions\Dashboard\BuildDefaultContentHealthAction;
use Capell\DashboardReports\Actions\Dashboard\ExportContentHealthCsvAction;
use Capell\DashboardReports\Actions\Dashboard\ExportPublishingTrendCsvAction;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

final class ExportDashboardReportCommand extends Command
{
    protected $signature = 'capell:dashboard-reports:export
        {report : Report to export: content-health or publishing-trend.}
        {--path= : Optional file path to write instead of printing CSV to stdout.}
        {--from= : Publishing trend range start date/time.}
        {--to= : Publishing trend range end date/time.}
        {--stale-days= : Content health stale-page threshold override.}';

    protected $description = 'Export Dashboard Reports widget data as CSV.';

    public function handle(): int
    {
        $report = (string) $this->argument('report');

        if (! in_array($report, ['content-health', 'publishing-trend'], true)) {
            $this->components->error(__('capell-dashboard-reports::dashboard.export_invalid_report'));

            return self::INVALID;
        }

        if ($report === 'content-health') {
            $csv = ExportContentHealthCsvAction::run($this->staleDays());
        } else {
            $rangeStart = $this->rangeStart();
            $rangeEnd = $this->rangeEnd();

            if ($rangeStart === null || $rangeEnd === null) {
                return self::INVALID;
            }

            if ($rangeStart->greaterThanOrEqualTo($rangeEnd)) {
                $this->components->error(__('capell-dashboard-reports::dashboard.export_invalid_range'));

                return self::INVALID;
            }

            $csv = ExportPublishingTrendCsvAction::run($rangeStart, $rangeEnd);
        }

        $pathOption = $this->option('path');
        $path = is_string($pathOption) ? trim($pathOption) : '';

        if ($path === '') {
            $this->output->write($csv);

            return self::SUCCESS;
        }

        File::ensureDirectoryExists((string) dirname($path));
        File::put($path, $csv);

        $this->components->info(__('capell-dashboard-reports::dashboard.export_written', [
            'path' => $path,
        ]));

        return self::SUCCESS;
    }

    private function staleDays(): int
    {
        $option = $this->option('stale-days');

        if (is_numeric($option)) {
            return max(1, (int) $option);
        }

        return BuildDefaultContentHealthAction::DEFAULT_STALE_DAYS;
    }

    private function rangeStart(): ?CarbonImmutable
    {
        $option = $this->option('from');

        if (is_string($option) && trim($option) !== '') {
            return $this->parseDateOption($option, 'from');
        }

        return now()->toImmutable()->subDays(30)->startOfDay();
    }

    private function rangeEnd(): ?CarbonImmutable
    {
        $option = $this->option('to');

        if (is_string($option) && trim($option) !== '') {
            return $this->parseDateOption($option, 'to');
        }

        return now()->toImmutable()->endOfDay();
    }

    private function parseDateOption(string $value, string $name): ?CarbonImmutable
    {
        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            $this->components->error(__('capell-dashboard-reports::dashboard.export_invalid_date', [
                'option' => $name,
            ]));

            return null;
        }
    }
}
