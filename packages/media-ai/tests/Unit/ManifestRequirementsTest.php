<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

/**
 * @return array<string, mixed>
 */
function mediaAIManifest(): array
{
    $manifest = json_decode(
        File::get(dirname(__DIR__, 2) . '/capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($manifest), RuntimeException::class, 'Expected Media AI manifest array.');

    return $manifest;
}

/**
 * @return array<string, mixed>
 */
function mediaAIComposerManifest(): array
{
    $composerManifest = json_decode(
        File::get(dirname(__DIR__, 2) . '/composer.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($composerManifest), RuntimeException::class, 'Expected Media AI composer manifest array.');

    return $composerManifest;
}

/**
 * @return array<string, mixed>
 */
function mediaAIScreenshotContract(): array
{
    $screenshotContract = json_decode(
        File::get(dirname(__DIR__, 2) . '/docs/screenshots.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($screenshotContract), RuntimeException::class, 'Expected Media AI screenshot contract array.');

    return $screenshotContract;
}

it('keeps marketplace copy aligned with shipped image doctor capabilities', function (): void {
    $manifest = mediaAIManifest();
    $composerManifest = mediaAIComposerManifest();
    $summary = 'Provider-backed image editing inside Capell\'s media library: improve images, remove backgrounds or objects, restore, and upscale through a Doctor image action that stays hidden until configured.';

    expect($manifest['product'] ?? null)->toBe([
        'group' => 'Capell Media',
        'tier' => 'premium',
        'bundle' => 'media',
    ])
        ->and($manifest['commercial'] ?? null)->toBe([
            'proposedLicense' => 'paid',
            'requestedCertification' => 'first-party',
            'supportPolicy' => 'priority',
            'privateDocsRequested' => true,
        ])
        ->and($manifest['marketplace']['summary'] ?? null)->toBe($summary)
        ->and($manifest['description'] ?? null)->toContain('remove backgrounds or objects')
        ->and($manifest['description'] ?? null)->toContain('rather than a production AI provider')
        ->and($manifest['description'] ?? null)->toContain('premium Capell Media add-on')
        ->and($composerManifest['description'] ?? null)->toContain('Provider-backed image editing')
        ->and($composerManifest['description'] ?? null)->toContain('safe Doctor image action')
        ->and($composerManifest['description'] ?? null)->not->toContain('alt text');
});

it('keeps marketplace screenshots backed by the committed media ai gallery assets', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = mediaAIManifest();
    $screenshotContract = mediaAIScreenshotContract();
    $marketplaceScreenshotEntries = $manifest['marketplace']['screenshots'] ?? [];
    $contractEntries = $screenshotContract['entries'] ?? [];

    throw_unless(is_array($marketplaceScreenshotEntries), RuntimeException::class, 'Expected Media AI marketplace screenshot entries array.');
    throw_unless(is_array($contractEntries), RuntimeException::class, 'Expected Media AI screenshot contract entries array.');

    $marketplaceScreenshotPaths = [];

    foreach ($marketplaceScreenshotEntries as $marketplaceScreenshotEntry) {
        throw_unless(is_array($marketplaceScreenshotEntry), RuntimeException::class, 'Media AI marketplace screenshot entries must be arrays.');

        $screenshotPath = $marketplaceScreenshotEntry['path'] ?? null;
        $altText = $marketplaceScreenshotEntry['alt'] ?? null;
        $caption = $marketplaceScreenshotEntry['caption'] ?? null;

        throw_unless(is_string($screenshotPath), RuntimeException::class, 'Media AI marketplace screenshot paths must be strings.');
        throw_unless(is_string($altText), RuntimeException::class, 'Media AI marketplace screenshot alt text must be strings.');
        throw_unless(is_string($caption), RuntimeException::class, 'Media AI marketplace screenshot captions must be strings.');

        $marketplaceScreenshotPaths[] = $screenshotPath;

        expect(File::exists($packagePath . '/' . $screenshotPath))->toBeTrue()
            ->and(strlen(trim($altText)))->toBeGreaterThanOrEqual(12)
            ->and(strlen(trim($caption)))->toBeGreaterThanOrEqual(12);
    }

    $requiredScreenshotPaths = [];

    foreach ($contractEntries as $contractEntry) {
        throw_unless(is_array($contractEntry), RuntimeException::class, 'Media AI screenshot contract entries must be arrays.');

        if (($contractEntry['required'] ?? false) !== true) {
            continue;
        }

        $screenshotPath = $contractEntry['screenshotPath'] ?? null;

        throw_unless(is_string($screenshotPath), RuntimeException::class, 'Required Media AI screenshot contract entries must declare a screenshot path.');

        $requiredScreenshotPaths[] = str_replace('packages/media-ai/', '', $screenshotPath);
    }

    expect($marketplaceScreenshotPaths)->toBe([
        'docs/assets/marketplace/extension-card.jpg',
        'docs/images/screenshots/media-ai-doctor-image.png',
        'docs/images/screenshots/media-ai-doctor-image-dark.png',
    ])->and($requiredScreenshotPaths)->toBe([
        'docs/images/screenshots/media-ai-doctor-image.png',
        'docs/images/screenshots/media-ai-doctor-image-dark.png',
    ]);

    foreach ($requiredScreenshotPaths as $requiredScreenshotPath) {
        expect($marketplaceScreenshotPaths)->toContain($requiredScreenshotPath);
    }
});
