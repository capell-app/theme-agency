<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

describe('theme agency capell.json manifest', function (): void {
    it('uses the approved marketplace copy and committed PNG screenshots', function (): void {
        $manifest = agencyThemeManifest();

        expect($manifest['description'])->toBe('Theme Agency turns a Capell site into a confident creative portfolio. It ships an expressive page system — full-bleed launch hero, animated proof wall, project showcase, and a conversion-focused brief CTA — built to make studio and agency work look like the work, not a template. Three presets (Signal, Gallery, Atelier) re-skin every section from energetic high-contrast to refined editorial neutrals, all driven by Theme Studio tokens with zero code. Built on Foundation Theme contracts, it stays fast, cache-aware, and safe for public output, so it drops into the standard Capell theme workflow.')
            ->and($manifest['marketplace']['summary'])->toBe('A bold, motion-led theme for creative studios and marketing agencies — campaign hero, case-study proof, and a filterable work showcase, with three presets from high-contrast Signal to editorial Atelier.')
            ->and($manifest['marketplace']['description'])->toBe($manifest['description']);

        $marketplace = $manifest['marketplace'] ?? null;
        $screenshots = is_array($marketplace) ? ($marketplace['screenshots'] ?? null) : null;

        if (! is_array($screenshots)) {
            throw new RuntimeException('Theme Agency manifest screenshots must be an array.');
        }

        foreach ($screenshots as $screenshot) {
            if (! is_array($screenshot) || ! is_string($screenshot['path'] ?? null)) {
                throw new RuntimeException('Theme Agency manifest screenshot path must be a string.');
            }

            expect($screenshot['path'])->toStartWith('docs/screenshots/')
                ->toEndWith('.png')
                ->and(File::exists(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
        }
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
    $manifest = json_decode(
        File::get(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    if (! is_array($manifest)) {
        throw new RuntimeException('Theme Agency manifest must decode to an array.');
    }

    return $manifest;
}
