<?php

declare(strict_types=1);

use Capell\AgentBridge\Support\KnowledgeRepository;

it('discovers package manifests with capability metadata', function (): void {
    app()->setBasePath(getcwd() ?: dirname(__DIR__, 4));

    $packages = collect((new KnowledgeRepository)->packages());

    $seoSuite = $packages->firstWhere('name', 'capell-app/seo-suite');

    expect($seoSuite)
        ->not->toBeNull()
        ->and($seoSuite['productGroup'])->toBe('Capell Search & SEO')
        ->and($seoSuite['tier'])->toBe('premium')
        ->and($seoSuite['bundle'])->toBe('search-seo')
        ->and($seoSuite['contexts'])->toContain('admin')
        ->and($seoSuite['requires'])->toContain('capell-app/admin')
        ->and($seoSuite['path'])->toEndWith('packages/seo-suite');
});

it('discovers and reads configured public markdown documents', function (): void {
    app()->setBasePath(getcwd() ?: dirname(__DIR__, 4));

    config()->set('capell-agent-bridge.public_docs_paths', [
        base_path('packages/agent-bridge/docs'),
    ]);

    $repository = new KnowledgeRepository;
    $documents = collect($repository->documents());

    $overview = $documents->firstWhere('path', 'packages/agent-bridge/docs/overview.md');

    expect($overview)
        ->not->toBeNull()
        ->and($overview['title'])->toBe('overview')
        ->and($repository->readDocument('packages/agent-bridge/docs/overview.md'))
        ->toContain('Agent Bridge');
});

it('returns null for documents outside the configured public paths', function (): void {
    app()->setBasePath(getcwd() ?: dirname(__DIR__, 4));

    config()->set('capell-agent-bridge.public_docs_paths', [
        base_path('packages/agent-bridge/docs'),
    ]);

    expect((new KnowledgeRepository)->readDocument('composer.json'))->toBeNull();
});
