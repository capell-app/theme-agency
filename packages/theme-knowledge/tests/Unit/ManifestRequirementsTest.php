<?php

declare(strict_types=1);

use Capell\ThemeStudio\Knowledge\KnowledgeThemeServiceProvider;

it('declares the required first-party theme manifest boundaries', function (): void {
    $contents = file_get_contents(__DIR__ . '/../../capell.json');
    $manifest = json_decode($contents === false ? '{}' : $contents, true, flags: JSON_THROW_ON_ERROR);

    expect($manifest['themeKey'])->toBe('knowledge')
        ->and($manifest['extends'])->toBe('capell-app/foundation-theme')
        ->and($manifest['database']['migrations'])->toBeFalse()
        ->and($manifest['providers']['runtime'])->toContain(KnowledgeThemeServiceProvider::class);
});

it('uses buyer-facing marketplace copy and committed real preview assets', function (): void {
    $contents = file_get_contents(__DIR__ . '/../../capell.json');
    $manifest = json_decode($contents === false ? '{}' : $contents, true, flags: JSON_THROW_ON_ERROR);

    expect($manifest['marketplace']['summary'])->toBe('A premium knowledge-base and documentation theme for Capell — sidebar-navigated articles, in-page table of contents, prominent search, and clean code blocks out of the box.')
        ->and($manifest['marketplace']['description'])->toBe('Theme Knowledge turns a Capell site into a polished documentation and help centre. It pairs a category sidebar, sticky table of contents, and breadcrumb trails with a prominent search experience and readable long-form typography, so visitors find answers fast. Resource libraries, author bios, topic hubs, and newsletter capture round out a full knowledge-marketing surface, with optional Blog, Search, and Newsletter integrations lighting up automatically when those packages are installed. Built on the Capell foundation theme with a configurable colour palette and accessible focus states. (Note: the sidebar/TOC/breadcrumb/code-block/functional-search claims require the §3 work before this copy is truthful.)')
        ->and(array_column($manifest['marketplace']['screenshots'], 'path'))->toBe([
            'docs/assets/marketplace/extension-card.jpg',
            'docs/assets/marketplace/hero-desktop.jpg',
            'docs/assets/marketplace/hero-mobile.jpg',
        ]);

    collect($manifest['marketplace']['screenshots'])
        ->each(fn (array $screenshot): mixed => expect(file_exists(__DIR__ . '/../../' . $screenshot['path']))->toBeTrue());
});
