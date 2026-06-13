<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LocalServices\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\ThemeStudio\LocalServices\LocalServicesThemeServiceProvider;
use Illuminate\Support\Collection;
use JsonException;

final class ThemeLocalServicesHealthCheck implements ChecksExtensionHealth
{
    private const string PACKAGE_ROOT = __DIR__ . '/../..';

    private const string STYLESHEET_PATH = 'resources/css/theme-local-services.css';

    /**
     * @var list<string>
     */
    private const array INCLUDED_SECTIONS = [
        'navigation',
        'hero',
        'features',
        'services',
        'service-packages',
        'service-areas',
        'locality-proof',
        'proof',
        'content-listing',
        'quote-form',
        'quote-estimator',
        'case-studies',
        'resources',
        'contact',
        'cta',
        'footer',
    ];

    /**
     * @var list<string>
     */
    private const array FOUNDATION_SECTIONS = [
        'navigation',
        'footer',
    ];

    public function __construct(private readonly string $packageRoot = self::PACKAGE_ROOT) {}

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
            $check->packageFilesCheck(),
            $check->marketplaceManifestCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    public function themeStudioDefinitionCheck(): DoctorCheckResultData
    {
        $definition = LocalServicesThemeServiceProvider::definition();
        $missingSections = $this->missingDefinitionSections();

        $valid = $definition->key === LocalServicesThemeServiceProvider::THEME_KEY
            && $definition->package === LocalServicesThemeServiceProvider::$packageName
            && $definition->extends === 'default'
            && ($definition->assets['css'] ?? null) === 'vendor/capell/themes/local-services.css'
            && $definition->presets !== []
            && $missingSections === [];

        return new DoctorCheckResultData(
            label: 'Theme Local Services Theme Studio definition',
            passed: $valid,
            message: $valid
                ? 'The Local Services theme definition exposes the expected key, package, asset, preset, and section contract.'
                : 'The Local Services theme definition is missing expected wiring: ' . implode(', ', $missingSections) . '.',
            remediation: $valid
                ? null
                : 'Ensure LocalServicesThemeServiceProvider::definition() matches the package manifest and declares every shipped section.',
        );
    }

    public function packageFilesCheck(): DoctorCheckResultData
    {
        $missingFiles = $this->missingRequiredPackageFiles();

        return new DoctorCheckResultData(
            label: 'Theme Local Services package files',
            passed: $missingFiles === [],
            message: $missingFiles === []
                ? 'The Local Services layout, stylesheet, manifest, and package-owned section views are present.'
                : 'Missing package files: ' . implode(', ', $missingFiles) . '.',
            remediation: $missingFiles === []
                ? null
                : 'Restore the missing Local Services theme files before listing or rendering the package.',
        );
    }

    public function marketplaceManifestCheck(): DoctorCheckResultData
    {
        $issues = $this->marketplaceManifestIssues();

        return new DoctorCheckResultData(
            label: 'Theme Local Services manifest wiring',
            passed: $issues === [],
            message: $issues === []
                ? 'The Local Services manifest declares the frontend surface, runtime provider, health check, marketplace copy, and existing screenshot assets.'
                : 'Manifest issues: ' . implode(' ', $issues),
            remediation: $issues === []
                ? null
                : 'Update capell.json so Diagnostics and Marketplace can discover the Local Services theme reliably.',
        );
    }

