<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\EstateAgents\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\ThemeStudio\EstateAgents\EstateAgentsThemeServiceProvider;
use Illuminate\Support\Collection;
use JsonException;

final class ThemeEstateAgentsHealthCheck implements ChecksExtensionHealth
{
    private const string PACKAGE_ROOT = __DIR__ . '/../..';

    private const string STYLESHEET_PATH = 'resources/css/theme-estate-agents.css';

    /**
     * @var list<string>
     */
    private const array INCLUDED_SECTIONS = [
        'navigation',
        'hero',
        'property-search',
        'featured-properties',
        'valuation-cta',
        'local-guide',
        'agent-team',
        'viewing-request',
        'market-proof',
        'features',
        'proof',
        'content-listing',
        'cta',
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
        $definition = EstateAgentsThemeServiceProvider::definition();
        $missingSections = $this->missingDefinitionSections();

        $valid = $definition->key === EstateAgentsThemeServiceProvider::THEME_KEY
            && $definition->package === EstateAgentsThemeServiceProvider::$packageName
            && $definition->extends === 'default'
            && ($definition->assets['css'] ?? null) === 'vendor/capell/themes/estate-agents.css'
            && $definition->presets !== []
            && $missingSections === [];

        return new DoctorCheckResultData(
            label: 'Theme Estate Agents Theme Studio definition',
            passed: $valid,
            message: $valid
                ? 'The Estate Agents theme definition exposes the expected key, package, asset, preset, and section contract.'
                : 'The Estate Agents theme definition is missing expected wiring: ' . implode(', ', $missingSections) . '.',
            remediation: $valid
                ? null
                : 'Ensure EstateAgentsThemeServiceProvider::definition() matches the package manifest and declares every shipped section.',
        );
    }

    public function packageFilesCheck(): DoctorCheckResultData
    {
        $missingFiles = $this->missingRequiredPackageFiles();

        return new DoctorCheckResultData(
            label: 'Theme Estate Agents package files',
            passed: $missingFiles === [],
            message: $missingFiles === []
                ? 'The Estate Agents layout, stylesheet, manifest, and package-owned section views are present.'
                : 'Missing package files: ' . implode(', ', $missingFiles) . '.',
            remediation: $missingFiles === []
                ? null
                : 'Restore the missing Estate Agents theme files before listing or rendering the package.',
        );
    }

    public function marketplaceManifestCheck(): DoctorCheckResultData
    {
        $issues = $this->marketplaceManifestIssues();

        return new DoctorCheckResultData(
            label: 'Theme Estate Agents manifest wiring',
            passed: $issues === [],
            message: $issues === []
                ? 'The Estate Agents manifest declares the frontend surface, runtime provider, health check, marketplace copy, and existing screenshot assets.'
                : 'Manifest issues: ' . implode(' ', $issues),
            remediation: $issues === []
                ? null
                : 'Update capell.json so Diagnostics and Marketplace can discover the Estate Agents theme reliably.',
        );
    }

    /**
     * @return list<string>
     */
    public function missingDefinitionSections(): array
    {
        $definition = EstateAgentsThemeServiceProvider::definition();

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

        if (($manifest['name'] ?? null) !== EstateAgentsThemeServiceProvider::$packageName) {
            $issues[] = 'Package name does not match the service provider package name.';
        }

        if (($manifest['themeKey'] ?? null) !== EstateAgentsThemeServiceProvider::THEME_KEY) {
            $issues[] = 'Theme key does not match the service provider theme key.';
        }

        if (($manifest['extends'] ?? null) !== 'default') {
            $issues[] = 'Manifest must declare the built-in default theme as its parent.';
        }

        $requires = data_get($manifest, 'dependencies.requires', []);

        if (! is_array($requires) || ! in_array('capell-app/foundation-theme', $requires, true)) {
            $issues[] = 'Manifest must require Foundation Theme for built-in default theme inheritance.';
        }

        if (! is_array($requires) || ! in_array('capell-app/frontend', $requires, true)) {
            $issues[] = 'Manifest must require Capell Frontend for built-in default theme fallbacks.';
        }

        $surfaces = $manifest['surfaces'] ?? [];

        if (! is_array($surfaces) || ! in_array('frontend', $surfaces, true)) {
            $issues[] = 'Frontend surface is not declared.';
        }

        $runtimeProviders = data_get($manifest, 'providers.runtime', []);

        if (! is_array($runtimeProviders) || ! in_array(EstateAgentsThemeServiceProvider::class, $runtimeProviders, true)) {
            $issues[] = 'Runtime provider is not declared.';
        }

        $healthCheckClasses = collect(is_array($manifest['healthChecks'] ?? null) ? $manifest['healthChecks'] : [])
            ->pluck('class')
            ->all();

        if (! in_array(self::class, $healthCheckClasses, true)) {
            $issues[] = 'Health check class is not declared.';
        }

        if (data_get($manifest, 'commercial.proposedLicense') !== 'paid') {
            $issues[] = 'Commercial license must be paid for this premium theme.';
        }

        if (! is_string(data_get($manifest, 'marketplace.summary')) || data_get($manifest, 'marketplace.summary') === '') {
            $issues[] = 'Marketplace summary is missing.';
        }

        if (! is_string(data_get($manifest, 'marketplace.description')) || data_get($manifest, 'marketplace.description') === '') {
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

            $manifestData = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (! is_array($manifestData)) {
            return null;
        }

        /** @var array<string, mixed> $manifest */
        $manifest = [];

        foreach ($manifestData as $key => $value) {
            if (is_string($key)) {
                $manifest[$key] = $value;
            }
        }

        return $manifest;
    }

    /**
     * @param  array<string, mixed>  $manifest
     * @return list<string>
     */
    private function marketplaceScreenshotPaths(array $manifest): array
    {
        $screenshots = data_get($manifest, 'marketplace.screenshots', []);

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
