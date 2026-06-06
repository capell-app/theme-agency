<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Actions\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Diagnostics\Data\Health\ExtensionHealthReportData;
use Capell\Diagnostics\Data\Health\HealthCheckResultData;
use Capell\Diagnostics\Enums\HealthCheckImplementationStatus;
use Illuminate\Support\Facades\File;
use Lorisleiva\Actions\Concerns\AsAction;
use ReflectionClass;
use Spatie\LaravelData\DataCollection;
use Throwable;

/**
 * Resolves every health-check class declared in installed package manifests,
 * classifies each as implemented / stub / broken, and executes the runnable
 * ones to capture real pass/fail outcomes.
 *
 * This is the executor the package was missing: previously declared health
 * checks were only counted, never run, producing a false-green dashboard. The
 * action degrades gracefully — a check that throws, returns nothing, or only
 * satisfies the bare contract is reported honestly rather than crashing the run.
 *
 * @method static ExtensionHealthReportData run()
 */
final class RunExtensionHealthChecksAction
{
    use AsAction;

    /**
     * The static method a real health check exposes to return its assertions.
     */
    private const string DIAGNOSTICS_METHOD = 'runDiagnostics';

    public function __construct(
        private readonly ?string $customLocalPackagesPath = null,
    ) {}

    public function handle(): ExtensionHealthReportData
    {
        $results = [];

        foreach ($this->discoverDeclaredHealthChecks() as $declaration) {
            $results[] = $this->resolveAndRun($declaration);
        }

        return $this->summarize($results);
    }

    /**
     * @param  array{package: string, key: string, label: string, class: string, severity: string}  $declaration
     */
    private function resolveAndRun(array $declaration): HealthCheckResultData
    {
        $className = $declaration['class'];

        if ($className === '' || ! class_exists($className)) {
            return $this->brokenResult(
                $declaration,
                (string) __('capell-diagnostics::package.health_check_broken_missing_class'),
            );
        }

        if (! is_subclass_of($className, ChecksExtensionHealth::class)) {
            return $this->brokenResult(
                $declaration,
                (string) __('capell-diagnostics::package.health_check_broken_non_contract'),
            );
        }

        if (! $this->isRunnable($className)) {
            return new HealthCheckResultData(
                packageName: $declaration['package'],
                key: $declaration['key'],
                label: $declaration['label'],
                className: $className,
                severity: $declaration['severity'],
                implementationStatus: HealthCheckImplementationStatus::Stub,
                passed: null,
                message: (string) __('capell-diagnostics::package.health_check_stub_no_assertions'),
            );
        }

        return $this->executeRunnable($declaration, $className);
    }

    /**
     * @param  array{package: string, key: string, label: string, class: string, severity: string}  $declaration
     * @param  class-string  $className
     */
    private function executeRunnable(array $declaration, string $className): HealthCheckResultData
    {
        try {
            $diagnostics = $this->runDiagnosticsMethod($className, $declaration['key']);
            [$passed, $message] = $this->summarizeDiagnostics($diagnostics);

            return new HealthCheckResultData(
                packageName: $declaration['package'],
                key: $declaration['key'],
                label: $declaration['label'],
                className: $className,
                severity: $declaration['severity'],
                implementationStatus: HealthCheckImplementationStatus::Implemented,
                passed: $passed,
                message: $message,
            );
        } catch (Throwable $throwable) {
            return new HealthCheckResultData(
                packageName: $declaration['package'],
                key: $declaration['key'],
                label: $declaration['label'],
                className: $className,
                severity: $declaration['severity'],
                implementationStatus: HealthCheckImplementationStatus::Implemented,
                passed: false,
                message: (string) __('capell-diagnostics::package.health_check_threw', [
                    'message' => $throwable->getMessage(),
                ]),
            );
        }
    }

    /**
     * @param  class-string  $className
     * @return iterable<int, mixed>
     */
    private function runDiagnosticsMethod(string $className, string $key): iterable
    {
        $reflection = new ReflectionClass($className);
        $method = $reflection->getMethod(self::DIAGNOSTICS_METHOD);

        if ($method->getNumberOfParameters() > 0) {
            /** @var iterable<int, mixed> $diagnostics */
            $diagnostics = $className::{self::DIAGNOSTICS_METHOD}($key);

            return $diagnostics;
        }

        /** @var iterable<int, mixed> $diagnostics */
        $diagnostics = $className::{self::DIAGNOSTICS_METHOD}();

        return $diagnostics;
    }

