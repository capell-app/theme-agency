<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

describe('theme commerce capell.json manifest', function (): void {
    it('declares its demo command for package demo installs', function (): void {
        $manifest = commerceThemeManifest();

        expect($manifest['commands']['demo'])->toBe('capell:theme-commerce-demo')
            ->and($manifest['commands']['demoParams'])->toBe(['url', 'languages', 'sites']);
    });

    it('uses buyer-facing marketplace copy and committed real preview assets', function (): void {
        $manifest = commerceThemeManifest();
        $marketplace = $manifest['marketplace'] ?? null;
        $screenshots = is_array($marketplace) ? ($marketplace['screenshots'] ?? null) : null;

        throw_unless(is_array($screenshots), RuntimeException::class, 'Theme Commerce manifest screenshots must be an array.');

        $screenshotPaths = [];

        foreach ($screenshots as $screenshot) {
            throw_if(! is_array($screenshot) || ! is_string($screenshot['path'] ?? null), RuntimeException::class, 'Theme Commerce manifest screenshot path must be a string.');

            $screenshotPaths[] = $screenshot['path'];
        }

        expect($manifest['marketplace']['summary'])->toBe('A premium, conversion-focused retail theme for Capell — image-led catalog, product discovery, social proof, and buying-guide layouts that turn browsing into baskets.')
            ->and($manifest['marketplace']['description'])->toBe('Editorial Commerce is a premium Capell theme built for retail and e-commerce storefronts that need to feel like a buying journey, not a styled brochure. It ships an image-led hero, product finder, collection and product grids, comparison and proof sections, buying-guide editorial, and conversion CTAs — all driven by hydrated render data with zero database access in public Blade. Pair it with Capell Shopify Commerce to light up connected-catalog merchandising panels, and with Blog for buying-guide content that supports purchase decisions. Warm editorial direction (deep ink, forest-green merchandising, coral action accents) and a token-driven design system keep every store on-brand while staying fast and accessible.')
            ->and($screenshotPaths)->toBe([
                'docs/assets/marketplace/extension-card.jpg',
                'docs/screenshots/commerce-homepage-layout.png',
            ]);

        foreach ($screenshotPaths as $screenshotPath) {
            expect(File::exists(__DIR__ . '/../../' . $screenshotPath))->toBeTrue();
        }
    });

    it('keeps non-cacheable theme output from queueing invalidation without sources', function (): void {
        $manifest = commerceThemeManifest();
        $cacheSafety = data_get($manifest, 'performance.cacheSafety');

        throw_unless(is_array($cacheSafety), RuntimeException::class, 'Theme Commerce cache safety manifest data must be an array.');

        expect($cacheSafety)->toMatchArray([
            'cacheable' => false,
            'variesBy' => ['site', 'locale'],
            'sensitiveOutput' => false,
            'invalidationSources' => [],
            'queueInvalidation' => false,
        ]);
    });
});

/**
 * @return array<string, mixed>
 */
function commerceThemeManifest(): array
{
    return capell_json_file_array(__DIR__ . '/../../capell.json');
}
