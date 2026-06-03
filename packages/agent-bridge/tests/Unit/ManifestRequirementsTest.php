<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

describe('agent-bridge capell.json manifest', function (): void {
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

    it('uses buyer-facing app extension content', function () use ($manifest, $composer): void {
        $manifestData = $manifest();
        $composerData = $composer();

        expect($manifestData['description'])->toContain('preview-then-confirm')
            ->and($manifestData['description'])->toContain('audited operations')
            ->and($manifestData['marketplace']['summary'])->toContain('scoped tokens')
            ->and($manifestData['marketplace']['summary'])->toContain('full audit trail')
            ->and($composerData['description'])->toContain('Scoped MCP access')
            ->and($composerData['keywords'])->toContain('model-context-protocol')
            ->and($composerData['keywords'])->toContain('scoped-access');
    });

    it('declares every generated screenshot for marketplace display', function () use ($manifest, $packagePath): void {
        $manifestData = $manifest();
        $screenshotPaths = collect($manifestData['marketplace']['screenshots'] ?? [])
            ->pluck('path')
            ->values()
            ->all();

        $generatedScreenshotPaths = collect(File::files($packagePath . '/docs/screenshots'))
            ->map(fn (SplFileInfo $screenshotFile): string => 'docs/screenshots/' . $screenshotFile->getFilename())
            ->sort()
            ->values()
            ->all();

        $declaredScreenshotPaths = collect($screenshotPaths)
            ->sort()
            ->values()
            ->all();

        expect($declaredScreenshotPaths)->toBe($generatedScreenshotPaths);
    });

    it('keeps marketplace screenshots readable and backed by files', function () use ($manifest, $packagePath): void {
        $manifestData = $manifest();

        foreach ($manifestData['marketplace']['screenshots'] ?? [] as $screenshot) {
            expect($screenshot['path'])->toStartWith('docs/screenshots/')
                ->and(File::exists($packagePath . '/' . $screenshot['path']))->toBeTrue()
                ->and(strlen(trim((string) $screenshot['alt'])))->toBeGreaterThanOrEqual(12)
                ->and(strlen(trim((string) $screenshot['caption'])))->toBeGreaterThanOrEqual(12);
        }
    });
});
