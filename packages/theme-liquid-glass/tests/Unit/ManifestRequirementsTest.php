<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

describe('theme Liquid Glass capell.json manifest', function (): void {
    it('uses free foundation product metadata and committed marketplace assets', function (): void {
        $manifest = liquidGlassThemeMarketplaceManifest();

        expect($manifest['description'])->toBe('Theme Liquid Glass gives Capell sites a free modern glass interface with translucent panels, crisp typography, warm accent actions, and polished standard sections. Three presets cover bright product launches, editorial glass pages, and darker graphite surfaces while staying fully driven by Theme Studio tokens. It extends the default frontend runtime, keeps public output cache-safe and editor-free, and adds a polished alternative to the Foundation and Corporate free lanes.')
            ->and($manifest['marketplace']['summary'])->toBe('A free glass interface theme for modern Capell sites with translucent panels, sharp content rhythm, and three token-driven presets.')
            ->and($manifest['marketplace']['description'])->toBe($manifest['description']);

        expect($manifest['product'])->toMatchArray([
            'group' => 'Capell Foundation',
            'tier' => 'free',
            'bundle' => 'foundation',
        ])
            ->and($manifest['commercial'])->toMatchArray([
                'proposedLicense' => 'free',
                'requestedCertification' => 'first-party',
                'supportPolicy' => 'capell-first-party',
                'privateDocsRequested' => false,
            ]);

        $screenshots = $manifest['marketplace']['screenshots'] ?? null;

        throw_unless(is_array($screenshots), RuntimeException::class, 'Theme Liquid Glass marketplace screenshots must be an array.');

        foreach ($screenshots as $screenshot) {
            throw_if(! is_array($screenshot) || ! is_string($screenshot['path'] ?? null), RuntimeException::class, 'Theme Liquid Glass marketplace screenshots must define string paths.');

            expect(File::exists(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
        }

        expect(collect($screenshots)->pluck('path')->all())->toBe([
            'docs/assets/marketplace/extension-card.svg',
            'docs/assets/marketplace/liquid-glass-homepage.svg',
            'docs/assets/marketplace/liquid-glass-landing.svg',
            'docs/assets/marketplace/liquid-glass-listing.svg',
            'docs/assets/marketplace/liquid-glass-search.svg',
            'docs/assets/marketplace/liquid-glass-contact.svg',
        ]);
    });

    it('declares its demo command for package demo installs', function (): void {
        $manifest = liquidGlassThemeMarketplaceManifest();

        expect($manifest['commands']['demo'])->toBe('capell:theme-liquid-glass-demo')
            ->and($manifest['commands']['demoParams'])->toBe(['url', 'languages', 'sites']);
    });

    it('declares public-output safety and theme metadata', function (): void {
        $manifest = liquidGlassThemeMarketplaceManifest();

        expect($manifest['kind'])->toBe('theme')
            ->and($manifest['themeKey'])->toBe('liquid-glass')
            ->and($manifest['extends'])->toBe('default')
            ->and($manifest['security']['publicOutput'])->toMatchArray([
                'cacheSafe' => true,
                'forbidAuthoringSurface' => true,
                'forbidSecrets' => true,
                'forbidPublicBladeQueries' => true,
            ]);
    });
});

/**
 * @return array<string, mixed>
 */
function liquidGlassThemeMarketplaceManifest(): array
{
    $manifest = json_decode(
        File::get(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($manifest), RuntimeException::class, 'Theme Liquid Glass manifest must decode to an array.');

    $stringKeyedManifest = [];

    foreach ($manifest as $key => $value) {
        if (is_string($key)) {
            $stringKeyedManifest[$key] = $value;
        }
    }

    return $stringKeyedManifest;
}
