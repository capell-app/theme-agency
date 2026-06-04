<?php

declare(strict_types=1);

use Capell\KnowledgeBase\Actions\CreateKnowledgeBaseArticleAction;
use Capell\KnowledgeBase\Actions\CreateKnowledgeBaseCollectionAction;
use Capell\KnowledgeBase\Data\CreateKnowledgeBaseArticleData;
use Capell\KnowledgeBase\Data\CreateKnowledgeBaseCollectionData;
use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleFeedback;
use Capell\KnowledgeBase\Tests\KnowledgeBaseTestCase;
use Illuminate\Support\Facades\Route;

require_once dirname(__DIR__, 2) . '/KnowledgeBaseTestCase.php';

uses(KnowledgeBaseTestCase::class);

it('registers public knowledge base routes', function (): void {
    expect(Route::has('capell-knowledge-base.index'))->toBeTrue()
        ->and(Route::has('capell-knowledge-base.article'))->toBeTrue()
        ->and(Route::has('capell-knowledge-base.article.feedback'))->toBeTrue();
});

it('throttles the public article feedback route', function (): void {
    $route = Route::getRoutes()->getByName('capell-knowledge-base.article.feedback');

    expect($route?->gatherMiddleware())->toContain('throttle:30,1');
});

it('renders public navigation and articles without authoring internals', function (): void {
    $collection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Getting Started',
        description: 'Install and configure the product.',
    ));

    CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $collection,
        title: 'Install Capell',
        body: '<h2>Install</h2><p>Run the installer.</p>',
        summary: 'Install safely.',
        status: KnowledgeBaseArticleStatus::Published,
    ));

    CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $collection,
        title: 'Draft Article',
        body: '<p>Draft content.</p>',
        status: KnowledgeBaseArticleStatus::Draft,
    ));

    $indexResponse = $this->get('/docs');
    $articleResponse = $this->get('/docs/getting-started/install-capell');

    $indexResponse
        ->assertOk()
        ->assertHeader('Cache-Control', 'max-age=300, public, stale-while-revalidate=300')
        ->assertSee('Knowledge base')
        ->assertSee('Getting Started')
        ->assertSee('Install Capell')
        ->assertDontSee('Draft Article');

    $articleResponse
        ->assertOk()
        ->assertHeader('Cache-Control', 'max-age=300, public, stale-while-revalidate=300')
        ->assertSee('Install Capell')
        ->assertSee('Run the installer.', false)
        ->assertDontSee('Draft Article')
        ->assertDontSee('author_type', false)
        ->assertDontSee('author_id', false)
        ->assertDontSee('model_id', false)
        ->assertDontSee('field_path', false)
        ->assertDontSee('capell-app/knowledge-base', false)
        ->assertDontSee('Filament', false)
        ->assertDontSee('signed', false);
});

it('records public article feedback without exposing raw visitor identifiers', function (): void {
    $collection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Getting Started',
    ));

    CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $collection,
        title: 'Install Capell',
        body: '<p>Run the installer.</p>',
        status: KnowledgeBaseArticleStatus::Published,
    ));

    $this
        ->from('/docs/getting-started/install-capell')
        ->post('/docs/getting-started/install-capell/feedback', [
            'helpful' => '0',
            'comment' => 'Needs more detail.',
        ])
        ->assertRedirect('/docs/getting-started/install-capell')
        ->assertSessionHas('knowledge_base_feedback_status');

    $feedback = KnowledgeBaseArticleFeedback::query()->firstOrFail();

    expect($feedback->helpful)->toBeFalse()
        ->and($feedback->comment)->toBe('Needs more detail.')
        ->and($feedback->visitor_hash)->not->toBeNull()
        ->and($feedback->visitor_hash)->not->toBe('127.0.0.1');
});

it('updates repeat public article feedback instead of stuffing duplicate votes', function (): void {
    $collection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Getting Started',
    ));

    CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $collection,
        title: 'Install Capell',
        body: '<p>Run the installer.</p>',
        status: KnowledgeBaseArticleStatus::Published,
    ));

    $this
        ->from('/docs/getting-started/install-capell')
        ->post('/docs/getting-started/install-capell/feedback', [
            'helpful' => '0',
            'comment' => 'Needs more detail.',
        ])
        ->assertRedirect('/docs/getting-started/install-capell');

    $this
        ->from('/docs/getting-started/install-capell')
        ->post('/docs/getting-started/install-capell/feedback', [
            'helpful' => '1',
            'comment' => 'The update helped.',
        ])
        ->assertRedirect('/docs/getting-started/install-capell');

    $feedback = KnowledgeBaseArticleFeedback::query()->firstOrFail();

    expect(KnowledgeBaseArticleFeedback::query()->count())->toBe(1)
        ->and($feedback->helpful)->toBeTrue()
        ->and($feedback->comment)->toBe('The update helped.');
});

it('does not render private collections or draft articles through public routes', function (): void {
    $privateCollection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Private Docs',
        isPublic: false,
    ));
    $publicCollection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Public Docs',
    ));

    CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $privateCollection,
        title: 'Private Article',
        body: '<p>Private body.</p>',
        status: KnowledgeBaseArticleStatus::Published,
    ));
    CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $publicCollection,
        title: 'Draft Article',
        body: '<p>Draft body.</p>',
        status: KnowledgeBaseArticleStatus::Draft,
    ));

    $this->get('/docs/private-docs/private-article')->assertNotFound();
    $this->get('/docs/public-docs/draft-article')->assertNotFound();
    $this->get('/docs')
        ->assertOk()
        ->assertDontSee('Private Article')
        ->assertDontSee('Draft Article');
});
