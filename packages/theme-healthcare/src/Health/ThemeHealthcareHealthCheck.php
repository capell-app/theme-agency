<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Healthcare\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Healthcare\HealthcareThemeServiceProvider;
use Illuminate\Support\Collection;

final class ThemeHealthcareHealthCheck implements ChecksExtensionHealth
{
    /**
     * @var list<string>
     */
    private const array REQUIRED_VIEW_NAMES = [
        'capell-theme-healthcare::page',
        'capell-theme-healthcare::sections.utility-bar',
        'capell-theme-healthcare::sections.navigation',
        'capell-theme-healthcare::sections.hero',
        'capell-theme-healthcare::sections.services',
        'capell-theme-healthcare::sections.blog-teaser',
        'capell-theme-healthcare::sections.service-finder',
        'capell-theme-healthcare::sections.care-pathway',
        'capell-theme-healthcare::sections.clinicians',
        'capell-theme-healthcare::sections.booking',
        'capell-theme-healthcare::sections.locations',
        'capell-theme-healthcare::sections.insurance-trust',
        'capell-theme-healthcare::sections.events',
        'capell-theme-healthcare::sections.comparison',
        'capell-theme-healthcare::sections.proof',
        'capell-theme-healthcare::sections.contact',
        'capell-theme-healthcare::sections.cta',
        'capell-theme-healthcare::sections.footer',
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
            $check->themeViewsCheck(),
            $check->vendorAssetsCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    public function themeStudioDefinitionCheck(): DoctorCheckResultData
    {
        $registered = $this->isThemeStudioDefinitionRegistered();

        return new DoctorCheckResultData(
            label: 'Theme Healthcare Studio definition',
            passed: $registered,
            message: $registered
                ? 'The healthcare theme definition is registered and available for rendering.'
                : 'The healthcare theme definition is not registered; healthcare pages cannot render through Theme Studio.',
            remediation: $registered
                ? null
                : 'Ensure HealthcareThemeServiceProvider boots with capell-app/theme-healthcare installed.',
        );
    }

    public function themeViewsCheck(): DoctorCheckResultData
    {
        $missingViews = $this->missingViews();

        return new DoctorCheckResultData(
            label: 'Theme Healthcare render views',
            passed: $missingViews === [],
            message: $missingViews === []
                ? 'The healthcare layout and section views are resolvable.'
                : 'Missing healthcare views: ' . implode(', ', $missingViews) . '.',
            remediation: $missingViews === []
                ? null
                : 'Ensure the package view namespace is loaded and the healthcare section Blade files are present.',
        );
    }

    public function vendorAssetsCheck(): DoctorCheckResultData
    {
        $missingAssets = $this->missingVendorAssets();

        return new DoctorCheckResultData(
            label: 'Theme Healthcare vendor assets',
            passed: $missingAssets === [],
            message: $missingAssets === []
                ? 'The healthcare Tailwind import and Blade source assets are registered.'
                : 'Missing healthcare assets: ' . implode(', ', $missingAssets) . '.',
            remediation: $missingAssets === []
                ? null
                : 'Ensure HealthcareThemeServiceProvider registers the theme CSS import and Blade source path.',
        );
    }

    public function isThemeStudioDefinitionRegistered(): bool
    {
        if (! app()->bound(ThemeRegistry::class)) {
            return false;
        }

        return resolve(ThemeRegistry::class)->has(HealthcareThemeServiceProvider::THEME_KEY);
    }

    /**
     * @return list<string>
     */
    public function missingViews(): array
    {
        if (! function_exists('view')) {
            return self::REQUIRED_VIEW_NAMES;
        }

        return array_values(collect(self::REQUIRED_VIEW_NAMES)
            ->reject(static fn (string $viewName): bool => view()->exists($viewName))
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    public function missingVendorAssets(): array
    {
        $missingAssets = [];

        if (! is_file(dirname(__DIR__, 2) . '/resources/css/theme-healthcare.css')) {
            $missingAssets[] = 'resources/css/theme-healthcare.css';
        }

        $registeredImports = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindImport);
        $registeredSources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource);

        $hasTailwindImport = $registeredImports->contains(
            static fn (mixed $asset): bool => $asset->packageName === HealthcareThemeServiceProvider::$packageName
                && $asset->value === 'resources/css/theme-healthcare.css',
        );

        $hasTailwindSource = $registeredSources->contains(
            static fn (mixed $asset): bool => $asset->packageName === HealthcareThemeServiceProvider::$packageName
                && $asset->value === 'resources/views/**/*.blade.php',
        );

        if (! $hasTailwindImport) {
            $missingAssets[] = 'Tailwind import vendor asset';
        }

        if (! $hasTailwindSource) {
            $missingAssets[] = 'Blade source vendor asset';
        }

        return $missingAssets;
    }
}
