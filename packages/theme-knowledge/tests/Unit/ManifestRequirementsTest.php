<?php

declare(strict_types=1);

use Capell\ThemeStudio\Knowledge\KnowledgeThemeServiceProvider;

it('declares the required first-party theme manifest boundaries', function (): void {
    $manifest = knowledgeThemeManifest();
    $database = $manifest['database'] ?? null;
    $providers = $manifest['providers'] ?? null;

    throw_unless(is_array($database), RuntimeException::class, 'Theme Knowledge database manifest data must be an array.');

    throw_unless(is_array($providers), RuntimeException::class, 'Theme Knowledge providers manifest data must be an array.');

    $runtimeProviders = $providers['runtime'] ?? null;

    throw_unless(is_array($runtimeProviders), RuntimeException::class, 'Theme Knowledge runtime providers must be an array.');

    expect($manifest['themeKey'])->toBe('knowledge')
        ->and($manifest['extends'])->toBe('capell-app/foundation-theme')
        ->and($manifest['performance']['frontendRenderBudgetMs'])->toBeLessThanOrEqual(20)
        ->and($manifest['performance']['adminQueryBudget'])->toBe(0)
        ->and($database['migrations'])->toBeFalse()
        ->and($runtimeProviders)->toContain(KnowledgeThemeServiceProvider::class);
});

it('uses buyer-facing marketplace copy and committed real preview assets', function (): void {
    $manifest = knowledgeThemeManifest();
    $marketplace = $manifest['marketplace'] ?? null;

    throw_unless(is_array($marketplace), RuntimeException::class, 'Theme Knowledge marketplace manifest data must be an array.');

    $screenshots = $marketplace['screenshots'] ?? null;

    throw_unless(is_array($screenshots), RuntimeException::class, 'Theme Knowledge marketplace screenshots must be an array.');

    $screenshotPaths = [];

    foreach ($screenshots as $screenshot) {
        throw_if(! is_array($screenshot) || ! is_string($screenshot['path'] ?? null), RuntimeException::class, 'Theme Knowledge marketplace screenshots must define string paths.');

        $screenshotPaths[] = $screenshot['path'];
    }

    expect($marketplace['summary'])->toBe('A premium knowledge-base and documentation theme for Capell — sidebar-navigated articles, in-page table of contents, prominent search, and readable long-form layouts out of the box.')
        ->and($marketplace['description'])->toBe('Theme Knowledge turns a Capell site into a polished documentation and help centre. It pairs a category sidebar, sticky table of contents, code-friendly prose, and breadcrumb trails with a prominent search experience and readable long-form typography, so visitors find answers fast. Resource libraries, author bios, topic hubs, and newsletter capture round out a full knowledge-marketing surface, with optional Blog, Search, and Newsletter integrations lighting up automatically when those packages are installed. Built on the Capell foundation theme with configurable colour tokens, dark-mode support, and accessible focus states.')
        ->and($screenshotPaths)->toBe([
            'docs/assets/marketplace/extension-card.jpg',
            'docs/screenshots/knowledge-homepage-layout.png',
            'docs/screenshots/knowledge-homepage-layout-dark.png',
        ]);

    foreach ($screenshotPaths as $screenshotPath) {
        expect(file_exists(__DIR__ . '/../../' . $screenshotPath))->toBeTrue();
    }
});

/**
 * @return array<string, mixed>
 */
function knowledgeThemeManifest(): array
{
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($manifest), RuntimeException::class, 'Theme Knowledge manifest must decode to an array.');

    return $manifest;
}