    /**
     * Reduces a check's returned diagnostics to an overall pass flag and summary.
     *
     * Accepts the documented `Collection<DoctorCheckResultData>` shape but also
     * degrades gracefully when a minimal check returns an empty result, a plain
     * array, or arbitrary values.
     *
     * @param  iterable<int, mixed>  $diagnostics
     * @return array{0: bool, 1: string}
     */
    private function summarizeDiagnostics(iterable $diagnostics): array
    {
        $total = 0;
        $failures = [];

        foreach ($diagnostics as $diagnostic) {
            $total++;

            if (! $this->diagnosticPassed($diagnostic)) {
                $failures[] = $this->diagnosticLabel($diagnostic);
            }
        }

        if ($total === 0) {
            return [true, (string) __('capell-diagnostics::package.health_check_empty_assertions')];
        }

        if ($failures === []) {
            return [true, (string) __('capell-diagnostics::package.health_check_assertions_passed', [
                'total' => $total,
            ])];
        }

        return [
            false,
            (string) __('capell-diagnostics::package.health_check_assertions_failed', [
                'failed' => count($failures),
                'total' => $total,
                'failures' => implode(', ', $failures),
            ]),
        ];
    }

    private function diagnosticPassed(mixed $diagnostic): bool
    {
        if ($diagnostic instanceof DoctorCheckResultData) {
            return $diagnostic->passed;
        }

        if (is_array($diagnostic) && array_key_exists('passed', $diagnostic)) {
            return (bool) $diagnostic['passed'];
        }

        if (is_object($diagnostic) && property_exists($diagnostic, 'passed')) {
            return (bool) $diagnostic->passed;
        }

        return (bool) $diagnostic;
    }

    private function diagnosticLabel(mixed $diagnostic): string
    {
        if ($diagnostic instanceof DoctorCheckResultData) {
            return $diagnostic->label;
        }

        if (is_array($diagnostic) && is_string($diagnostic['label'] ?? null)) {
            return $diagnostic['label'];
        }

        if (is_object($diagnostic) && property_exists($diagnostic, 'label') && is_string($diagnostic->label)) {
            return $diagnostic->label;
        }

        return (string) __('capell-diagnostics::package.health_check_fallback_assertion_label');
    }

    /**
     * @param  class-string  $className
     */
    private function isRunnable(string $className): bool
    {
        $reflection = new ReflectionClass($className);

        if (! $reflection->hasMethod(self::DIAGNOSTICS_METHOD)) {
            return false;
        }

        $method = $reflection->getMethod(self::DIAGNOSTICS_METHOD);

        return $method->isPublic() && $method->isStatic();
    }

    /**
     * @param  array{package: string, key: string, label: string, class: string, severity: string}  $declaration
     */
    private function brokenResult(array $declaration, string $message): HealthCheckResultData
    {
        return new HealthCheckResultData(
            packageName: $declaration['package'],
            key: $declaration['key'],
            label: $declaration['label'],
            className: $declaration['class'],
            severity: $declaration['severity'],
            implementationStatus: HealthCheckImplementationStatus::Broken,
            passed: null,
            message: $message,
        );
    }

    /**
     * @param  list<HealthCheckResultData>  $results
     */
    private function summarize(array $results): ExtensionHealthReportData
    {
        $implemented = array_filter(
            $results,
            static fn (HealthCheckResultData $result): bool => $result->implementationStatus === HealthCheckImplementationStatus::Implemented,
        );
        $stubs = array_filter(
            $results,
            static fn (HealthCheckResultData $result): bool => $result->implementationStatus === HealthCheckImplementationStatus::Stub,
        );
        $broken = array_filter(
            $results,
            static fn (HealthCheckResultData $result): bool => $result->implementationStatus === HealthCheckImplementationStatus::Broken,
        );
        $executed = array_filter($results, static fn (HealthCheckResultData $result): bool => $result->wasExecuted());
        $failed = array_filter($results, static fn (HealthCheckResultData $result): bool => $result->failed());

        usort(
            $results,
            static fn (HealthCheckResultData $left, HealthCheckResultData $right): int => [$left->packageName, $left->key] <=> [$right->packageName, $right->key],
        );

        return new ExtensionHealthReportData(
            declaredCount: count($results),
            implementedCount: count($implemented),
            stubCount: count($stubs),
            brokenCount: count($broken),
            executedCount: count($executed),
            passedCount: count($executed) - count($failed),
            failedCount: count($failed),
            checks: HealthCheckResultData::collect($results, DataCollection::class),
            overallStatus: $this->overallStatus($results),
            healthScore: $this->healthScore($results),
            worstSeverity: $this->worstSeverity($results),
        );
    }

