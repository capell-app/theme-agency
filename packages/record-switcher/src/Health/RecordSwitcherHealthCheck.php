<?php

declare(strict_types=1);

namespace Capell\RecordSwitcher\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\RecordSwitcher\Filament\RecordSwitcherHeadingExtender;
use Illuminate\Support\Collection;

final class RecordSwitcherHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $check = new self;

        return collect([
            $check->headingExtenderCheck(),
            $check->adminAssetsCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    public function headingExtenderCheck(): DoctorCheckResultData
    {
        $available = class_exists(RecordSwitcherHeadingExtender::class);

        return new DoctorCheckResultData(
            label: 'Record Switcher heading extender',
            passed: $available,
            message: $available
                ? 'The Record Switcher heading extender is available.'
                : 'The Record Switcher heading extender could not be loaded.',
            remediation: $available
                ? null
                : 'Ensure the record-switcher package autoloader and service provider are registered.',
        );
    }

    public function adminAssetsCheck(): DoctorCheckResultData
    {
        $missingAssets = $this->missingAssetPaths();

        return new DoctorCheckResultData(
            label: 'Record Switcher admin assets',
            passed: $missingAssets === [],
            message: $missingAssets === []
                ? 'The Record Switcher admin CSS and Alpine assets are present.'
                : 'Missing Record Switcher assets: ' . implode(', ', $missingAssets) . '.',
            remediation: $missingAssets === []
                ? null
                : 'Rebuild the record-switcher package assets before publishing the admin surface.',
        );
    }

    /**
     * @return list<string>
     */
    public function missingAssetPaths(): array
    {
        $paths = [
            'resources/css/components/record-switcher.css' => dirname(__DIR__, 2) . '/resources/css/components/record-switcher.css',
            'resources/dist/record-switcher.js' => dirname(__DIR__, 2) . '/resources/dist/record-switcher.js',
        ];

        return array_values(array_keys(array_filter(
            $paths,
            static fn (string $path): bool => ! is_file($path),
        )));
    }
}
