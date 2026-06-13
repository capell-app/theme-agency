<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Actions\DashboardReports;

use Capell\Diagnostics\Data\InfrastructureStatusData;
use Capell\Diagnostics\Support\DiagnosticsSnapshotCache;
use Illuminate\Support\Facades\File;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array<int, InfrastructureStatusData> run(?string $packagesPath = null)
 */
final class BuildPublicOutputSafetyReportAction
{
    use AsAction;

    /**
     * @return array<int, InfrastructureStatusData>
     */
    public function handle(?string $packagesPath = null): array
    {
        $packagesPath ??= base_path('packages');

        return DiagnosticsSnapshotCache::rememberDataList(
            'public-output-safety:' . hash('sha256', $packagesPath),
            InfrastructureStatusData::class,
            fn (): array => $this->build($packagesPath),
        );
    }

    /**
     * @return array<int, InfrastructureStatusData>
     */
    private function build(string $packagesPath): array
    {
        if (! File::isDirectory($packagesPath)) {
            return [
                $this->status(
                    key: 'public-output.packages-path',
                    status: 'warning',
                    detail: (string) __('capell-diagnostics::package.public_output_safety_missing_packages_path'),
                ),
            ];
        }

        $reports = [];

        foreach (File::directories($packagesPath) as $packagePath) {
            $manifestPath = $packagePath . '/capell.json';

            if (! File::exists($manifestPath)) {
                continue;
            }

            $manifest = json_decode(File::get($manifestPath), true);

            if (! is_array($manifest)) {
                $reports[] = $this->status(
                    key: 'public-output.' . basename((string) $packagePath),
                    status: 'error',
                    detail: (string) __('capell-diagnostics::package.public_output_safety_invalid_manifest', [
                        'package' => basename((string) $packagePath),
                    ]),
                );

                continue;
            }

            if (! in_array('frontend', $manifest['surfaces'] ?? [], true)) {
                continue;
            }

            $reports[] = $this->reportForManifest($manifest, basename((string) $packagePath));
        }

        if ($reports === []) {
            return [
                $this->status(
                    key: 'public-output.none',
                    status: 'ok',
                    detail: (string) __('capell-diagnostics::package.public_output_safety_no_frontend_packages'),
                ),
            ];
        }

        return $reports;
    }

    /**
     * @param  array<string, mixed>  $manifest
     */
    private function reportForManifest(array $manifest, string $fallbackPackage): InfrastructureStatusData
    {
        $composerName = is_string($manifest['name'] ?? null) ? $manifest['name'] : $fallbackPackage;
        $cacheSafety = $manifest['performance']['cacheSafety'] ?? null;

        if (! is_array($cacheSafety)) {
            return $this->status(
                key: 'public-output.' . $fallbackPackage,
                status: 'error',
                detail: (string) __('capell-diagnostics::package.public_output_safety_missing_cache_safety', [
                    'package' => $composerName,
                ]),
            );
        }

        if (($cacheSafety['sensitiveOutput'] ?? false) === true) {
            return $this->status(
                key: 'public-output.' . $fallbackPackage,
                status: 'error',
                detail: (string) __('capell-diagnostics::package.public_output_safety_sensitive_output', [
                    'package' => $composerName,
                ]),
            );
        }

        $variesBy = $cacheSafety['variesBy'] ?? [];

        if (($cacheSafety['cacheable'] ?? false) === true && (! is_array($variesBy) || $variesBy === [])) {
            return $this->status(
                key: 'public-output.' . $fallbackPackage,
                status: 'warning',
                detail: (string) __('capell-diagnostics::package.public_output_safety_cacheable_without_vary', [
                    'package' => $composerName,
                ]),
            );
        }

        return $this->status(
            key: 'public-output.' . $fallbackPackage,
            status: 'ok',
            detail: (string) __('capell-diagnostics::package.public_output_safety_ok', [
                'package' => $composerName,
            ]),
        );
    }

    private function status(string $key, string $status, string $detail): InfrastructureStatusData
    {
        return new InfrastructureStatusData(
            key: $key,
            label: (string) __('capell-diagnostics::package.public_output_safety'),
            status: $status,
            detail: $detail,
        );
    }
}
