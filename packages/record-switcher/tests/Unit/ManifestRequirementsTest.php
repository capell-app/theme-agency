<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Contracts\Extensions\RegistersExtensionAsset;
use Capell\Core\Support\Manifest\ManifestValidator;
use Capell\RecordSwitcher\Health\RecordSwitcherHealthCheck;
use Capell\RecordSwitcher\Manifest\RecordSwitcherAssetsContribution;
use Capell\RecordSwitcher\Manifest\RecordSwitcherHealthContribution;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

describe('record-switcher manifest', function (): void {
    $packagePath = dirname(__DIR__, 2);

    $manifest = fn (): array => recordSwitcherJsonFileArray($packagePath . '/capell.json');

    $composer = fn (): array => recordSwitcherJsonFileArray($packagePath . '/composer.json');

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
        $product = recordSwitcherArrayValue($manifestData, 'product');
        $commercial = recordSwitcherArrayValue($manifestData, 'commercial');
        $capabilities = $manifestData['capabilities'] ?? [];
        throw_unless(is_array($capabilities), RuntimeException::class, 'Expected Record Switcher capabilities.');

        expect($product['tier'] ?? null)->toBe('free')
            ->and($product['bundle'] ?? null)->toBe('foundation')
            ->and($capabilities)->toContain('same-site-sibling-prioritization')
            ->and($commercial['proposedLicense'] ?? null)->toBe('free')
            ->and($commercial['requestedCertification'] ?? null)->toBe('first-party')
            ->and($commercial['supportPolicy'] ?? null)->toBe('capell-first-party')
            ->and($commercial['privateDocsRequested'] ?? null)->toBeFalse();
    });

    it('declares the shipped admin asset contribution contract', function () use ($manifest): void {
        $contributions = recordSwitcherManifestContributions($manifest());
        $contribution = $contributions->firstWhere('class', RecordSwitcherAssetsContribution::class) ?? [];
        throw_unless(is_array($contribution), RuntimeException::class, 'Expected Record Switcher asset contribution.');
        $contributionClass = $contribution['class'] ?? null;

        expect($contribution['type'])->toBe('asset')
            ->and($contribution['class'])->toBe(RecordSwitcherAssetsContribution::class)
            ->and(is_string($contributionClass) && is_subclass_of($contributionClass, RegistersExtensionAsset::class))->toBeTrue()
            ->and(RecordSwitcherAssetsContribution::compatibleCapellApiVersion())->toBe('^4.0');
    });

    it('declares the shipped diagnostics contribution contract', function () use ($manifest): void {
        $contributions = recordSwitcherManifestContributions($manifest());
        $contribution = $contributions->firstWhere('class', RecordSwitcherHealthContribution::class) ?? [];
        throw_unless(is_array($contribution), RuntimeException::class, 'Expected Record Switcher health contribution.');
        $contributionClass = $contribution['class'] ?? null;

        expect($contribution['type'])->toBe('health-check')
            ->and($contribution['checkClass'])->toBe(RecordSwitcherHealthCheck::class)
            ->and(is_string($contributionClass) && is_subclass_of($contributionClass, ChecksExtensionHealth::class))->toBeTrue()
            ->and(RecordSwitcherHealthContribution::compatibleCapellApiVersion())->toBe('^4.0');
    });

    it('keeps marketplace screenshots readable and backed by committed files', function () use ($manifest, $packagePath): void {
        $marketplace = recordSwitcherArrayValue($manifest(), 'marketplace');
        $screenshots = $marketplace['screenshots'] ?? [];
        throw_unless(is_array($screenshots), RuntimeException::class, 'Expected Record Switcher screenshots.');

        expect($screenshots)->not->toBeEmpty();

        foreach ($screenshots as $screenshot) {
            throw_unless(is_array($screenshot), RuntimeException::class, 'Expected Record Switcher screenshot metadata.');

            $path = recordSwitcherStringValue($screenshot, 'path');

            expect(
                str_starts_with($path, 'docs/assets/marketplace/')
                    || str_starts_with($path, 'docs/screenshots/'),
            )->toBeTrue()
                ->and(File::exists($packagePath . '/' . $path))->toBeTrue()
                ->and(strlen(trim(recordSwitcherStringValue($screenshot, 'alt'))))->toBeGreaterThanOrEqual(12)
                ->and(strlen(trim(recordSwitcherStringValue($screenshot, 'caption'))))->toBeGreaterThanOrEqual(12);
        }
    });

    it('maps required screenshot contract entries to committed runner captures', function () use ($packagePath): void {
        $contract = json_decode(
            File::get($packagePath . '/docs/screenshots.json'),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );
        throw_unless(is_array($contract), RuntimeException::class, 'Record Switcher screenshot contract must decode to an array.');

        $entries = $contract['entries'] ?? [];
        throw_unless(is_array($entries), RuntimeException::class, 'Record Switcher screenshot contract entries must be arrays.');

        $generatedFor = $contract['generatedFor'] ?? null;
        $composerRequires = $contract['composerRequires'] ?? [];
        throw_unless(is_array($composerRequires), RuntimeException::class, 'Record Switcher screenshot contract composer requirements must be an array.');

        expect($generatedFor)->toBe('deployment-screenshot-runner')
            ->and($composerRequires)->toContain('capell-app/record-switcher')
            ->and($entries)->not->toBeEmpty();

        foreach ($entries as $entry) {
            throw_unless(is_array($entry), RuntimeException::class, 'Record Switcher screenshot contract entries must be arrays.');
            $screenshotPath = $entry['screenshotPath'] ?? null;
            throw_unless(is_string($screenshotPath), RuntimeException::class, 'Record Switcher screenshot paths must be strings.');

            expect($entry['required'])->toBeTrue()
                ->and($screenshotPath)->toStartWith('packages/record-switcher/docs/screenshots/')
                ->and($screenshotPath)->toEndWith('.png');

            $relativePath = str_replace('packages/record-switcher/', '', $screenshotPath);

            expect(File::exists($packagePath . '/' . $relativePath))->toBeTrue();

            $darkRelativePath = preg_replace('/\.png$/', '-dark.png', $relativePath);

            expect(is_string($darkRelativePath))->toBeTrue()
                ->and(File::exists($packagePath . '/' . $darkRelativePath))->toBeTrue();
        }
    });
});

