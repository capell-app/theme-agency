<?php

declare(strict_types=1);

it('keeps marketplace screenshots limited to committed marketplace assets while preserving the screenshot contract', function (): void {
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

    expect($marketplaceScreenshotPaths)->toBe(['docs/assets/marketplace/extension-card.jpg']);

    foreach ($marketplaceScreenshotPaths as $marketplaceScreenshotPath) {
        expect($marketplaceScreenshotPath)->toStartWith('docs/assets/marketplace/')
            ->and(file_exists(dirname(__DIR__, 2) . '/' . $marketplaceScreenshotPath))->toBeTrue();
    }

    foreach ($contractEntries as $contractEntry) {
        if (! is_array($contractEntry) || ($contractEntry['required'] ?? false) !== true) {
            continue;
        }

        $screenshotPath = $contractEntry['screenshotPath'] ?? null;

        if (is_string($screenshotPath)) {
            expect($marketplaceScreenshotPaths)
                ->not->toContain(str_replace('packages/frontend-optimizer/', '', $screenshotPath));
        }
    }
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
