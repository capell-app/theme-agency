<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;
use Capell\DocumentLifecycle\Filament\Resources\Documents\DocumentResource;
use Capell\DocumentLifecycle\Manifest\DocumentResourceContribution;

/**
 * @return array<string, mixed>
 */
function documentLifecycleManifest(): array
{
    $manifest = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($manifest), RuntimeException::class, 'Expected document lifecycle manifest array.');

    return $manifest;
}

it('declares the document admin resource as a manifest contribution', function (): void {
    $manifest = documentLifecycleManifest();

    $contribution = null;

    foreach (($manifest['contributes'] ?? []) as $manifestContribution) {
        if (is_array($manifestContribution) && ($manifestContribution['type'] ?? null) === 'admin-resource') {
            $contribution = $manifestContribution;

            break;
        }
    }

    expect($contribution)
        ->toBeArray()
        ->and($contribution['class'] ?? null)->toBe(DocumentResourceContribution::class)
        ->and($contribution['resourceClass'] ?? null)->toBe(DocumentResource::class)
        ->and($contribution['group'] ?? null)->toBe('Document')
        ->and(DocumentResourceContribution::compatibleCapellApiVersion())->toBe('^4.0')
        ->and(class_implements(DocumentResourceContribution::class))->toContain(RegistersExtensionAdminResource::class);
});

it('declares only real package surfaces in the manifest', function (): void {
    $manifest = documentLifecycleManifest();

    expect($manifest['surfaces'] ?? null)->toBe(['admin', 'console'])
        ->and($manifest['providers']['frontend'] ?? null)->toBe([]);
});

it('keeps marketplace screenshots limited to committed marketplace assets while preserving the screenshot contract', function (): void {
    $manifest = documentLifecycleManifest();
    $screenshotContract = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/docs/screenshots.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($screenshotContract), RuntimeException::class, 'Expected screenshot contract array.');

    $contractEntries = $screenshotContract['entries'] ?? [];
    $marketplace = $manifest['marketplace'] ?? [];

    throw_unless(is_array($contractEntries), RuntimeException::class, 'Expected screenshot contract entries array.');
    throw_unless(is_array($marketplace), RuntimeException::class, 'Expected marketplace manifest array.');

    $requiredScreenshotPaths = [];

    foreach ($contractEntries as $contractEntry) {
        if (! is_array($contractEntry) || ($contractEntry['required'] ?? false) !== true) {
            continue;
        }

        $screenshotPath = $contractEntry['screenshotPath'] ?? null;

        if (is_string($screenshotPath)) {
            $requiredScreenshotPaths[] = str_replace('packages/document-lifecycle/', '', $screenshotPath);
        }
    }

    $manifestScreenshotEntries = $marketplace['screenshots'] ?? [];

    throw_unless(is_array($manifestScreenshotEntries), RuntimeException::class, 'Expected marketplace screenshot entries array.');

    $manifestScreenshotPaths = [];

    foreach ($manifestScreenshotEntries as $manifestScreenshotEntry) {
        if (! is_array($manifestScreenshotEntry)) {
            continue;
        }

        $screenshotPath = $manifestScreenshotEntry['path'] ?? null;

        if (is_string($screenshotPath)) {
            $manifestScreenshotPaths[] = $screenshotPath;
        }
    }

    expect($manifestScreenshotPaths)->toBe(['docs/assets/marketplace/extension-card.jpg']);

    foreach ($manifestScreenshotPaths as $manifestScreenshotPath) {
        expect($manifestScreenshotPath)->toStartWith('docs/assets/marketplace/')
            ->and(file_exists(dirname(__DIR__, 2) . '/' . $manifestScreenshotPath))->toBeTrue();
    }

    foreach ($requiredScreenshotPaths as $requiredScreenshotPath) {
        expect($manifestScreenshotPaths)->not->toContain($requiredScreenshotPath);
    }
});
