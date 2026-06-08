<?php

declare(strict_types=1);

use Capell\FrontendOptimizer\Models\FrontendRenderProfile;

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

    expect($manifest['dependencies']['supports'] ?? [])->toContain('capell-app/media-library')
        ->and($manifest['capabilities'] ?? [])->toContain('frontend-optimizer-images');
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
