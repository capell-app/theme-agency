<?php

declare(strict_types=1);

namespace Capell\AccessGate\Console\Commands;

use Capell\AccessGate\Support\AccessGateDiagnosticsService;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Data\Diagnostics\DoctorReportData;
use Illuminate\Console\Command;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class AccessGateDoctorCommand extends Command
{
    protected $signature = 'capell:access-gate-doctor
        {--json : Output a machine-readable JSON health report}';

    protected $description = 'Check Access Gate configuration and safety requirements.';

    public function handle(Router $router, AccessGateDiagnosticsService $diagnostics): int
    {
        $checks = $diagnostics->runAllChecks($router);

        $report = new DoctorReportData(
            status: $checks->every(fn (DoctorCheckResultData $check): bool => $check->passed) ? 'passed' : 'failed',
            checks: $checks->values(),
        );

        if ($this->option('json')) {
            $this->line(json_encode($report->toArray(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

            return $report->passed() ? CommandAlias::SUCCESS : CommandAlias::FAILURE;
        }

        $this->outputChecks($checks);

        if (! $report->passed()) {
            $this->error(__('capell-access-gate::doctor.failed', ['count' => $checks->where('passed', false)->count()]));

            return CommandAlias::FAILURE;
        }

        $this->info(__('capell-access-gate::doctor.passed'));

        return CommandAlias::SUCCESS;
    }

    /**
     * @param  Collection<int, DoctorCheckResultData>  $checks
     */
    private function outputChecks(Collection $checks): void
    {
        $checks->each(function (DoctorCheckResultData $check): void {
            if ($check->passed) {
                $this->info($check->message);

                return;
            }

            $this->error($check->message);

            if ($check->remediation !== null && $check->remediation !== '') {
                $this->line($check->remediation);
            }
        });
    }
}
