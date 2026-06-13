<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

describe('theme Liquid Glass capell.json manifest', function (): void {
    it('uses free foundation product metadata and committed marketplace assets', function (): void {
        $manifest = liquidGlassThemeMarketplaceManifest();
        $marketplace = liquidGlassThemeManifestArray($manifest['marketplace'] ?? null, 'marketplace');

        expect(liquidGlassThemeManifestString($manifest['description'] ?? null, 'description'))->toBe('Theme Liquid Glass gives Capell sites a free modern glass interface with translucent panels, crisp typography, warm accent actions, and polished standard sections. Three presets cover bright product launches, editorial glass pages, and darker graphite surfaces while staying fully driven by Theme Studio tokens. It extends the default frontend runtime, keeps public output cache-safe and editor-free, and adds a polished alternative to the Foundation and Corporate free lanes.')
            ->and(liquidGlassThemeManifestString($marketplace['summary'] ?? null, 'marketplace.summary'))->toBe('A free glass interface theme for modern Capell sites with translucent panels, sharp content rhythm, and three token-driven presets.')
            ->and(liquidGlassThemeManifestString($marketplace['description'] ?? null, 'marketplace.description'))->toBe(liquidGlassThemeManifestString($manifest['description'] ?? null, 'description'));

        expect(liquidGlassThemeManifestArray($manifest['product'] ?? null, 'product'))->toMatchArray([
            'group' => 'Capell Foundation',
            'tier' => 'free',
            'bundle' => 'foundation',
        ])
            ->and(liquidGlassThemeManifestArray($manifest['commercial'] ?? null, 'commercial'))->toMatchArray([
                'proposedLicense' => 'free',
                'requestedCertification' => 'first-party',
                'supportPolicy' => 'capell-first-party',
                'privateDocsRequested' => false,
            ]);

        $screenshots = liquidGlassThemeManifestArrayList($marketplace['screenshots'] ?? null, 'marketplace.screenshots');

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
        $commands = liquidGlassThemeManifestArray($manifest['commands'] ?? null, 'commands');

        expect($commands['demo'] ?? null)->toBe('capell:theme-liquid-glass-demo')
            ->and($commands['demoParams'] ?? null)->toBe(['url', 'languages', 'sites']);
    });

    it('declares public-output safety and theme metadata', function (): void {
        $manifest = liquidGlassThemeMarketplaceManifest();

        expect($manifest['kind'])->toBe('theme')
            ->and($manifest['themeKey'])->toBe('liquid-glass')
            ->and($manifest['extends'])->toBe('default')
            ->and(liquidGlassThemeManifestArray(liquidGlassThemeManifestArray($manifest['security'] ?? null, 'security')['publicOutput'] ?? null, 'security.publicOutput'))->toMatchArray([
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

/**
 * @return array<string, mixed>
 */
function liquidGlassThemeManifestArray(mixed $value, string $key): array
{
    if (! is_array($value)) {
        throw new RuntimeException(sprintf('Theme Liquid Glass manifest [%s] must be an array.', $key));
    }

    $array = [];

    foreach ($value as $itemKey => $itemValue) {
        if (is_string($itemKey)) {
            $array[$itemKey] = $itemValue;
        }
    }

    return $array;
}

/**
 * @return list<array<string, mixed>>
 */
function liquidGlassThemeManifestArrayList(mixed $value, string $key): array
{
    if (! is_array($value)) {
        throw new RuntimeException(sprintf('Theme Liquid Glass manifest [%s] must be a list.', $key));
    }

    $items = [];

    foreach ($value as $item) {
        $items[] = liquidGlassThemeManifestArray($item, $key . ' item');
    }

    return $items;
}

function liquidGlassThemeManifestString(mixed $value, string $key): string
{
    if (! is_string($value)) {
        throw new RuntimeException(sprintf('Theme Liquid Glass manifest [%s] must be a string.', $key));
    }

    return $value;
}
