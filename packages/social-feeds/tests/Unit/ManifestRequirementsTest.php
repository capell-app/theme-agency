<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;
use Capell\Core\Contracts\Extensions\RegistersExtensionFrontendComponent;
use Capell\Core\Support\Manifest\ManifestValidator;
use Capell\SocialFeeds\Blocks\SocialFeedBlockDefinitionProvider;
use Capell\SocialFeeds\Blocks\SocialFeedBlockRenderer;
use Capell\SocialFeeds\Filament\Resources\SocialFeedConnections\SocialFeedConnectionResource;
use Capell\SocialFeeds\Filament\Resources\SocialFeedItems\SocialFeedItemResource;
use Capell\SocialFeeds\Manifest\SocialFeedConnectionModelContribution;
use Capell\SocialFeeds\Manifest\SocialFeedConnectionResourceContribution;
use Capell\SocialFeeds\Manifest\SocialFeedItemModelContribution;
use Capell\SocialFeeds\Manifest\SocialFeedItemResourceContribution;
use Capell\SocialFeeds\Manifest\SocialFeedWidgetContribution;
use Capell\SocialFeeds\Models\SocialFeedConnection;
use Capell\SocialFeeds\Models\SocialFeedItem;
use Capell\SocialFeeds\Tests\TestCase;
use Illuminate\Support\Facades\File;

uses(TestCase::class);

it('declares valid Capell extension manifest metadata', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = json_decode(File::get($packagePath . '/capell.json'), true, flags: JSON_THROW_ON_ERROR);
    $composer = json_decode(File::get($packagePath . '/composer.json'), true, flags: JSON_THROW_ON_ERROR);

    (new ManifestValidator)->validate($manifest, $composer, 'capell-app/social-feeds', $packagePath . '/capell.json');

    expect($manifest['commercial']['proposedLicense'])->toBe('paid')
        ->and($manifest['commercial']['requestedCertification'])->toBe('first-party')
        ->and($manifest['commercial']['privateDocsRequested'])->toBeTrue()
        ->and($manifest['marketplace']['screenshots'])->not->toBeEmpty();
});

it('declares the shipped admin resources, frontend widget, and schema-owned model contributions', function (): void {
    $manifest = json_decode(
        File::get(dirname(__DIR__, 2) . '/capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['contributes'])->toContain([
        'type' => 'admin-resource',
        'class' => SocialFeedConnectionResourceContribution::class,
        'resourceClass' => SocialFeedConnectionResource::class,
    ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'class' => SocialFeedItemResourceContribution::class,
            'resourceClass' => SocialFeedItemResource::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'frontend-component',
            'class' => SocialFeedWidgetContribution::class,
            'blockKey' => 'social-feed',
            'definitionProviderClass' => SocialFeedBlockDefinitionProvider::class,
            'rendererClass' => SocialFeedBlockRenderer::class,
            'surface' => 'frontend',
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'model',
            'class' => SocialFeedConnectionModelContribution::class,
            'modelClass' => SocialFeedConnection::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'model',
            'class' => SocialFeedItemModelContribution::class,
            'modelClass' => SocialFeedItem::class,
        ])
        ->and(class_implements(SocialFeedConnectionResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(SocialFeedItemResourceContribution::class))->toContain(RegistersExtensionAdminResource::class)
        ->and(class_implements(SocialFeedWidgetContribution::class))->toContain(RegistersExtensionFrontendComponent::class)
        ->and(class_implements(SocialFeedConnectionModelContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(SocialFeedItemModelContribution::class))->toContain(ExtensionContribution::class)
        ->and($manifest['contributionTraceability']['deferredContributions'])->not->toContain('admin-resource', 'model', 'frontend-component');
});

it('declares committed marketplace assets for every required screenshot capture target', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = json_decode(File::get($packagePath . '/capell.json'), true, flags: JSON_THROW_ON_ERROR);
    $screenshotContract = json_decode(File::get($packagePath . '/docs/screenshots.json'), true, flags: JSON_THROW_ON_ERROR);

    $marketplaceScreenshots = $manifest['marketplace']['screenshots'] ?? [];
    $contractEntries = $screenshotContract['entries'] ?? [];

    throw_unless(is_array($marketplaceScreenshots), RuntimeException::class, 'Social Feeds marketplace screenshots must be an array.');
    throw_unless(is_array($contractEntries), RuntimeException::class, 'Social Feeds screenshot contract entries must be an array.');

    $marketplaceScreenshotPaths = [];

    foreach ($marketplaceScreenshots as $marketplaceScreenshot) {
        throw_unless(is_array($marketplaceScreenshot), RuntimeException::class, 'Social Feeds marketplace screenshot entries must be arrays.');

        $path = $marketplaceScreenshot['path'] ?? null;
        $alt = $marketplaceScreenshot['alt'] ?? null;
        $caption = $marketplaceScreenshot['caption'] ?? null;

        throw_unless(is_string($path), RuntimeException::class, 'Social Feeds marketplace screenshot paths must be strings.');
        throw_unless(is_string($alt), RuntimeException::class, 'Social Feeds marketplace screenshot alt text must be strings.');
        throw_unless(is_string($caption), RuntimeException::class, 'Social Feeds marketplace screenshot captions must be strings.');

        $marketplaceScreenshotPaths[] = $path;

        expect(str_starts_with($path, 'docs/assets/marketplace/') || str_starts_with($path, 'docs/screenshots/'))->toBeTrue()
            ->and(File::exists($packagePath . '/' . $path))->toBeTrue()
            ->and(strlen(trim($alt)))->toBeGreaterThanOrEqual(12)
            ->and(strlen(trim($caption)))->toBeGreaterThanOrEqual(12);
    }

    $requiredMarketplaceAssetPaths = [];

    foreach ($contractEntries as $contractEntry) {
        if (! is_array($contractEntry)) {
            continue;
        }

        if (($contractEntry['required'] ?? false) !== true) {
            continue;
        }

        $screenshotPath = $contractEntry['screenshotPath'] ?? null;

        throw_unless(is_string($screenshotPath), RuntimeException::class, 'Required Social Feeds screenshot contract entries must have screenshot paths.');

        $requiredMarketplaceAssetPaths[] = str_replace('packages/social-feeds/', '', $screenshotPath);
    }

    expect($marketplaceScreenshotPaths)
        ->toContain('docs/assets/marketplace/extension-card.svg')
        ->toContain(...$requiredMarketplaceAssetPaths);
});
