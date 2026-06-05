<?php

declare(strict_types=1);

use Capell\KnowledgeBase\Actions\BuildPublicKnowledgeBaseArticleDataAction;
use Capell\KnowledgeBase\Actions\CreateKnowledgeBaseArticleAction;
use Capell\KnowledgeBase\Actions\CreateKnowledgeBaseCollectionAction;
use Capell\KnowledgeBase\Data\CreateKnowledgeBaseArticleData;
use Capell\KnowledgeBase\Data\CreateKnowledgeBaseCollectionData;
use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Tests\KnowledgeBaseTestCase;

require_once dirname(__DIR__, 2) . '/KnowledgeBaseTestCase.php';

uses(KnowledgeBaseTestCase::class);

function createSanitizationArticle(string $body): KnowledgeBaseArticle
{
    $collection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Getting Started',
    ));

    return CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $collection,
        title: 'Install Capell',
        body: $body,
        status: KnowledgeBaseArticleStatus::Published,
    ));
}

it('strips dangerous markup from the public article body while preserving safe rich text', function (): void {
    $article = createSanitizationArticle(
        '<h2>Install</h2><p>Run the <a href="/docs">installer</a>.</p>'
        . '<script>document.cookie</script>'
        . '<p onclick="steal()">Click me</p>'
        . '<iframe src="https://evil.example"></iframe>',
    );

    $articleData = BuildPublicKnowledgeBaseArticleDataAction::run($article);

    expect($articleData)->not->toBeNull();
    throw_unless($articleData !== null, RuntimeException::class, 'Expected public knowledge base article data.');

    expect($articleData->body)->toContain('<h2>Install</h2>')
        ->and($articleData->body)->toContain('Run the')
        ->and($articleData->body)->toContain('installer')
        ->and($articleData->body)->not->toContain('<script>')
        ->and($articleData->body)->not->toContain('document.cookie')
        ->and($articleData->body)->not->toContain('onclick')
        ->and($articleData->body)->not->toContain('<iframe');
});

it('does not render injected scripts on the public article page', function (): void {
    createSanitizationArticle(
        '<p>Safe paragraph.</p><script>window.location="https://evil.example"</script>',
    );

    $this->get('/docs/getting-started/install-capell')
        ->assertOk()
        ->assertSee('Safe paragraph.', false)
        ->assertDontSee('<script>', false)
        ->assertDontSee('evil.example', false);
});
