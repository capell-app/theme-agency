<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\AeoAnalytics\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\AeoAnalytics\AeoAnalyticsThemeServiceProvider;
use Illuminate\Support\Collection;

final class ThemeAeoAnalyticsHealthCheck implements ChecksExtensionHealth
{
    /** @var list<string> */
    private const array REQUIRED_VIEW_FILES = [
        'resources/views/page.blade.php',
        'resources/views/livewire/page/page.blade.php',
        'resources/views/sections/navigation.blade.php',
        'resources/views/sections/hero.blade.php',
        'resources/views/sections/dashboard-preview.blade.php',
        'resources/views/sections/metric-cards.blade.php',
        'resources/views/sections/features.blade.php',
        'resources/views/sections/coverage-map.blade.php',
        'resources/views/sections/integrations-grid.blade.php',
        'resources/views/sections/report-gallery.blade.php',
        'resources/views/sections/proof.blade.php',
        'resources/views/sections/content-listing.blade.php',
        'resources/views/sections/cta.blade.php',
        'resources/views/sections/footer.blade.php',
    ];

    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /** @return Collection<int, DoctorCheckResultData> */
    public static function runDiagnostics(): Collection
    {
        $check = new self;

        return collect([$check->themeStudioDefinitionCheck(), $check->requiredViewsCheck(), $check->marketplaceScreenshotsCheck()]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    public function themeStudioDefinitionCheck(): DoctorCheckResultData
    {
        $registered = app()->bound(ThemeRegistry::class) && resolve(ThemeRegistry::class)->has(AeoAnalyticsThemeServiceProvider::THEME_KEY);

        return new DoctorCheckResultData(
            label: 'Theme AEO Analytics Studio definition',
            passed: $registered,
            message: $registered ? 'The theme definition is registered and available for rendering.' : 'The theme definition is not registered.',
            remediation: $registered ? null : 'Ensure AeoAnalyticsThemeServiceProvider boots and registers the theme with ThemeRegistry.',
        );
    }

    /** @param list<string>|null $requiredViewFiles */
    public function requiredViewsCheck(?array $requiredViewFiles = null): DoctorCheckResultData
    {
        $missingViewFiles = $this->missingRequiredViewFiles($requiredViewFiles);

        return new DoctorCheckResultData(
            label: 'Theme AEO Analytics Blade views',
            passed: $missingViewFiles === [],
            message: $missingViewFiles === [] ? 'All required theme Blade views are present.' : 'Missing theme Blade views: ' . implode(', ', $missingViewFiles) . '.',
            remediation: $missingViewFiles === [] ? null : 'Restore the missing package Blade files before enabling the theme.',
        );
    }

    /** @param list<string>|null $screenshotPaths */
    public function marketplaceScreenshotsCheck(?array $screenshotPaths = null): DoctorCheckResultData
    {
        $missingScreenshotFiles = $this->missingMarketplaceScreenshotFiles($screenshotPaths);

        return new DoctorCheckResultData(
            label: 'Theme AEO Analytics marketplace screenshots',
            passed: $missingScreenshotFiles === [],
            message: $missingScreenshotFiles === [] ? 'Every marketplace screenshot path points at a committed package file.' : 'Missing marketplace screenshots: ' . implode(', ', $missingScreenshotFiles) . '.',
            remediation: $missingScreenshotFiles === [] ? null : 'Update capell.json to reference committed screenshots or add the missing media files.',
        );
    }

    /**
     * @param  list<string>|null  $requiredViewFiles
     * @return list<string>
     */
    public function missingRequiredViewFiles(?array $requiredViewFiles = null): array
    {
        return array_values(collect($requiredViewFiles ?? self::REQUIRED_VIEW_FILES)->reject(fn (string $relativePath): bool => is_file($this->packagePath($relativePath)))->values()->all());
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

        return array_values(collect($paths)->reject(fn (string $relativePath): bool => is_file($this->packagePath($relativePath)))->values()->all());
    }

    /** @return list<string> */
    private function marketplaceScreenshotPaths(): array
    {
        $manifestPath = $this->packagePath('capell.json');
        $manifest = is_file($manifestPath) ? json_decode((string) file_get_contents($manifestPath), associative: true) : [];
        $screenshots = is_array($manifest) ? data_get($manifest, 'marketplace.screenshots', []) : [];

        return array_values(collect(is_array($screenshots) ? $screenshots : [])->map(static fn (mixed $screenshot): mixed => is_array($screenshot) ? ($screenshot['path'] ?? null) : null)->filter(static fn (mixed $path): bool => is_string($path) && $path !== '')->all());
    }

    private function packagePath(string $relativePath): string
    {
        return dirname(__DIR__, 2) . '/' . ltrim($relativePath, '/');
    }
}