    /**
     * @param  list<HealthCheckResultData>  $results
     */
    private function overallStatus(array $results): string
    {
        $worstSeverity = $this->worstSeverity($results);

        if ($worstSeverity === 'critical') {
            return 'critical';
        }

        if ($worstSeverity !== null || $this->stubCount($results) > 0) {
            return 'degraded';
        }

        return 'healthy';
    }

    /**
     * @param  list<HealthCheckResultData>  $results
     */
    private function healthScore(array $results): int
    {
        $penalty = 0;

        foreach ($results as $result) {
            $severity = strtolower($result->severity);

            if ($result->implementationStatus === HealthCheckImplementationStatus::Broken) {
                $penalty += $severity === 'critical' ? 40 : 25;

                continue;
            }

            if ($result->failed()) {
                $penalty += $severity === 'critical' ? 30 : 20;

                continue;
            }

            if ($result->implementationStatus === HealthCheckImplementationStatus::Stub) {
                $penalty += 5;
            }
        }

        return max(0, 100 - $penalty);
    }

    /**
     * @param  list<HealthCheckResultData>  $results
     */
    private function worstSeverity(array $results): ?string
    {
        $worst = null;

        foreach ($results as $result) {
            if (! $result->failed() && $result->implementationStatus !== HealthCheckImplementationStatus::Broken) {
                continue;
            }

            $severity = strtolower($result->severity);

            if ($severity === 'critical') {
                return 'critical';
            }

            if ($worst === null && $severity !== '') {
                $worst = $severity;
            }
        }

        return $worst;
    }

    /**
     * @param  list<HealthCheckResultData>  $results
     */
    private function stubCount(array $results): int
    {
        return count(array_filter(
            $results,
            static fn (HealthCheckResultData $result): bool => $result->implementationStatus === HealthCheckImplementationStatus::Stub,
        ));
    }

    /**
     * Reads `healthChecks[]` from every installed Capell package manifest.
     *
     * @return list<array{package: string, key: string, label: string, class: string, severity: string}>
     */
    private function discoverDeclaredHealthChecks(): array
    {
        $declarations = [];

        foreach ($this->manifestPaths() as $manifestPath) {
            if (! File::exists($manifestPath)) {
                continue;
            }

            /** @var array<string, mixed>|null $manifest */
            $manifest = json_decode(File::get($manifestPath), true);

            if (! is_array($manifest)) {
                continue;
            }

            $packageName = is_string($manifest['name'] ?? null) ? $manifest['name'] : basename(dirname($manifestPath));
            $healthChecks = $manifest['healthChecks'] ?? null;

            if (! is_array($healthChecks)) {
                continue;
            }

            foreach ($healthChecks as $healthCheck) {
                if (! is_array($healthCheck)) {
                    continue;
                }

                $declarations[] = [
                    'package' => $packageName,
                    'key' => is_string($healthCheck['key'] ?? null) ? $healthCheck['key'] : '',
                    'label' => is_string($healthCheck['label'] ?? null) ? $healthCheck['label'] : '',
                    'class' => is_string($healthCheck['class'] ?? null) ? $healthCheck['class'] : '',
                    'severity' => is_string($healthCheck['severity'] ?? null) ? $healthCheck['severity'] : 'warning',
                ];
            }
        }

        return $declarations;
    }

    /**
     * @return list<string>
     */
    private function manifestPaths(): array
    {
        $packagesPath = $this->customLocalPackagesPath ?? base_path('packages');

        if (! File::isDirectory($packagesPath)) {
            return [];
        }

        $paths = [];

        foreach (File::directories($packagesPath) as $packagePath) {
            $paths[] = $packagePath . '/capell.json';
        }

        return $paths;
    }
}
