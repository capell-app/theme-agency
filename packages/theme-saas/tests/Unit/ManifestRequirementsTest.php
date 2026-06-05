<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

describe('theme saas capell.json manifest', function (): void {
    it('uses the approved marketplace copy and committed PNG screenshots', function (): void {
        $manifest = saasThemeManifest();
        $marketplace = $manifest['marketplace'] ?? null;

        throw_unless(is_array($marketplace), RuntimeException::class, 'Theme SaaS marketplace manifest data must be an array.');

        $screenshots = $marketplace['screenshots'] ?? null;

        throw_unless(is_array($screenshots), RuntimeException::class, 'Theme SaaS marketplace screenshots must be an array.');

        expect($manifest['description'])->toBe('Theme SaaS gives software and subscription businesses a product-led storefront out of the box: an activation-first hero, metric and logo proof, a plan-comparison and pricing layout, an ROI calculator, docs onboarding, and a demo-request flow — all rendered from portable Capell content with no presentation markup stored in your pages. It extends Foundation Theme, so your content stays clean while this theme owns the conversion layout, palette, and rhythm. Pairs with Form Builder for live demo/trial capture and Document Lifecycle for in-theme docs, and reads brand tokens so the SaaS preset re-skins the whole site. Built on Blade + Tailwind, cache-safe, and translation-ready.')
            ->and($marketplace['summary'])->toBe('A conversion-focused premium theme for software and subscription products — hero, feature proof, pricing comparison, calculator, docs, and demo-request sections that turn a Capell site into a product-led landing experience.')
            ->and($marketplace['description'])->toBe($manifest['description']);

        foreach ($screenshots as $screenshot) {
            throw_if(! is_array($screenshot) || ! is_string($screenshot['path'] ?? null), RuntimeException::class, 'Theme SaaS marketplace screenshots must define string paths.');

            expect($screenshot['path'])->toStartWith('docs/screenshots/')
                ->toEndWith('.png')
                ->and(File::exists(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue();
        }
    });

    it('declares its demo command for package demo installs', function (): void {
        $manifest = saasThemeManifest();
        $commands = $manifest['commands'] ?? null;

        throw_unless(is_array($commands), RuntimeException::class, 'Theme SaaS command manifest data must be an array.');

        expect($commands['demo'])->toBe('capell:theme-saas-demo')
            ->and($commands['demoParams'])->toBe(['url', 'languages', 'sites']);
    });

    it('keeps non-cacheable theme output from queueing invalidation without sources', function (): void {
        $manifest = saasThemeManifest();
        $cacheSafety = $manifest['performance']['cacheSafety'] ?? null;

        throw_unless(is_array($cacheSafety), RuntimeException::class, 'Theme SaaS cache safety manifest data must be an array.');

        expect($cacheSafety)->toMatchArray([
            'cacheable' => false,
            'variesBy' => ['site', 'locale'],
            'sensitiveOutput' => false,
            'invalidationSources' => [],
            'queueInvalidation' => false,
        ]);
    });

    it('documents the repo-root package test command without a package-local phpunit config', function (): void {
        $packageReadme = File::get(__DIR__ . '/../../README.md');
        $overview = File::get(__DIR__ . '/../../docs/overview.md');
        $improvementPlan = File::get(__DIR__ . '/../../docs/improvement-plan.md');

        expect($packageReadme)
            ->toContain('Run package tests from the repository root')
            ->toContain('vendor/bin/pest packages/theme-saas/tests')
            ->not->toContain('vendor/bin/pest packages/theme-saas/tests --configuration=phpunit.xml')
            ->and($overview)
            ->toContain('From the repository root, run `vendor/bin/pest packages/theme-saas/tests`')
            ->toContain('this package does not ship its own PHPUnit config')
            ->not->toContain('vendor/bin/pest packages/theme-saas/tests --configuration=phpunit.xml')
            ->and($improvementPlan)
            ->toContain('Verification command context documented.')
            ->not->toContain('Fix docs: remove `--configuration=phpunit.xml`');
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

    throw_unless(is_array($manifest), RuntimeException::class, 'Theme SaaS manifest must decode to an array.');

    return $manifest;
}
