<?php

declare(strict_types=1);

use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\FoundationTheme\Health\FoundationThemeHealthCheck;
use Capell\FoundationTheme\Providers\FoundationThemeServiceProvider;

beforeEach(function (): void {
    CapellCore::forcePackageInstalled('capell-app/frontend');
    CapellCore::forcePackageInstalled('capell-app/layout-builder');

    $registry = resolve(ThemeRegistry::class);
    $registry->reset();
    $registry->register(
        FoundationThemeServiceProvider::definition(),
        new BladeThemeRenderer(
            themeKey: FoundationThemeServiceProvider::THEME_KEY,
            layoutView: 'capell-foundation-theme::theme.page',
            sectionRenderers: [],
        ),
        [],
    );

    $this->publishedManifestPath = public_path('vendor/capell-foundation-theme/manifest.json');

    if (! is_dir(dirname($this->publishedManifestPath))) {
        mkdir(dirname($this->publishedManifestPath), 0o775, true);
    }

    file_put_contents($this->publishedManifestPath, '{}');
});

afterEach(function (): void {
    if (is_file($this->publishedManifestPath)) {
        unlink($this->publishedManifestPath);
    }

    resolve(ThemeRegistry::class)->reset();
});

it('reports a compatible capell api version', function (): void {
    expect(FoundationThemeHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('runs real diagnostics returning check results', function (): void {
    $results = FoundationThemeHealthCheck::runDiagnostics();

    expect($results)->toHaveCount(3)
        ->and($results->every(static fn (mixed $result): bool => $result instanceof DoctorCheckResultData))->toBeTrue();
});

it('passes when the definition, required packages, and published assets are present', function (): void {
    $results = FoundationThemeHealthCheck::runDiagnostics();

    expect(FoundationThemeHealthCheck::passed())->toBeTrue()
        ->and($results->every(static fn (DoctorCheckResultData $result): bool => $result->passed))->toBeTrue();
});

it('fails the theme definition check when the definition is not registered', function (): void {
    resolve(ThemeRegistry::class)->reset();

    $check = new FoundationThemeHealthCheck;

    expect($check->isThemeStudioDefinitionRegistered())->toBeFalse()
        ->and($check->themeStudioDefinitionCheck()->passed)->toBeFalse()
        ->and(FoundationThemeHealthCheck::passed())->toBeFalse();
});

it('fails the required packages check when a dependency is missing', function (): void {
    CapellCore::forcePackageInstalled('capell-app/layout-builder', false);

    $check = new FoundationThemeHealthCheck;

    expect($check->missingRequiredPackages())->toContain('capell-app/layout-builder')
        ->and($check->requiredPackagesCheck()->passed)->toBeFalse()
        ->and(FoundationThemeHealthCheck::passed())->toBeFalse();
});

it('fails the published assets check when the manifest is missing', function (): void {
    unlink($this->publishedManifestPath);

    $check = new FoundationThemeHealthCheck;

    expect($check->publishedAssetManifestExists())->toBeFalse()
        ->and($check->publishedAssetsCheck()->passed)->toBeFalse()
        ->and(FoundationThemeHealthCheck::passed())->toBeFalse();
});
