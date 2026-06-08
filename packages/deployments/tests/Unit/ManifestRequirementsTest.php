<?php

declare(strict_types=1);

/**
 * @return array<string, mixed>
 */
function deploymentsPackageManifest(): array
{
    $manifest = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($manifest), RuntimeException::class, 'Expected deployments manifest array.');

    return $manifest;
}

it('declares only shipped deployment surfaces and capabilities', function (): void {
    $manifest = deploymentsPackageManifest();

    expect($manifest['surfaces'] ?? null)->toBe(['admin'])
        ->and($manifest['capabilities'] ?? [])->toBe([
            'deployments',
            'deployments-admin',
            'deployments-install-policy',
            'deployments-publish-history',
            'deployments-publish-idempotency',
            'deployments-token-refresh',
        ])
        ->and($manifest['commands'] ?? [])->toBe([
            'install' => null,
            'setup' => null,
            'demo' => null,
            'doctor' => null,
        ]);
});

it('keeps marketplace screenshots aligned with committed deployment media', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = deploymentsPackageManifest();
    $screenshotContract = json_decode(
        (string) file_get_contents($packagePath . '/docs/screenshots.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($screenshotContract), RuntimeException::class, 'Expected deployments screenshot contract array.');

    $contractEntries = $screenshotContract['entries'] ?? [];
    $marketplace = $manifest['marketplace'] ?? [];
    $marketplaceScreenshotEntries = is_array($marketplace) ? ($marketplace['screenshots'] ?? []) : [];

    throw_unless(is_array($contractEntries), RuntimeException::class, 'Expected deployments screenshot contract entries array.');
    throw_unless(is_array($marketplaceScreenshotEntries), RuntimeException::class, 'Expected deployments marketplace screenshot entries array.');

    $requiredScreenshotPaths = [];

    foreach ($contractEntries as $contractEntry) {
        throw_unless(is_array($contractEntry), RuntimeException::class, 'Deployments screenshot contract entries must be arrays.');

        if (($contractEntry['required'] ?? false) !== true) {
            continue;
        }

        $screenshotPath = $contractEntry['screenshotPath'] ?? null;

        throw_unless(is_string($screenshotPath), RuntimeException::class, 'Required deployments screenshot entries must declare a screenshot path.');

        $requiredScreenshotPaths[] = str_replace('packages/deployments/', '', $screenshotPath);

        $darkScreenshotPath = $contractEntry['darkScreenshotPath'] ?? null;

        if (is_string($darkScreenshotPath) && $darkScreenshotPath !== '') {
            $requiredScreenshotPaths[] = str_replace('packages/deployments/', '', $darkScreenshotPath);
        }
    }

    $marketplaceScreenshotPaths = [];

    foreach ($marketplaceScreenshotEntries as $marketplaceScreenshotEntry) {
        throw_unless(is_array($marketplaceScreenshotEntry), RuntimeException::class, 'Deployments marketplace screenshot entries must be arrays.');

        $path = $marketplaceScreenshotEntry['path'] ?? null;
        $alt = $marketplaceScreenshotEntry['alt'] ?? null;
        $caption = $marketplaceScreenshotEntry['caption'] ?? null;

        throw_unless(is_string($path), RuntimeException::class, 'Deployments marketplace screenshot paths must be strings.');
        throw_unless(is_string($alt), RuntimeException::class, 'Deployments marketplace screenshot alt text must be strings.');
        throw_unless(is_string($caption), RuntimeException::class, 'Deployments marketplace screenshot captions must be strings.');

        $marketplaceScreenshotPaths[] = $path;

        expect(file_exists($packagePath . '/' . $path))->toBeTrue()
            ->and(strlen(trim($alt)))->toBeGreaterThanOrEqual(12)
            ->and(strlen(trim($caption)))->toBeGreaterThanOrEqual(12);
    }

    expect($requiredScreenshotPaths)->toBe([
        'docs/screenshots/deployment-connection-page.png',
        'docs/screenshots/deployment-connection-page-dark.png',
    ])->and($marketplaceScreenshotPaths)->toBe([
        'docs/assets/marketplace/extension-card.jpg',
        ...$requiredScreenshotPaths,
    ]);
});
