<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionSetting;
use Capell\FrontendOptimizer\Console\Commands\PruneRenderProfilesCommand;
use Capell\FrontendOptimizer\Filament\Configurators\Types\FrontendOptimizerPageTypeConfigurator;
use Capell\FrontendOptimizer\Health\FrontendOptimizerHealthCheck;
use Capell\FrontendOptimizer\Manifest\FrontendOptimizerConfiguratorContribution;
use Capell\FrontendOptimizer\Manifest\FrontendOptimizerConsoleCommandsContribution;
use Capell\FrontendOptimizer\Manifest\FrontendOptimizerHealthContribution;
use Capell\FrontendOptimizer\Manifest\FrontendOptimizerModelsContribution;
use Capell\FrontendOptimizer\Manifest\FrontendOptimizerSettingsContribution;
use Capell\FrontendOptimizer\Models\FrontendOptimizationRun;
use Capell\FrontendOptimizer\Models\FrontendRenderProfile;
use Capell\FrontendOptimizer\Settings\FrontendOptimizerSettings;

it('keeps marketplace screenshots aligned with the committed runner captures', function (): void {
    $manifest = frontendOptimizerPackageManifest();
    $screenshotContract = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/docs/screenshots.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($screenshotContract), RuntimeException::class, 'Expected screenshot contract array.');

    $marketplace = $manifest['marketplace'] ?? [];
    $contractEntries = $screenshotContract['entries'] ?? [];

    throw_unless(is_array($marketplace), RuntimeException::class, 'Expected marketplace manifest array.');
    throw_unless(is_array($contractEntries), RuntimeException::class, 'Expected screenshot contract entries array.');

    $marketplaceScreenshotEntries = $marketplace['screenshots'] ?? [];

    throw_unless(is_array($marketplaceScreenshotEntries), RuntimeException::class, 'Expected marketplace screenshot entries array.');

    $marketplaceScreenshotPaths = [];

    foreach ($marketplaceScreenshotEntries as $marketplaceScreenshotEntry) {
        if (! is_array($marketplaceScreenshotEntry)) {
            continue;
        }

        $path = $marketplaceScreenshotEntry['path'] ?? null;

        if (is_string($path)) {
            $marketplaceScreenshotPaths[] = $path;
        }
    }

    $contractPaths = collect($contractEntries)
        ->filter(fn (mixed $contractEntry): bool => is_array($contractEntry) && ($contractEntry['required'] ?? false) === true)
        ->pluck('screenshotPath')
        ->filter(fn (mixed $screenshotPath): bool => is_string($screenshotPath))
        ->map(fn (string $screenshotPath): string => str_replace('packages/frontend-optimizer/', '', $screenshotPath))
        ->values();

    expect($marketplaceScreenshotPaths)->toBe([
        'docs/assets/marketplace/extension-card.jpg',
    ]);

    expect($contractPaths->all())->toBe([
        'docs/screenshots/frontend-optimizer-profile-assets.png',
        'docs/screenshots/frontend-optimizer-critical-css-output.png',
    ]);

    foreach ($marketplaceScreenshotPaths as $marketplaceScreenshotPath) {
        expect(file_exists(dirname(__DIR__, 2) . '/' . $marketplaceScreenshotPath))->toBeTrue();
    }
});

it('declares render profile cache invalidation metadata for generated critical css', function (): void {
    $cacheSafety = frontendOptimizerPackageManifest()['performance']['cacheSafety'] ?? null;

    throw_unless(is_array($cacheSafety), RuntimeException::class, 'Expected cache safety metadata array.');

    $invalidationSources = $cacheSafety['invalidationSources'] ?? null;

    throw_unless(is_array($invalidationSources), RuntimeException::class, 'Expected invalidation source metadata array.');

    expect($invalidationSources)->toContain([
        'model' => FrontendRenderProfile::class,
        'events' => ['updated'],
    ]);
});

it('declares image optimization and media library pairings', function (): void {
    $manifest = frontendOptimizerPackageManifest();
    $dependencies = frontendOptimizerManifestArray($manifest, 'dependencies');

    expect($dependencies['supports'] ?? [])->toContain('capell-app/media-library');
});

it('declares shipped admin configurator and operational contribution metadata', function (): void {
    $manifest = frontendOptimizerPackageManifest();
    $contributes = frontendOptimizerManifestArray($manifest, 'contributes');

    expect($contributes)->toContain([
        'type' => 'configurator',
        'class' => FrontendOptimizerConfiguratorContribution::class,
        'configuratorClass' => FrontendOptimizerPageTypeConfigurator::class,
        'group' => 'blueprint',
    ])
        ->and($contributes)->toContain([
            'type' => 'model',
            'class' => FrontendOptimizerModelsContribution::class,
            'modelClasses' => [
                FrontendRenderProfile::class,
                FrontendOptimizationRun::class,
            ],
        ])
        ->and($contributes)->toContain([
            'type' => 'console-command',
            'class' => FrontendOptimizerConsoleCommandsContribution::class,
            'commands' => ['capell:frontend-optimizer:prune-profiles'],
            'commandClasses' => [
                PruneRenderProfilesCommand::class,
            ],
        ])
        ->and($contributes)->toContain([
            'type' => 'setting',
            'class' => FrontendOptimizerSettingsContribution::class,
            'settingsClass' => FrontendOptimizerSettings::class,
            'settingsGroup' => 'frontend_optimizer',
        ])
        ->and($contributes)->toContain([
            'type' => 'health-check',
            'class' => FrontendOptimizerHealthContribution::class,
            'checkClass' => FrontendOptimizerHealthCheck::class,
        ])
        ->and(data_get($manifest, 'contributionTraceability.deferredContributions'))->toBe([])
        ->and(class_implements(FrontendOptimizerConfiguratorContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(FrontendOptimizerModelsContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(FrontendOptimizerConsoleCommandsContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(FrontendOptimizerSettingsContribution::class))->toContain(RegistersExtensionSetting::class)
        ->and(class_implements(FrontendOptimizerHealthContribution::class))->toContain(ChecksExtensionHealth::class);
});

/**
 * @return array<string, mixed>
 */
function frontendOptimizerPackageManifest(): array
{
    $manifest = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($manifest), RuntimeException::class, 'Expected frontend optimizer manifest array.');

    return $manifest;
}

/**
 * @param  array<string, mixed>  $manifest
 * @return array<array-key, mixed>
 */
function frontendOptimizerManifestArray(array $manifest, string $key): array
{
    $value = $manifest[$key] ?? null;

    throw_unless(is_array($value), RuntimeException::class, sprintf('Expected frontend optimizer manifest [%s] to be an array.', $key));

    return $value;
}
