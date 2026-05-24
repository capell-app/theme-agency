<?php

declare(strict_types=1);

use Capell\AgentBridge\Support\KnowledgeRepository;
use Capell\AgentBridge\Tools\Knowledge\RecommendPackagesTool;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;

it('recommends matching packages from the knowledge repository', function (): void {
    app()->setBasePath(getcwd());

    $response = (new RecommendPackagesTool)->handle(
        new Request(['query' => 'seo redirects']),
        new KnowledgeRepository,
    );

    $structuredContent = $response->getStructuredContent();

    expect($structuredContent)
        ->not->toBeNull()
        ->and($structuredContent['query'])->toBe('seo redirects')
        ->and($structuredContent['recommendations'])->not->toBeEmpty();

    $recommendations = $structuredContent['recommendations'];
    $recommendedNames = is_array($recommendations) ? array_column($recommendations, 'name') : [];

    expect($recommendedNames)
        ->toContain('capell-app/seo-suite');
});

it('requires a package recommendation query', function (): void {
    (new RecommendPackagesTool)->handle(
        new Request([]),
        new KnowledgeRepository,
    );
})->throws(ValidationException::class);