/**
 * @return array<string, mixed>
 */
function recordSwitcherJsonFileArray(string $path): array
{
    $data = json_decode(File::get($path), associative: true, flags: JSON_THROW_ON_ERROR);

    throw_unless(is_array($data), RuntimeException::class, sprintf('Expected JSON file [%s] to decode to an array.', $path));

    return recordSwitcherStringKeyedArray($data);
}

/**
 * @param  array<string, mixed>  $items
 * @return array<string, mixed>
 */
function recordSwitcherArrayValue(array $items, string $key): array
{
    $value = $items[$key] ?? null;

    throw_unless(is_array($value), RuntimeException::class, sprintf('Expected Record Switcher manifest key [%s] to be an array.', $key));

    return recordSwitcherStringKeyedArray($value);
}

/**
 * @param  array<array-key, mixed>  $items
 */
function recordSwitcherStringValue(array $items, string $key): string
{
    $value = $items[$key] ?? null;

    throw_unless(is_string($value), RuntimeException::class, sprintf('Expected Record Switcher manifest key [%s] to be a string.', $key));

    return $value;
}

/**
 * @param  array<string, mixed>  $manifest
 * @return Collection<int|string, mixed>
 */
function recordSwitcherManifestContributions(array $manifest): Collection
{
    $contributes = $manifest['contributes'] ?? null;

    throw_unless(is_array($contributes), RuntimeException::class, 'Expected Record Switcher manifest contributions.');

    return collect($contributes);
}

/**
 * @param  array<array-key, mixed>  $items
 * @return array<string, mixed>
 */
function recordSwitcherStringKeyedArray(array $items): array
{
    $stringKeyedItems = [];

    foreach ($items as $key => $value) {
        throw_unless(is_string($key), RuntimeException::class, 'Expected Record Switcher manifest keys to be strings.');

        $stringKeyedItems[$key] = $value;
    }

    return $stringKeyedItems;
}
