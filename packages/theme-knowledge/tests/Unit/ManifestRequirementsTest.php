<?php

declare(strict_types=1);

use Capell\ThemeStudio\Knowledge\KnowledgeThemeServiceProvider;
use Illuminate\Support\Facades\File;

it('declares the required first-party theme manifest boundaries', function (): void {
    $manifest = knowledgeThemeManifest();
    $database = $manifest['database'] ?? null;
    $providers = $manifest['providers'] ?? null;

    if (! is_array($database)) {
        throw new RuntimeException('Theme Knowledge database manifest data must be an array.');
    }

    if (! is_array($providers)) {
        throw new RuntimeException('Theme Knowledge providers manifest data must be an array.');
    }

    $runtimeProviders = $providers['runtime'] ?? null;

    if (! is_array($runtimeProviders)) {
        throw new RuntimeException('Theme Knowledge runtime providers must be an array.');
    }

    expect($manifest['themeKey'])->toBe('knowledge')
        ->and($manifest['extends'])->toBe('capell-app/foundation-theme')
        ->and($database['migrations'])->toBeFalse()
        ->and($runtimeProviders)->toContain(KnowledgeThemeServiceProvider::class);
});

it('uses buyer-facing marketplace copy and committed real preview assets', function (): void {
    $manifest = knowledgeThemeManifest();
    $marketplace = $manifest['marketplace'] ?? null;

    if (! is_array($marketplace)) {
        throw new RuntimeException('Theme Knowledge marketplace manifest data must be an array.');
    }

    $screenshots = $marketplace['screenshots'] ?? null;

    if (! is_array($screenshots)) {
        throw new RuntimeException('Theme Knowledge marketplace screenshots must be an array.');
    }

    $screenshotPaths = [];

    foreach ($screenshots as $screenshot) {
        if (! is_array($screenshot) || ! is_string($screenshot['path'] ?? null)) {
            throw new RuntimeException('Theme Knowledge marketplace screenshots must define string paths.');
        }

        $screenshotPaths[] = $screenshot['path'];
    }

    expect($marketplace['summary'])->toBe('A premium knowledge-base and documentation theme for Capell — sidebar-navigated articles, in-page table of contents, prominent search, and clean code blocks out of the box.')
        ->and($marketplace['description'])->toBe('Theme Knowledge turns a Capell site into a polished documentation and help centre. It pairs a category sidebar, sticky table of contents, and breadcrumb trails with a prominent search experience and readable long-form typography, so visitors find answers fast. Resource libraries, author bios, topic hubs, and newsletter capture round out a full knowledge-marketing surface, with optional Blog, Search, and Newsletter integrations lighting up automatically when those packages are installed. Built on the Capell foundation theme with a configurable colour palette and accessible focus states. (Note: the sidebar/TOC/breadcrumb/code-block/functional-search claims require the §3 work before this copy is truthful.)')
        ->and($screenshotPaths)->toBe([
            'docs/assets/marketplace/extension-card.jpg',
            'docs/assets/marketplace/hero-desktop.jpg',
            'docs/assets/marketplace/hero-mobile.jpg',
        ]);

    foreach ($screenshotPaths as $screenshotPath) {
        expect(File::exists(__DIR__ . '/../../' . $screenshotPath))->toBeTrue();
    }
});

/**
 * @return array<string, mixed>
 */
function knowledgeThemeManifest(): array
{
    $manifest = json_decode(
        File::get(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    if (! is_array($manifest)) {
        throw new RuntimeException('Theme Knowledge manifest must decode to an array.');
    }

    return $manifest;
}
