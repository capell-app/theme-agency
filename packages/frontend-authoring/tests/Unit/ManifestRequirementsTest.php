<?php

declare(strict_types=1);
use Illuminate\Support\Facades\File;

describe('frontend authoring capell.json manifest', function (): void {
    $authoringManifest = fn (): array => json_decode(
        File::get(__DIR__ . '/../../capell.json'),
        associative: true,
    );

    $screenshotManifest = fn (): array => json_decode(
        File::get(__DIR__ . '/../../docs/screenshots.json'),
        associative: true,
    );

    it('declares requires using full composer package names', function () use ($authoringManifest): void {
        $manifest = $authoringManifest();

        foreach ($manifest['dependencies']['requires'] ?? [] as $requirement) {
            expect($requirement)->toContain('/');
        }
    });

    it('requires the Capell packages it depends on directly', function () use ($authoringManifest): void {
        $manifest = $authoringManifest();

        expect($manifest['dependencies']['requires'])->toContain('capell-app/core')
            ->and($manifest['dependencies']['requires'])->toContain('capell-app/admin')
            ->and($manifest['dependencies']['requires'])->toContain('capell-app/frontend');
    });

    it('requires the full frontend stack for screenshot browser runs', function () use ($screenshotManifest): void {
        $manifest = $screenshotManifest();

        expect($manifest['composerRequires'])->toContain('capell-app/core')
            ->and($manifest['composerRequires'])->toContain('capell-app/admin')
            ->and($manifest['composerRequires'])->toContain('capell-app/frontend')
            ->and($manifest['composerRequires'])->toContain('capell-app/foundation-theme')
            ->and($manifest['composerRequires'])->toContain('capell-app/frontend-authoring');
    });

    it('does not require Filament Peek for frontend authoring screenshots', function () use ($screenshotManifest): void {
        $manifest = $screenshotManifest();

        expect($manifest['composerRequires'])->not->toContain('capell-app/filament-peek')
            ->and($manifest['composerRequires'])->not->toContain('pboivin/filament-peek');
    });

    it('declares browser tests for public safety and admin editing', function () use ($screenshotManifest): void {
        $manifest = $screenshotManifest();
        $browserTestIds = capell_test_collect($manifest['browserTests'] ?? [])->pluck('id')->all();

        expect($browserTestIds)->toContain('anonymous-users-receive-no-authoring-surface')
            ->and($browserTestIds)->toContain('admin-can-open-single-field-editor');
    });
});
