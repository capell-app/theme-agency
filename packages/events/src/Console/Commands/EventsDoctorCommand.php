<?php

declare(strict_types=1);

namespace Capell\Events\Console\Commands;

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Data\Diagnostics\DoctorReportData;
use Capell\Events\Health\EventsHealthCheck;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class EventsDoctorCommand extends Command
{
    protected $signature = 'capell:events-doctor
        {--json : Output a machine-readable JSON health report}';

    protected $description = 'Run Events health checks.';

    public function handle(): int
    {
        $checks = EventsHealthCheck::runDiagnostics();
        $report = new DoctorReportData(
            status: $checks->every(fn (DoctorCheckResultData $check): bool => $check->passed) ? 'passed' : 'failed',
            checks: $checks->values(),
        );

        if ($this->option('json')) {
            $this->line(json_encode($report->toArray(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

            return $report->passed() ? CommandAlias::SUCCESS : CommandAlias::FAILURE;
        }

        $this->newLine();
        $this->line('<fg=blue;options=bold>' . __('capell-events::doctor.title') . '</>');
        $this->newLine();

        $report->checks->each(function (DoctorCheckResultData $check): void {
            $this->outputCheckResult($check);
        });

        $this->newLine();

        if ($report->passed()) {
            $this->info(__('capell-events::doctor.passed'));

            return CommandAlias::SUCCESS;
        }

        $this->error(__('capell-events::doctor.failed'));

        return CommandAlias::FAILURE;
    }

    private function outputCheckResult(DoctorCheckResultData $check): void
    {
        $icon = $check->passed ? '<fg=green>OK</>' : '<fg=red>FAIL</>';
        $message = $check->message;

        if (! $check->passed && $check->remediation !== null && $check->remediation !== '') {
            $message .= ' ' . $check->remediation;
        }

        $this->components->twoColumnDetail(
            sprintf('%s %s', $icon, $check->label),
            $check->passed ? '<fg=green>' . $message . '</>' : '<fg=red>' . $message . '</>',
        );
    }
}
