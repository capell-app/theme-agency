<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

describe('theme agency capell.json manifest', function (): void {
    it('uses the approved marketplace copy and committed PNG screenshots', function (): void {
        $manifest = agencyThemeManifest();
        $overview = File::get(__DIR__ . '/../../docs/overview.md');

        expect($manifest['description'])->toBe('Theme Agency turns a Capell site into a confident creative portfolio. It ships an expressive page system — full-bleed launch hero, animated proof wall, project showcase, and a conversion-focused brief CTA — built to make studio and agency work look like the work, not a template. Three presets (Signal, Gallery, Atelier) re-skin every section from energetic high-contrast to refined editorial neutrals, all driven by Theme Studio tokens with zero code. Built on the built-in default frontend contracts, it stays fast, cache-aware, and safe for public output, so it drops into the standard Capell theme workflow.')
            ->and($manifest['product'])->toMatchArray([
                'group' => 'Capell Themes',
                'tier' => 'premium',
                'bundle' => 'themes',
            ])
            ->and(data_get($manifest, 'commercial.proposedLicense'))->toBe('paid')
            ->and($overview)->toContain('Tier: **premium**')
            ->and($overview)->toContain('Product group: **Capell Themes**')
            ->and($manifest['marketplace']['summary'])->toBe('A bold, motion-led theme for creative studios and marketing agencies — campaign hero, case-study proof, and a filterable work showcase, with three presets from high-contrast Signal to editorial Atelier.')
            ->and($manifest['marketplace']['description'])->toBe($manifest['description']);

        $marketplace = $manifest['marketplace'] ?? null;
        $screenshots = is_array($marketplace) ? ($marketplace['screenshots'] ?? null) : null;

        throw_unless(is_array($screenshots), RuntimeException::class, 'Theme Agency manifest screenshots must be an array.');

        foreach ($screenshots as $screenshot) {
            throw_if(! is_array($screenshot) || ! is_string($screenshot['path'] ?? null), RuntimeException::class, 'Theme Agency manifest screenshot path must be a string.');

            expect(File::exists(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
        }

        expect(collect($screenshots)->pluck('path')->all())->toBe([
            'docs/assets/marketplace/extension-card.jpg',
            'docs/screenshots/agency-campaign-layout.png',
            'docs/screenshots/agency-case-study-layout.png',
            'docs/screenshots/agency-event-landing-layout.png',
            'docs/screenshots/agency-homepage-layout.png',
            'docs/screenshots/agency-insights-layout.png',
        ]);
    });

    it('declares its demo command for package demo installs', function (): void {
        $manifest = agencyThemeManifest();

        expect($manifest['commands']['demo'])->toBe('capell:theme-agency-demo')
            ->and($manifest['commands']['demoParams'])->toBe(['url', 'languages', 'sites']);
    });
});

/**
 * @return array<string, mixed>
 */
function agencyThemeManifest(): array
{
    return capell_json_file_array(__DIR__ . '/../../capell.json');
}
