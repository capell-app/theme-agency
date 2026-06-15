<?php

declare(strict_types=1);

namespace Capell\RecordSwitcher\Health;

use Capell\Admin\Contracts\Extenders\EditRecordHeadingExtender;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\RecordSwitcher\Filament\RecordSwitcherHeadingExtender;
use Capell\RecordSwitcher\Livewire\RecordSwitcher;
use Capell\RecordSwitcher\Providers\RecordSwitcherServiceProvider;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Collection;
use Throwable;

final class RecordSwitcherHealthCheck implements ChecksExtensionHealth
{
    private const string LIVEWIRE_NAMESPACE = 'capell-record-switcher';

    private const string LIVEWIRE_CLASS_NAMESPACE = 'Capell\\RecordSwitcher\\Livewire';

    private const string ASSET_HANDLE = 'record-switcher';

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
            $check->headingExtenderRegistrationCheck(),
            $check->livewireNamespaceCheck(),
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

    public function headingExtenderRegistrationCheck(): DoctorCheckResultData
    {
        $registered = $this->headingExtenderIsTagged();

        return new DoctorCheckResultData(
            label: 'Record Switcher heading extender registration',
            passed: $registered,
            message: $registered
                ? 'The Record Switcher heading extender is tagged into Capell Admin edit pages.'
                : 'The Record Switcher heading extender is not tagged into Capell Admin edit pages.',
            remediation: $registered
                ? null
                : 'Ensure the Record Switcher service provider boots and tags RecordSwitcherHeadingExtender with EditRecordHeadingExtender::TAG.',
        );
    }

    public function livewireNamespaceCheck(): DoctorCheckResultData
    {
        $registered = $this->livewireNamespaceIsRegistered();

        return new DoctorCheckResultData(
            label: 'Record Switcher Livewire namespace',
            passed: $registered,
            message: $registered
                ? 'The Record Switcher Livewire namespace resolves the heading switcher component.'
                : 'The Record Switcher Livewire namespace could not resolve the heading switcher component.',
            remediation: $registered
                ? null
                : 'Ensure the Record Switcher service provider calls Livewire::addNamespace() for capell-record-switcher.',
        );
    }

    public function adminAssetsCheck(): DoctorCheckResultData
    {
        $missingAssets = $this->missingAssetPaths();
        $missingHandles = $this->missingRegisteredAssetHandles();

        $problems = [
            ...array_map(
                static fn (string $path): string => "missing file {$path}",
                $missingAssets,
            ),
            ...array_map(
                static fn (string $handle): string => "missing registered handle {$handle}",
                $missingHandles,
            ),
        ];

        return new DoctorCheckResultData(
            label: 'Record Switcher admin assets',
            passed: $problems === [],
            message: $problems === []
                ? 'The Record Switcher admin CSS and Alpine assets are present and registered.'
                : 'Record Switcher asset issues: ' . implode(', ', $problems) . '.',
            remediation: $problems === []
                ? null
                : 'Rebuild the record-switcher package assets and ensure the service provider registers Filament asset handles.',
        );
    }

    public function headingExtenderIsTagged(): bool
    {
        return collect(app()->tagged(EditRecordHeadingExtender::TAG))
            ->contains(static fn (mixed $extender): bool => $extender instanceof RecordSwitcherHeadingExtender);
    }

    public function livewireNamespaceIsRegistered(): bool
    {
        if (! class_exists(RecordSwitcher::class)) {
            return false;
        }

        if (! app()->bound('livewire.finder')) {
            return false;
        }

        $finder = app('livewire.finder');

        if (! method_exists($finder, 'getClassNamespace')) {
            return false;
        }

        $namespace = $finder->getClassNamespace(self::LIVEWIRE_NAMESPACE);

        return ($namespace['classNamespace'] ?? null) === self::LIVEWIRE_CLASS_NAMESPACE
            && $finder->resolveClassComponentClassName(self::LIVEWIRE_NAMESPACE . '::record-switcher') === RecordSwitcher::class;
    }

    /**
     * @param  array<string, string>|null  $paths
     * @return list<string>
     */
    public function missingAssetPaths(?array $paths = null): array
    {
        $paths ??= [
            'resources/css/components/record-switcher.css' => dirname(__DIR__, 2) . '/resources/css/components/record-switcher.css',
            'resources/dist/record-switcher.js' => dirname(__DIR__, 2) . '/resources/dist/record-switcher.js',
        ];

        return array_keys(array_filter(
            $paths,
            static fn (string $path): bool => ! is_file($path),
        ));
    }

    /**
     * @return list<string>
     */
    public function missingRegisteredAssetHandles(): array
    {
        try {
            $styleIds = collect(FilamentAsset::getStyles([RecordSwitcherServiceProvider::$name]))
                ->map(static fn (Css $asset): string => $asset->getId());

            $alpineComponentIds = collect(FilamentAsset::getAlpineComponents([RecordSwitcherServiceProvider::$name]))
                ->map(static fn (AlpineComponent $asset): string => $asset->getId());
        } catch (Throwable) {
            return [
                'css:' . self::ASSET_HANDLE,
                'alpine:' . self::ASSET_HANDLE,
            ];
        }

        $handles = [
            'css:' . self::ASSET_HANDLE => $styleIds->contains(self::ASSET_HANDLE),
            'alpine:' . self::ASSET_HANDLE => $alpineComponentIds->contains(self::ASSET_HANDLE),
        ];

        return array_keys(array_filter(
            $handles,
            static fn (bool $registered): bool => ! $registered,
        ));
    }
}
