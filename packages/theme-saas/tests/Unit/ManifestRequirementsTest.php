<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

describe('theme saas capell.json manifest', function (): void {
    it('uses the approved marketplace copy and committed PNG screenshots', function (): void {
        $manifest = saasThemeManifest();
        $marketplace = $manifest['marketplace'] ?? null;

        if (! is_array($marketplace)) {
            throw new RuntimeException('Theme SaaS marketplace manifest data must be an array.');
        }

        $screenshots = $marketplace['screenshots'] ?? null;

        if (! is_array($screenshots)) {
            throw new RuntimeException('Theme SaaS marketplace screenshots must be an array.');
        }

        expect($manifest['description'])->toBe('Theme SaaS gives software and subscription businesses a product-led storefront out of the box: an activation-first hero, metric and logo proof, a plan-comparison and pricing layout, an ROI calculator, docs onboarding, and a demo-request flow — all rendered from portable Capell content with no presentation markup stored in your pages. It extends Foundation Theme, so your content stays clean while this theme owns the conversion layout, palette, and rhythm. Pairs with Form Builder for live demo/trial capture and Document Lifecycle for in-theme docs, and reads brand tokens so the SaaS preset re-skins the whole site. Built on Blade + Tailwind, cache-safe, and translation-ready.')
            ->and($marketplace['summary'])->toBe('A conversion-focused premium theme for software and subscription products — hero, feature proof, pricing comparison, calculator, docs, and demo-request sections that turn a Capell site into a product-led landing experience.')
            ->and($marketplace['description'])->toBe($manifest['description']);

        foreach ($screenshots as $screenshot) {
            if (! is_array($screenshot) || ! is_string($screenshot['path'] ?? null)) {
                throw new RuntimeException('Theme SaaS marketplace screenshots must define string paths.');
            }

            expect($screenshot['path'])->toStartWith('docs/screenshots/')
                ->toEndWith('.png')
                ->and(File::exists(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
        }
    });

    it('declares its demo command for package demo installs', function (): void {
        $manifest = saasThemeManifest();
        $commands = $manifest['commands'] ?? null;

        if (! is_array($commands)) {
            throw new RuntimeException('Theme SaaS command manifest data must be an array.');
        }

        expect($commands['demo'])->toBe('capell:theme-saas-demo')
            ->and($commands['demoParams'])->toBe(['url', 'languages', 'sites']);
    });
});

/**
 * @return array<string, mixed>
 */
function saasThemeManifest(): array
{
    $manifest = json_decode(
        File::get(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    if (! is_array($manifest)) {
        throw new RuntimeException('Theme SaaS manifest must decode to an array.');
    }

    return $manifest;
}
