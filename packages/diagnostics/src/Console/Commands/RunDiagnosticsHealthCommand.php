<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Console\Commands;

use Capell\Diagnostics\Actions\Health\BuildExtensionHealthTrendAction;
use Capell\Diagnostics\Actions\Health\ExportExtensionHealthReportCsvAction;
use Capell\Diagnostics\Actions\Health\RecordExtensionHealthReportAction;
use Capell\Diagnostics\Actions\Health\RunExtensionHealthChecksAction;
use Capell\Diagnostics\Data\Health\ExtensionHealthReportData;
use Capell\Diagnostics\Data\Health\ExtensionHealthTrendData;
use Capell\Diagnostics\Data\Health\HealthCheckResultData;
use Illuminate\Console\Command;
use Override;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

final class RunDiagnosticsHealthCommand extends Command
{
    protected $signature = 'capell:diagnostics:health
        {--json : Output health-check data as JSON}
        {--csv : Output health-check data as CSV}
        {--strict : Fail when declared health checks are stubbed}';

    protected $description = 'Run Diagnostics extension health checks.';

    #[Override]
    public function getDescription(): string
    {
        return (string) __('capell-diagnostics::package.health_command_description');
    }

    public function handle(): int
    {
        if ((bool) $this->option('json') && (bool) $this->option('csv')) {
            $this->components->error((string) __('capell-diagnostics::package.health_command_single_export_format'));

            return SymfonyCommand::FAILURE;
        }

        $report = RunExtensionHealthChecksAction::run();
        $trend = BuildExtensionHealthTrendAction::run($report);
        RecordExtensionHealthReportAction::run($report);

        if ((bool) $this->option('json')) {
            $this->output->writeln(json_encode($this->payloadFor($report, $trend), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

            return $this->exitCodeFor($report, strict: (bool) $this->option('strict'));
        }

        if ((bool) $this->option('csv')) {
            $this->output->writeln(ExportExtensionHealthReportCsvAction::run($report));

            return $this->exitCodeFor($report, strict: (bool) $this->option('strict'));
        }

        $this->components->info((string) __('capell-diagnostics::package.health_command_summary', [
            'status' => $report->overallStatus,
            'score' => $report->healthScore,
            'implemented' => $report->implementedCount,
            'declared' => $report->declaredCount,
            'stub' => $report->stubCount,
            'broken' => $report->brokenCount,
            'failed' => $report->failedCount,
        ]));

        if ($trend->previousScore !== null) {
            $this->components->info((string) __('capell-diagnostics::package.health_command_trend', [
                'previousStatus' => $trend->previousStatus,
                'previousScore' => $trend->previousScore,
                'delta' => $trend->scoreDelta,
            ]));
        }

        if ($report->checks->count() > 0) {
            $this->table([
                (string) __('capell-diagnostics::package.health_command_column_package'),
                (string) __('capell-diagnostics::package.health_command_column_key'),
                (string) __('capell-diagnostics::package.health_command_column_status'),
                (string) __('capell-diagnostics::package.health_command_column_result'),
            ], $this->rowsFor($report));
        }

        return $this->exitCodeFor($report, strict: (bool) $this->option('strict'));
    }

    /**
     * @return array{status: string, score: int, worstSeverity: string|null, previousStatus: string|null, previousScore: int|null, scoreDelta: int|null, previousRecordedAt: string|null, declared: int, implemented: int, stub: int, broken: int, executed: int, passed: int, failed: int, checks: list<array{package: string, key: string, label: string, class: string, severity: string, implementation: string, passed: bool|null, message: string|null}>}
     */
    private function payloadFor(ExtensionHealthReportData $report, ExtensionHealthTrendData $trend): array
    {
        /** @var list<array{package: string, key: string, label: string, class: string, severity: string, implementation: string, passed: bool|null, message: string|null}> $checks */
        $checks = $report->checks
            ->toCollection()
            ->map(fn (HealthCheckResultData $check): array => [
                'package' => $check->packageName,
                'key' => $check->key,
                'label' => $check->label,
                'class' => $check->className,
                'severity' => $check->severity,
                'implementation' => $check->implementationStatus->value,
                'passed' => $check->passed,
                'message' => $check->message,
            ])
            ->values()
            ->all();

        return [
            'status' => $report->overallStatus,
            'score' => $report->healthScore,
            'worstSeverity' => $report->worstSeverity,
            'previousStatus' => $trend->previousStatus,
            'previousScore' => $trend->previousScore,
            'scoreDelta' => $trend->scoreDelta,
            'previousRecordedAt' => $trend->recordedAt,
            'declared' => $report->declaredCount,
            'implemented' => $report->implementedCount,
            'stub' => $report->stubCount,
            'broken' => $report->brokenCount,
            'executed' => $report->executedCount,
            'passed' => $report->passedCount,
            'failed' => $report->failedCount,
            'checks' => $checks,
        ];
    }

    /**
     * @return list<array{package: string, key: string, status: string, result: string}>
     */
    private function rowsFor(ExtensionHealthReportData $report): array
    {
        /** @var list<array{package: string, key: string, status: string, result: string}> $rows */
        $rows = $report->checks
            ->toCollection()
            ->map(fn (HealthCheckResultData $check): array => [
                'package' => $check->packageName,
                'key' => $check->key,
                'status' => $check->implementationStatus->value,
                'result' => $this->resultFor($check),
            ])
            ->values()
            ->all();

        return $rows;
    }

    private function resultFor(HealthCheckResultData $check): string
    {
        if ($check->passed === true) {
            return (string) __('capell-diagnostics::package.health_command_result_passed');
        }

        if ($check->passed === false) {
            return (string) __('capell-diagnostics::package.health_command_result_failed');
        }

        return (string) __('capell-diagnostics::package.health_command_result_not_run');
    }

    private function exitCodeFor(ExtensionHealthReportData $report, bool $strict): int
    {
        return $report->failedCount > 0 || $report->brokenCount > 0 || ($strict && $report->stubCount > 0)
            ? SymfonyCommand::FAILURE
            : SymfonyCommand::SUCCESS;
    }
}