    /**
     * @return list<string>
     */
    public function missingDefinitionSections(): array
    {
        $definition = LocalServicesThemeServiceProvider::definition();

        return array_values(collect(self::INCLUDED_SECTIONS)
            ->diff($definition->includedSections)
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    public function missingRequiredPackageFiles(): array
    {
        $packageRoot = $this->packageRoot;

        return array_values(collect($this->requiredPackageFiles())
            ->reject(static fn (string $relativePath): bool => is_file($packageRoot . '/' . $relativePath))
            ->values()
            ->all());
    }

    /**
     * @return list<string>
     */
    public function marketplaceManifestIssues(): array
    {
        $manifest = $this->manifest();

        if ($manifest === null) {
            return ['capell.json is missing or invalid.'];
        }

        $issues = [];

        if (($manifest['name'] ?? null) !== LocalServicesThemeServiceProvider::$packageName) {
            $issues[] = 'Package name does not match the service provider package name.';
        }

        if (($manifest['themeKey'] ?? null) !== LocalServicesThemeServiceProvider::THEME_KEY) {
            $issues[] = 'Theme key does not match the service provider theme key.';
        }

        if (($manifest['extends'] ?? null) !== 'default') {
            $issues[] = 'Manifest must declare the built-in default theme as its parent.';
        }

        $requires = data_get($manifest, 'dependencies.requires', []);

        if (! is_array($requires) || ! in_array('capell-app/frontend', $requires, true)) {
            $issues[] = 'Manifest must require Capell Frontend for built-in default theme fallbacks.';
        }

        $surfaces = $manifest['surfaces'] ?? [];

        if (! is_array($surfaces) || ! in_array('frontend', $surfaces, true)) {
            $issues[] = 'Frontend surface is not declared.';
        }

        $runtimeProviders = $manifest['providers']['runtime'] ?? [];

        if (! is_array($runtimeProviders) || ! in_array(LocalServicesThemeServiceProvider::class, $runtimeProviders, true)) {
            $issues[] = 'Runtime provider is not declared.';
        }

        $healthCheckClasses = collect(is_array($manifest['healthChecks'] ?? null) ? $manifest['healthChecks'] : [])
            ->pluck('class')
            ->all();

        if (! in_array(self::class, $healthCheckClasses, true)) {
            $issues[] = 'Health check class is not declared.';
        }

        if (! is_string($manifest['marketplace']['summary'] ?? null) || $manifest['marketplace']['summary'] === '') {
            $issues[] = 'Marketplace summary is missing.';
        }

        if (! is_string($manifest['marketplace']['description'] ?? null) || $manifest['marketplace']['description'] === '') {
            $issues[] = 'Marketplace description is missing.';
        }

        foreach ($this->marketplaceScreenshotPaths($manifest) as $screenshotPath) {
            if (! is_file($this->packageRoot . '/' . $screenshotPath)) {
                $issues[] = 'Screenshot asset is missing: ' . $screenshotPath . '.';
            }
        }

        return $issues;
    }

    /**
     * @return list<string>
     */
    private function requiredPackageFiles(): array
    {
        $sectionViewFiles = collect(self::INCLUDED_SECTIONS)
            ->reject(static fn (string $sectionKey): bool => in_array($sectionKey, self::FOUNDATION_SECTIONS, true))
            ->map(static fn (string $sectionKey): string => 'resources/views/sections/' . $sectionKey . '.blade.php')
            ->values()
            ->all();

        return [
            'capell.json',
            'resources/views/page.blade.php',
            self::STYLESHEET_PATH,
            ...$sectionViewFiles,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function manifest(): ?array
    {
        $manifestPath = $this->packageRoot . '/capell.json';

        if (! is_file($manifestPath)) {
            return null;
        }

        try {
            $contents = file_get_contents($manifestPath);

            if ($contents === false) {
                return null;
            }

            $manifest = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($manifest) ? $manifest : null;
    }

    /**
     * @param  array<string, mixed>  $manifest
     * @return list<string>
     */
    private function marketplaceScreenshotPaths(array $manifest): array
    {
        $screenshots = $manifest['marketplace']['screenshots'] ?? [];

        if (! is_array($screenshots)) {
            return [];
        }

        return array_values(collect($screenshots)
            ->pluck('path')
            ->filter(static fn (mixed $path): bool => is_string($path) && $path !== '')
            ->values()
            ->all());
    }
}
