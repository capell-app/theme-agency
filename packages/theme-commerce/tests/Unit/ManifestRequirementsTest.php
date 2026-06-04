<?php

declare(strict_types=1);
use Illuminate\Support\Facades\File;

describe('theme commerce capell.json manifest', function (): void {
    it('declares its demo command for package demo installs', function (): void {
        $manifest = json_decode(
            File::get(__DIR__ . '/../../capell.json'),
            associative: true,
        );

        expect($manifest['commands']['demo'])->toBe('capell:theme-commerce-demo')
            ->and($manifest['commands']['demoParams'])->toBe(['url', 'languages', 'sites']);
    });

    it('uses buyer-facing marketplace copy and committed real preview assets', function (): void {
        $manifest = json_decode(
            File::get(__DIR__ . '/../../capell.json'),
            associative: true,
        );

        expect($manifest['marketplace']['summary'])->toBe('A premium, conversion-focused retail theme for Capell — image-led catalog, product discovery, social proof, and buying-guide layouts that turn browsing into baskets.')
            ->and($manifest['marketplace']['description'])->toBe('Editorial Commerce is a premium Capell theme built for retail and e-commerce storefronts that need to feel like a buying journey, not a styled brochure. It ships an image-led hero, product finder, collection and product grids, comparison and proof sections, buying-guide editorial, and conversion CTAs — all driven by hydrated render data with zero database access in public Blade. Pair it with Capell Shopify Commerce to light up connected-catalog merchandising panels, and with Blog for buying-guide content that supports purchase decisions. Warm editorial direction (deep ink, forest-green merchandising, coral action accents) and a token-driven design system keep every store on-brand while staying fast and accessible.')
            ->and(array_column($manifest['marketplace']['screenshots'], 'path'))->toBe([
                'docs/assets/marketplace/extension-card.jpg',
                'docs/assets/marketplace/hero-desktop.jpg',
                'docs/assets/marketplace/hero-mobile.jpg',
            ]);

        collect($manifest['marketplace']['screenshots'])
            ->each(fn (array $screenshot): mixed => expect(File::exists(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue());
    });
});
