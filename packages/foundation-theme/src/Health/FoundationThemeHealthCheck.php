<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\FoundationTheme\Providers\FoundationThemeServiceProvider;
use Illuminate\Support\Collection;

final class FoundationThemeHealthCheck implements ChecksExtensionHealth
{
    /**
     * Packages Foundation Theme cannot render without.
     *
     * @var list<string>
     */
    private const array REQUIRED_PACKAGES = [
        'capell-app/frontend',
        'capell-app/layout-builder',
    ];

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
            $check->themeStudioDefinitionCheck(),
            $check->requiredPackagesCheck(),
            $check->publishedAssetsCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * Asserts the Foundation Theme Studio definition is registered so pages can render.
     */
    public function themeStudioDefinitionCheck(): DoctorCheckResultData
    {
        $registered = $this->isThemeStudioDefinitionRegistered();

        return new DoctorCheckResultData(
            label: 'Foundation Theme Studio definition',
            passed: $registered,
            message: $registered
                ? 'The Foundation Theme Studio definition is registered and available for rendering.'
                : 'The Foundation Theme Studio definition is not registered; pages cannot render through Foundation Theme.',
            remediation: $registered
                ? null
                : 'Ensure FoundationThemeServiceProvider boots and registers the theme definition with the ThemeRegistry.',
        );
    }

    /**
     * Asserts every package Foundation Theme depends on is installed.
     */
    public function requiredPackagesCheck(): DoctorCheckResultData
    {
        $missingPackages = $this->missingRequiredPackages();

        return new DoctorCheckResultData(
            label: 'Foundation Theme required packages',
            passed: $missingPackages === [],
            message: $missingPackages === []
                ? 'The frontend and layout-builder packages Foundation Theme depends on are installed.'
                : 'Missing required packages: ' . implode(', ', $missingPackages) . '.',
            remediation: $missingPackages === []
                ? null
                : 'Install the missing Capell packages so Foundation Theme can register its assets and layout areas.',
        );
    }

    /**
     * Asserts the published frontend asset manifest exists at the public path.
     */
    public function publishedAssetsCheck(): DoctorCheckResultData
    {
        $manifestExists = $this->publishedAssetManifestExists();

        return new DoctorCheckResultData(
            label: 'Foundation Theme published assets',
            passed: $manifestExists,
            message: $manifestExists
                ? 'The Foundation Theme frontend asset manifest is published to the public path.'
                : 'The Foundation Theme frontend asset manifest is missing from the public path.',
            remediation: $manifestExists
                ? null
                : 'Run php artisan vendor:publish --tag=capell-foundation-theme-assets to publish the frontend build.',
        );
    }

    public function isThemeStudioDefinitionRegistered(): bool
    {
        if (! app()->bound(ThemeRegistry::class)) {
            return false;
        }

        return app(ThemeRegistry::class)->has(FoundationThemeServiceProvider::THEME_KEY);
    }

    /**
     * @return list<string>
     */
    public function missingRequiredPackages(): array
    {
        return collect(self::REQUIRED_PACKAGES)
            ->reject(static fn (string $packageName): bool => CapellCore::isPackageInstalled($packageName))
            ->values()
            ->all();
    }

    public function publishedAssetManifestExists(): bool
    {
        return is_file(public_path('vendor/capell-foundation-theme/manifest.json'));
    }
}
