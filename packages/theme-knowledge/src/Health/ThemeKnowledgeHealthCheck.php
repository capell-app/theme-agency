<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Knowledge\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Knowledge\KnowledgeThemeServiceProvider;
use Illuminate\Support\Collection;

final class ThemeKnowledgeHealthCheck implements ChecksExtensionHealth
{
    /**
     * @var list<string>
     */
    private const array REQUIRED_VIEW_NAMES = [
        'capell-theme-knowledge::page',
        'capell-theme-knowledge::sections.navigation',
        'capell-theme-knowledge::sections.hero',
        'capell-theme-knowledge::sections.features',
        'capell-theme-knowledge::sections.proof',
        'capell-theme-knowledge::sections.doc-article',
        'capell-theme-knowledge::sections.content-listing',
        'capell-theme-knowledge::sections.topic-hubs',
        'capell-theme-knowledge::sections.topic-index',
        'capell-theme-knowledge::sections.reading-path',
        'capell-theme-knowledge::sections.source-map',
        'capell-theme-knowledge::sections.featured-content',
        'capell-theme-knowledge::sections.resource-library',
        'capell-theme-knowledge::sections.search-listing',
        'capell-theme-knowledge::sections.newsletter',
        'capell-theme-knowledge::sections.authors',
        'capell-theme-knowledge::sections.cta',
        'capell-theme-knowledge::sections.footer',
        'capell-theme-knowledge::knowledge-base.index',
        'capell-theme-knowledge::knowledge-base.article',
        'capell-theme-knowledge::knowledge-base.partials.collection-card',
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
            label: 'Theme Knowledge Studio definition',
            passed: $registered,
            message: $registered
                ? 'The knowledge theme definition is registered and available for rendering.'
                : 'The knowledge theme definition is not registered; knowledge pages cannot render through Theme Studio.',
            remediation: $registered
                ? null
                : 'Ensure KnowledgeThemeServiceProvider boots with capell-app/theme-knowledge installed.',
        );
    }

    public function themeViewsCheck(): DoctorCheckResultData
    {
        $missingViews = $this->missingViews();

        return new DoctorCheckResultData(
            label: 'Theme Knowledge render views',
            passed: $missingViews === [],
            message: $missingViews === []
                ? 'The knowledge layout and section views are resolvable.'
                : 'Missing knowledge views: ' . implode(', ', $missingViews) . '.',
            remediation: $missingViews === []
                ? null
                : 'Ensure the package view namespace is loaded and the knowledge section Blade files are present.',
        );
    }

    public function vendorAssetsCheck(): DoctorCheckResultData
    {
        $missingAssets = $this->missingVendorAssets();

        return new DoctorCheckResultData(
            label: 'Theme Knowledge vendor assets',
            passed: $missingAssets === [],
            message: $missingAssets === []
                ? 'The knowledge Tailwind import and Blade source assets are registered.'
                : 'Missing knowledge assets: ' . implode(', ', $missingAssets) . '.',
            remediation: $missingAssets === []
                ? null
                : 'Ensure KnowledgeThemeServiceProvider registers the theme CSS import and Blade source path.',
        );
    }

    public function isThemeStudioDefinitionRegistered(): bool
    {
        if (! app()->bound(ThemeRegistry::class)) {
            return false;
        }

        return resolve(ThemeRegistry::class)->has(KnowledgeThemeServiceProvider::THEME_KEY);
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

        if (! is_file(dirname(__DIR__, 2) . '/resources/css/theme-knowledge.css')) {
            $missingAssets[] = 'resources/css/theme-knowledge.css';
        }

        $registeredImports = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindImport);
        $registeredSources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource);

        $hasTailwindImport = $registeredImports->contains(
            static fn (mixed $asset): bool => $asset->packageName === KnowledgeThemeServiceProvider::$packageName
                && $asset->value === 'resources/css/theme-knowledge.css',
        );

        $hasTailwindSource = $registeredSources->contains(
            static fn (mixed $asset): bool => $asset->packageName === KnowledgeThemeServiceProvider::$packageName
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
