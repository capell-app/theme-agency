<?php

declare(strict_types=1);
use Illuminate\Support\Facades\File;

describe('theme corporate capell.json manifest', function (): void {
    it('uses the approved marketplace copy and committed PNG screenshots', function (): void {
        $manifest = json_decode(
            File::get(__DIR__ . '/../../capell.json'),
            associative: true,
        );

        expect($manifest['description'])->toBe('Theme Corporate gives established businesses, advisory firms, and public bodies a polished, credibility-first site without a design project. Six curated presets — Boardroom, Civic, Advisory, Integrity, Enterprise Trust, and Public Ledger — span deep-navy formal through accessible civic and editorial advisory looks, each tuned for clarity and contrast. Structured proof carousels, capability bands, and a measured content hierarchy are built to read as authoritative on desktop and mobile alike, with full dark-mode support. Drop it on any Capell site, run the one-command demo, and pick a preset.')
            ->and($manifest['marketplace']['summary'])->toBe('A restrained, trust-led website theme for B2B, professional-services, and public-sector organisations — formal hierarchy, board-grade proof blocks, and six palette presets out of the box.')
            ->and($manifest['marketplace']['description'])->toBe($manifest['description']);

        collect($manifest['marketplace']['screenshots'])
            ->each(function (array $screenshot): void {
                expect($screenshot['path'])->toStartWith('docs/screenshots/')
                    ->toEndWith('.png')
                    ->and(File::exists(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
            });
    });

    it('declares its demo command for package demo installs', function (): void {
        $manifest = json_decode(
            File::get(__DIR__ . '/../../capell.json'),
            associative: true,
        );

        expect($manifest['commands']['demo'])->toBe('capell:theme-corporate-demo')
            ->and($manifest['commands']['demoParams'])->toBe(['url', 'languages', 'sites']);
    });
});
