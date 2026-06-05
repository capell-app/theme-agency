<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\RegistersExtensionAsset;
use Capell\Core\Support\Manifest\ManifestValidator;
use Capell\RecordSwitcher\Manifest\RecordSwitcherAssetsContribution;
use Illuminate\Support\Facades\File;

describe('record-switcher manifest', function (): void {
    $packagePath = dirname(__DIR__, 2);

    $manifest = fn (): array => json_decode(
        File::get($packagePath . '/capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    $composer = fn (): array => json_decode(
        File::get($packagePath . '/composer.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    it('validates the extension manifest against composer metadata', function () use ($manifest, $composer): void {
        $validator = new ManifestValidator;

        $validator->validate(
            $manifest(),
            $composer(),
            'capell-app/record-switcher',
            dirname(__DIR__, 2) . '/capell.json',
        );

        expect(true)->toBeTrue();
    });

    it('declares free first-party marketplace metadata', function () use ($manifest): void {
        $manifestData = $manifest();

        expect($manifestData['product']['tier'])->toBe('free')
            ->and($manifestData['product']['bundle'])->toBe('foundation')
            ->and($manifestData['commercial']['proposedLicense'])->toBe('free')
            ->and($manifestData['commercial']['requestedCertification'])->toBe('first-party')
            ->and($manifestData['commercial']['supportPolicy'])->toBe('capell-first-party')
            ->and($manifestData['commercial']['privateDocsRequested'])->toBeFalse();
    });

    it('declares the shipped admin asset contribution contract', function () use ($manifest): void {
        $contribution = $manifest()['contributes'][0] ?? [];
        $contributionClass = $contribution['class'] ?? null;

        expect($contribution['type'])->toBe('asset')
            ->and($contribution['class'])->toBe(RecordSwitcherAssetsContribution::class)
            ->and(is_string($contributionClass) && is_subclass_of($contributionClass, RegistersExtensionAsset::class))->toBeTrue()
            ->and(RecordSwitcherAssetsContribution::compatibleCapellApiVersion())->toBe('^4.0');
    });

    it('keeps marketplace screenshots readable and backed by committed files', function () use ($manifest, $packagePath): void {
        $screenshots = $manifest()['marketplace']['screenshots'] ?? [];

        expect($screenshots)->not->toBeEmpty();

        foreach ($screenshots as $screenshot) {
            $path = (string) $screenshot['path'];

            expect(
                str_starts_with($path, 'docs/assets/marketplace/')
                    || str_starts_with($path, 'docs/screenshots/'),
            )->toBeTrue()
                ->and(File::exists($packagePath . '/' . $path))->toBeTrue()
                ->and(strlen(trim((string) $screenshot['alt'])))->toBeGreaterThanOrEqual(12)
                ->and(strlen(trim((string) $screenshot['caption'])))->toBeGreaterThanOrEqual(12);
        }
    });

    it('maps required screenshot contract entries to committed runner captures', function () use ($packagePath): void {
        $contract = json_decode(
            File::get($packagePath . '/docs/screenshots.json'),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        $entries = $contract['entries'] ?? [];

        expect($contract['generatedFor'])->toBe('deployment-screenshot-runner')
            ->and($contract['composerRequires'] ?? [])->toContain('capell-app/record-switcher')
            ->and($entries)->not->toBeEmpty();

        foreach ($entries as $entry) {
            expect($entry['required'])->toBeTrue()
                ->and($entry['screenshotPath'])->toStartWith('packages/record-switcher/docs/screenshots/')
                ->and($entry['screenshotPath'])->toEndWith('.png');

            $relativePath = str_replace('packages/record-switcher/', '', (string) $entry['screenshotPath']);

            expect(File::exists($packagePath . '/' . $relativePath))->toBeTrue();

            $darkRelativePath = preg_replace('/\.png$/', '-dark.png', $relativePath);

            expect(is_string($darkRelativePath))->toBeTrue()
                ->and(File::exists($packagePath . '/' . $darkRelativePath))->toBeTrue();
        }
    });
});
