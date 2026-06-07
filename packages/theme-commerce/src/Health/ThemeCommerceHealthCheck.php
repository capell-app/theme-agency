<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Commerce\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Commerce\CommerceThemeServiceProvider;
use Illuminate\Support\Collection;

final class ThemeCommerceHealthCheck implements ChecksExtensionHealth
{
    /**
     * @var list<string>
     */
    private const array REQUIRED_VIEW_NAMES = [
        'capell-theme-commerce::page',
        'capell-theme-commerce::sections.navigation',
        'capell-theme-commerce::sections.hero',
        'capell-theme-commerce::sections.product-grid',
        'capell-theme-commerce::sections.product-detail',
        'capell-theme-commerce::sections.mini-basket',
        'capell-theme-commerce::sections.collections',
        'capell-theme-commerce::sections.product-finder',
        'capell-theme-commerce::sections.comparison',
        'capell-theme-commerce::sections.catalog',
        'capell-theme-commerce::sections.lookbook',
        'capell-theme-commerce::sections.promotion',
        'capell-theme-commerce::sections.buying-guide',
        'capell-theme-commerce::sections.proof',
        'capell-theme-commerce::sections.blog-teaser',
        'capell-theme-commerce::sections.cta',
        'capell-theme-commerce::sections.footer',
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
            label: 'Theme Commerce Studio definition',
            passed: $registered,
            message: $registered
                ? 'The commerce theme definition is registered and available for rendering.'
                : 'The commerce theme definition is not registered; commerce pages cannot render through Theme Studio.',
            remediation: $registered
                ? null
                : 'Ensure CommerceThemeServiceProvider boots with capell-app/theme-commerce installed.',
        );
    }

    public function themeViewsCheck(): DoctorCheckResultData
    {
        $missingViews = $this->missingViews();

        return new DoctorCheckResultData(
            label: 'Theme Commerce render views',
            passed: $missingViews === [],
            message: $missingViews === []
                ? 'The commerce layout and section views are resolvable.'
                : 'Missing commerce views: ' . implode(', ', $missingViews) . '.',
            remediation: $missingViews === []
                ? null
                : 'Ensure the package view namespace is loaded and the commerce section Blade files are present.',
        );
    }

    public function vendorAssetsCheck(): DoctorCheckResultData
    {
        $missingAssets = $this->missingVendorAssets();

        return new DoctorCheckResultData(
            label: 'Theme Commerce vendor assets',
            passed: $missingAssets === [],
            message: $missingAssets === []
                ? 'The commerce Tailwind import and Blade source assets are registered.'
                : 'Missing commerce assets: ' . implode(', ', $missingAssets) . '.',
            remediation: $missingAssets === []
                ? null
                : 'Ensure CommerceThemeServiceProvider registers the theme CSS import and Blade source path.',
        );
    }

    public function isThemeStudioDefinitionRegistered(): bool
    {
        if (! app()->bound(ThemeRegistry::class)) {
            return false;
        }

        return resolve(ThemeRegistry::class)->has(CommerceThemeServiceProvider::THEME_KEY);
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

        if (! is_file(dirname(__DIR__, 2) . '/resources/css/theme-commerce.css')) {
            $missingAssets[] = 'resources/css/theme-commerce.css';
        }

        $registeredImports = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindImport);
        $registeredSources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource);

        $hasTailwindImport = $registeredImports->contains(
            static fn (mixed $asset): bool => $asset->packageName === CommerceThemeServiceProvider::$packageName
                && $asset->value === 'resources/css/theme-commerce.css',
        );

        $hasTailwindSource = $registeredSources->contains(
            static fn (mixed $asset): bool => $asset->packageName === CommerceThemeServiceProvider::$packageName
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
