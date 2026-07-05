<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LiquidGlass\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\LiquidGlass\LiquidGlassThemeServiceProvider;
use Illuminate\Support\Collection;

final class ThemeLiquidGlassHealthCheck implements ChecksExtensionHealth
{
    /**
     * @var list<string>
     */
    private const array REQUIRED_VIEW_FILES = [
        'resources/views/sections/navigation.blade.php',
        'resources/views/sections/hero.blade.php',
        'resources/views/sections/features.blade.php',
        'resources/views/sections/showcase.blade.php',
        'resources/views/sections/presets.blade.php',
        'resources/views/sections/proof.blade.php',
        'resources/views/sections/content-listing.blade.php',
        'resources/views/sections/cta.blade.php',
        'resources/views/sections/footer.blade.php',
        // Task C2: layout-builder-native chrome and bespoke widget views.
        'resources/views/header/index.blade.php',
        'resources/views/footer.blade.php',
        'resources/views/widget/cta.blade.php',
        'resources/views/widget/showcase.blade.php',
        'resources/views/widget/presets.blade.php',
    ];

    /**
     * @var list<string>
     */
    private const array REQUIRED_ASSET_FILES = [
        'resources/css/theme-liquid-glass.css',
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
            $check->requiredViewsCheck(),
            $check->requiredAssetsCheck(),
            $check->rendererAlignmentCheck(),
            $check->marketplaceScreenshotsCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    /**
     * @param  list<string>|null  $requiredAssetFiles
     */
    public function requiredAssetsCheck(?array $requiredAssetFiles = null): DoctorCheckResultData
    {
        $missingAssetFiles = $this->missingRequiredAssetFiles($requiredAssetFiles);

        return new DoctorCheckResultData(
            label: 'Theme Liquid Glass assets',
            passed: $missingAssetFiles === [],
            message: $missingAssetFiles === []
                ? 'All Theme Liquid Glass package assets are present.'
                : 'Missing Theme Liquid Glass assets: ' . implode(', ', $missingAssetFiles) . '.',
            remediation: $missingAssetFiles === []
                ? null
                : 'Restore the missing CSS or asset files before enabling Theme Liquid Glass.',
        );
    }

    public function rendererAlignmentCheck(): DoctorCheckResultData
    {
        $definition = LiquidGlassThemeServiceProvider::definition();
        $valid = $definition->key === LiquidGlassThemeServiceProvider::THEME_KEY
            && $definition->package === LiquidGlassThemeServiceProvider::$packageName
            && $definition->extends === 'default'
            && ($definition->assets['css'] ?? null) === 'vendor/capell/themes/liquid-glass.css';

        return new DoctorCheckResultData(
            label: 'Theme Liquid Glass renderer alignment',
            passed: $valid,
            message: $valid
                ? 'Theme definition, inherited renderer, and CSS asset path are aligned.'
                : 'Theme definition, inherited renderer, or CSS asset path is misaligned.',
            remediation: $valid
                ? null
                : 'Align LiquidGlassThemeServiceProvider::definition(), capell.json, and the published CSS asset path.',
        );
    }

    public function themeStudioDefinitionCheck(): DoctorCheckResultData
    {
        $registered = $this->isThemeStudioDefinitionRegistered();

        return new DoctorCheckResultData(
            label: 'Theme Liquid Glass Studio definition',
            passed: $registered,
            message: $registered
                ? 'The Liquid Glass Theme Studio definition is registered and available for rendering.'
                : 'The Liquid Glass Theme Studio definition is not registered; pages cannot render through Theme Liquid Glass.',
            remediation: $registered
                ? null
                : 'Ensure LiquidGlassThemeServiceProvider boots and registers the Liquid Glass theme with the ThemeRegistry.',
        );
    }

    /**
     * @param  list<string>|null  $requiredViewFiles
     */
    public function requiredViewsCheck(?array $requiredViewFiles = null): DoctorCheckResultData
    {
        $missingViewFiles = $this->missingRequiredViewFiles($requiredViewFiles);

        return new DoctorCheckResultData(
            label: 'Theme Liquid Glass Blade views',
            passed: $missingViewFiles === [],
            message: $missingViewFiles === []
                ? 'All Theme Liquid Glass page and section Blade views are present.'
                : 'Missing Theme Liquid Glass Blade views: ' . implode(', ', $missingViewFiles) . '.',
            remediation: $missingViewFiles === []
                ? null
                : 'Restore the missing package Blade files before enabling Theme Liquid Glass.',
        );
    }

    /**
     * @param  list<string>|null  $screenshotPaths
     */
    public function marketplaceScreenshotsCheck(?array $screenshotPaths = null): DoctorCheckResultData
    {
        $missingScreenshotFiles = $this->missingMarketplaceScreenshotFiles($screenshotPaths);

        return new DoctorCheckResultData(
            label: 'Theme Liquid Glass marketplace screenshots',
            passed: $missingScreenshotFiles === [],
            message: $missingScreenshotFiles === []
                ? 'Every marketplace screenshot path points at a committed package file.'
                : 'Missing marketplace screenshots: ' . implode(', ', $missingScreenshotFiles) . '.',
            remediation: $missingScreenshotFiles === []
                ? null
                : 'Update capell.json to reference committed screenshots or add the missing media files.',
        );
    }

    public function isThemeStudioDefinitionRegistered(): bool
    {
        if (! app()->bound(ThemeRegistry::class)) {
            return false;
        }

        return resolve(ThemeRegistry::class)->has(LiquidGlassThemeServiceProvider::THEME_KEY);
    }

    /**
     * @param  list<string>|null  $requiredViewFiles
     * @return list<string>
     */
    public function missingRequiredViewFiles(?array $requiredViewFiles = null): array
    {
        return array_values(collect($requiredViewFiles ?? self::REQUIRED_VIEW_FILES)
            ->reject(fn (string $relativePath): bool => is_file($this->packagePath($relativePath)))
            ->values()
            ->all());
    }

    /**
     * @param  list<string>|null  $requiredAssetFiles
     * @return list<string>
     */
    public function missingRequiredAssetFiles(?array $requiredAssetFiles = null): array
    {
        return array_values(collect($requiredAssetFiles ?? self::REQUIRED_ASSET_FILES)
            ->reject(fn (string $relativePath): bool => is_file($this->packagePath($relativePath)))
            ->values()
            ->all());
    }

    /**
     * @param  list<string>|null  $screenshotPaths
     * @return list<string>
     */
    public function missingMarketplaceScreenshotFiles(?array $screenshotPaths = null): array
    {
        $paths = $screenshotPaths ?? $this->marketplaceScreenshotPaths();

        if ($paths === []) {
            return ['capell.json marketplace.screenshots'];
        }

        return array_values(collect($paths)
            ->reject(fn (string $relativePath): bool => is_file($this->packagePath($relativePath)))
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    private function marketplaceScreenshotPaths(): array
    {
        $manifestPath = $this->packagePath('capell.json');

        if (! is_file($manifestPath)) {
            return [];
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), associative: true);
        $screenshots = is_array($manifest) ? data_get($manifest, 'marketplace.screenshots', []) : [];

        if (! is_array($screenshots)) {
            return [];
        }

        return array_values(collect($screenshots)
            ->map(static fn (mixed $screenshot): mixed => is_array($screenshot) ? ($screenshot['path'] ?? null) : null)
            ->filter(static fn (mixed $path): bool => is_string($path) && $path !== '')
            ->all());
    }

    private function packagePath(string $relativePath): string
    {
        return dirname(__DIR__, 2) . '/' . ltrim($relativePath, '/');
    }
}
