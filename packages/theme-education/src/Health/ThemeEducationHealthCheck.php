<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Education\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Education\EducationThemeServiceProvider;
use Illuminate\Support\Collection;

final class ThemeEducationHealthCheck implements ChecksExtensionHealth
{
    /**
     * @var list<string>
     */
    private const array REQUIRED_VIEW_NAMES = [
        'capell-theme-education::page',
        'capell-theme-education::sections.navigation',
        'capell-theme-education::sections.hero',
        'capell-theme-education::sections.features',
        'capell-theme-education::sections.proof',
        'capell-theme-education::sections.content-listing',
        'capell-theme-education::sections.course-catalog',
        'capell-theme-education::sections.pathway-comparison',
        'capell-theme-education::sections.outcomes',
        'capell-theme-education::sections.instructors',
        'capell-theme-education::sections.events',
        'capell-theme-education::sections.admissions-checklist',
        'capell-theme-education::sections.enrolment-cta',
        'capell-theme-education::sections.resources',
        'capell-theme-education::sections.faq',
        'capell-theme-education::sections.cta',
        'capell-theme-education::sections.footer',
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
            label: 'Theme Education Studio definition',
            passed: $registered,
            message: $registered
                ? 'The education theme definition is registered and available for rendering.'
                : 'The education theme definition is not registered; education pages cannot render through Theme Studio.',
            remediation: $registered
                ? null
                : 'Ensure EducationThemeServiceProvider boots with capell-app/theme-education installed.',
        );
    }

    public function themeViewsCheck(): DoctorCheckResultData
    {
        $missingViews = $this->missingViews();

        return new DoctorCheckResultData(
            label: 'Theme Education render views',
            passed: $missingViews === [],
            message: $missingViews === []
                ? 'The education layout and section views are resolvable.'
                : 'Missing education views: ' . implode(', ', $missingViews) . '.',
            remediation: $missingViews === []
                ? null
                : 'Ensure the package view namespace is loaded and the education section Blade files are present.',
        );
    }

    public function vendorAssetsCheck(): DoctorCheckResultData
    {
        $missingAssets = $this->missingVendorAssets();

        return new DoctorCheckResultData(
            label: 'Theme Education vendor assets',
            passed: $missingAssets === [],
            message: $missingAssets === []
                ? 'The education Tailwind import and Blade source assets are registered.'
                : 'Missing education assets: ' . implode(', ', $missingAssets) . '.',
            remediation: $missingAssets === []
                ? null
                : 'Ensure EducationThemeServiceProvider registers the theme CSS import and Blade source path.',
        );
    }

    public function isThemeStudioDefinitionRegistered(): bool
    {
        if (! app()->bound(ThemeRegistry::class)) {
            return false;
        }

        return resolve(ThemeRegistry::class)->has(EducationThemeServiceProvider::THEME_KEY);
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

        if (! is_file(dirname(__DIR__, 2) . '/' . EducationThemeServiceProvider::TAILWIND_IMPORT)) {
            $missingAssets[] = EducationThemeServiceProvider::TAILWIND_IMPORT;
        }

        $registeredImports = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindImport);
        $registeredSources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource);

        $hasTailwindImport = $registeredImports->contains(
            static fn (mixed $asset): bool => $asset->packageName === EducationThemeServiceProvider::$packageName
                && $asset->value === EducationThemeServiceProvider::TAILWIND_IMPORT,
        );

        $hasTailwindSource = $registeredSources->contains(
            static fn (mixed $asset): bool => $asset->packageName === EducationThemeServiceProvider::$packageName
                && $asset->value === EducationThemeServiceProvider::TAILWIND_SOURCE,
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
