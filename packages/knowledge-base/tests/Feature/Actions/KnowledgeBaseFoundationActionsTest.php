<?php

declare(strict_types=1);

use Capell\KnowledgeBase\Actions\BuildAiReadableKnowledgeBaseOutputAction;
use Capell\KnowledgeBase\Actions\BuildKnowledgeBaseArticleSchemaAction;
use Capell\KnowledgeBase\Actions\BuildKnowledgeBaseSearchDocumentsAction;
use Capell\KnowledgeBase\Actions\BuildPublicKnowledgeBaseArticleDataAction;
use Capell\KnowledgeBase\Actions\BuildPublicKnowledgeBaseNavigationAction;
use Capell\KnowledgeBase\Actions\CreateKnowledgeBaseArticleAction;
use Capell\KnowledgeBase\Actions\CreateKnowledgeBaseArticleVersionAction;
use Capell\KnowledgeBase\Actions\CreateKnowledgeBaseCollectionAction;
use Capell\KnowledgeBase\Actions\PublishKnowledgeBaseArticleVersionAction;
use Capell\KnowledgeBase\Actions\RecordKnowledgeBaseArticleFeedbackAction;
use Capell\KnowledgeBase\Actions\RelateKnowledgeBaseArticlesAction;
use Capell\KnowledgeBase\Actions\UpdateKnowledgeBaseArticleAction;
use Capell\KnowledgeBase\Actions\UpdateKnowledgeBaseCollectionAction;
use Capell\KnowledgeBase\Data\CreateKnowledgeBaseArticleData;
use Capell\KnowledgeBase\Data\CreateKnowledgeBaseArticleVersionData;
use Capell\KnowledgeBase\Data\CreateKnowledgeBaseCollectionData;
use Capell\KnowledgeBase\Data\PublicKnowledgeBaseNavigationItemData;
use Capell\KnowledgeBase\Data\RecordKnowledgeBaseArticleFeedbackData;
use Capell\KnowledgeBase\Data\UpdateKnowledgeBaseArticleData;
use Capell\KnowledgeBase\Data\UpdateKnowledgeBaseCollectionData;
use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleFeedback;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleVersion;
use Capell\KnowledgeBase\Tests\KnowledgeBaseTestCase;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Assert;

require_once dirname(__DIR__, 2) . '/KnowledgeBaseTestCase.php';

uses(KnowledgeBaseTestCase::class);

it('creates collections, versioned articles, and public-safe article data', function (): void {
    $collection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Getting Started',
        description: 'Install and configure the product.',
    ));

    $article = CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $collection,
        title: 'Install Capell',
        body: '<h2>Install</h2><p>Run the installer and configure the site.</p>',
        summary: 'Install the product safely.',
        status: KnowledgeBaseArticleStatus::Published,
        searchWeight: 80,
    ));

    $currentVersion = $article->currentVersion;

    expect($article->status)->toBe(KnowledgeBaseArticleStatus::Published)
        ->and($currentVersion)->toBeInstanceOf(KnowledgeBaseArticleVersion::class);
    throw_unless($currentVersion instanceof KnowledgeBaseArticleVersion, RuntimeException::class, 'Expected the created article current version.');

    expect($currentVersion->author_type)->toBeNull()
        ->and($currentVersion->author_id)->toBeNull();

    $publicArticle = BuildPublicKnowledgeBaseArticleDataAction::run($article);
    $publicPayload = json_encode($publicArticle?->toArray(), JSON_THROW_ON_ERROR);

    expect($publicArticle?->publicPath)->toBe('/docs/getting-started/install-capell')
        ->and($publicPayload)->toContain('Install Capell')
        ->and($publicPayload)->not->toContain('author')
        ->and($publicPayload)->not->toContain('author_type')
        ->and($publicPayload)->not->toContain('author_id')
        ->and($publicPayload)->not->toContain('model_id')
        ->and($publicPayload)->not->toContain('field_path')
        ->and($publicPayload)->not->toContain('Filament')
        ->and($publicPayload)->not->toContain('signed')
        ->and($publicPayload)->not->toContain('capell-app/knowledge-base');
});

it('publishes new versions and keeps navigation, search, and ai output public only', function (): void {
    $publicCollection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Public Docs',
        sortOrder: 1,
    ));
    $privateCollection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Private Docs',
        isPublic: false,
    ));
    $childCollection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Child Docs',
        parent: $publicCollection,
        sortOrder: 2,
    ));

    $publicArticle = CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $publicCollection,
        title: 'Public Article',
        body: '<p>Old public body.</p>',
        summary: 'Old public summary.',
        status: KnowledgeBaseArticleStatus::Published,
        searchWeight: 90,
    ));
    $hiddenArticle = CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $privateCollection,
        title: 'Hidden Article',
        body: '<p>Hidden body.</p>',
        status: KnowledgeBaseArticleStatus::Published,
    ));
    CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $childCollection,
        title: 'Child Article',
        body: '<p>Child body.</p>',
        status: KnowledgeBaseArticleStatus::Published,
    ));

    $version = CreateKnowledgeBaseArticleVersionAction::run(new CreateKnowledgeBaseArticleVersionData(
        article: $publicArticle,
        version: 'v2',
        title: 'Public Article Updated',
        body: '<h2>Updated</h2><p>Public body for visitors and AI.</p>',
        summary: 'Updated public summary.',
    ));

    PublishKnowledgeBaseArticleVersionAction::run($version);

    $navigation = BuildPublicKnowledgeBaseNavigationAction::run();
    /** @var Collection<int, mixed> $searchDocuments */
    $searchDocuments = BuildKnowledgeBaseSearchDocumentsAction::run();
    /** @var Collection<int, mixed> $aiOutput */
    $aiOutput = BuildAiReadableKnowledgeBaseOutputAction::run();

    $rootNavigationItem = $navigation->first();
    Assert::assertInstanceOf(PublicKnowledgeBaseNavigationItemData::class, $rootNavigationItem);

    $childNavigationItem = $rootNavigationItem->children[0] ?? null;
    Assert::assertInstanceOf(PublicKnowledgeBaseNavigationItemData::class, $childNavigationItem);

    expect($navigation)->toHaveCount(1)
        ->and($rootNavigationItem->articles)->toHaveCount(1)
        ->and($rootNavigationItem->articles[0]['title'])->toBe('Public Article Updated')
        ->and($rootNavigationItem->children)->toHaveCount(1)
        ->and($childNavigationItem->title)->toBe('Child Docs')
        ->and($childNavigationItem->articles[0]['title'])->toBe('Child Article')
        ->and($searchDocuments)->toHaveCount(1)
        ->and($searchDocuments->first()->weight)->toBe(90)
        ->and($searchDocuments->first()->title)->toBe('Public Article Updated')
        ->and($aiOutput)->toHaveCount(1)
        ->and($aiOutput->first()->content)->toBe('Updated Public body for visitors and AI.')
        ->and($aiOutput->first()->publicPath)->toBe('/docs/public-docs/public-article')
        ->and($aiOutput->pluck('title')->all())->not->toContain($hiddenArticle->title);
});

it('exposes a public-safe search payload for the search package', function (): void {
    $collection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Getting Started',
    ));

    $article = CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $collection,
        title: 'Install Capell',
        body: '<p>Run the installer.</p>',
        summary: 'Install safely.',
        status: KnowledgeBaseArticleStatus::Published,
        searchWeight: 80,
    ));

    $payload = $article->refresh()->toSearchableArray();

    expect($payload)->toMatchArray([
        'title' => 'Install Capell',
        'url' => '/docs/getting-started/install-capell',
        'excerpt' => 'Install safely.',
        'body' => 'Run the installer.',
        'type' => 'knowledge-base',
        'status' => 'published',
        'is_public' => true,
    ])
        ->and($payload['meta'])->toMatchArray([
            'collection' => 'Getting Started',
            'version' => 'v1',
            'weight' => 80,
        ])
        ->and($payload)->not->toHaveKey('author_id')
        ->and($payload)->not->toHaveKey('field_path');
});

it('builds article schema data for public knowledge base articles', function (): void {
    $collection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Getting Started',
    ));

    $article = CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $collection,
        title: 'Install Capell',
        body: '<h2>Install</h2><p>Run the installer.</p>',
        summary: 'Install safely.',
        status: KnowledgeBaseArticleStatus::Published,
    ));

    $schema = BuildKnowledgeBaseArticleSchemaAction::run($article, 'https://example.test/docs/getting-started/install-capell');

    expect($schema)->toMatchArray([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        '@id' => 'https://example.test/docs/getting-started/install-capell#article',
        'url' => 'https://example.test/docs/getting-started/install-capell',
        'headline' => 'Install Capell',
        'description' => 'Install safely.',
        'articleBody' => 'Install Run the installer.',
    ])
        ->and($schema)->not->toHaveKey('author_id')
        ->and($schema)->not->toHaveKey('field_path');
});

it('updates an article by publishing a new version through the article update action', function (): void {
    $collection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Public Docs',
    ));
    $article = CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $collection,
        title: 'Public Article',
        body: '<p>Old body.</p>',
        summary: 'Old summary.',
        status: KnowledgeBaseArticleStatus::Published,
    ));

    $updated = UpdateKnowledgeBaseArticleAction::run($article, new UpdateKnowledgeBaseArticleData(
        collection: $collection,
        title: 'Public Article Revised',
        body: '<p>New body.</p>',
        slug: 'public-article-revised',
        summary: 'New summary.',
        version: 'v2',
        status: KnowledgeBaseArticleStatus::Published,
        searchWeight: 75,
        isAiReadable: false,
    ));

    $updated->load('currentVersion');

    expect($updated->status)->toBe(KnowledgeBaseArticleStatus::Published)
        ->and($updated->title)->toBe('Public Article Revised')
        ->and($updated->slug)->toBe('public-article-revised')
        ->and($updated->summary)->toBe('New summary.')
        ->and($updated->search_weight)->toBe(75)
        ->and($updated->is_ai_readable)->toBeFalse()
        ->and($updated->versions()->count())->toBe(2)
        ->and($updated->currentVersion?->version)->toBe('v2')
        ->and($updated->currentVersion?->body)->toBe('<p>New body.</p>')
        ->and($updated->currentVersion?->published_at)->not->toBeNull();
});

it('rejects duplicate article slugs inside the same collection only', function (): void {
    $collection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Public Docs',
    ));
    $otherCollection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Developer Docs',
    ));

    CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $collection,
        title: 'Install Capell',
        body: '<p>Install the product.</p>',
        status: KnowledgeBaseArticleStatus::Published,
    ));

    CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $otherCollection,
        title: 'Install Capell',
        body: '<p>Install the developer tools.</p>',
        status: KnowledgeBaseArticleStatus::Published,
    ));

    try {
        CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
            collection: $collection,
            title: 'Install Capell',
            body: '<p>Install the product again.</p>',
            status: KnowledgeBaseArticleStatus::Published,
        ));
    } catch (ValidationException $validationException) {
        expect($validationException->errors())->toBe([
            'slug' => [__('capell-knowledge-base::generic.validation.slug_unique')],
        ]);

        return;
    }

    Assert::fail('Expected duplicate article slug validation to fail.');
});

it('rejects duplicate collection slugs and keys during updates', function (): void {
    $collection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Public Docs',
        key: 'public-docs',
    ));
    $otherCollection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Developer Docs',
        key: 'developer-docs',
    ));

    $slugRejected = false;

    try {
        UpdateKnowledgeBaseCollectionAction::run($collection, new UpdateKnowledgeBaseCollectionData(
            title: 'Public Docs',
            slug: $otherCollection->slug,
            key: 'public-docs-updated',
        ));
    } catch (ValidationException $validationException) {
        $slugRejected = true;

        expect($validationException->errors())->toBe([
            'slug' => [__('capell-knowledge-base::generic.validation.collection_slug_unique')],
        ]);
    }

    expect($slugRejected)->toBeTrue();

    try {
        UpdateKnowledgeBaseCollectionAction::run($collection, new UpdateKnowledgeBaseCollectionData(
            title: 'Public Docs',
            slug: 'public-docs-updated',
            key: $otherCollection->key,
        ));
    } catch (ValidationException $validationException) {
        expect($validationException->errors())->toBe([
            'key' => [__('capell-knowledge-base::generic.validation.collection_key_unique')],
        ]);

        return;
    }

    Assert::fail('Expected duplicate collection key validation to fail.');
});

it('records redacted feedback and related article links', function (): void {
    $collection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Troubleshooting',
    ));
    $article = CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $collection,
        title: 'Fix Cache',
        body: '<p>Clear cache safely.</p>',
        status: KnowledgeBaseArticleStatus::Published,
    ));
    $relatedArticle = CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $collection,
        title: 'Check Logs',
        body: '<p>Read logs safely.</p>',
        status: KnowledgeBaseArticleStatus::Published,
    ));

    RelateKnowledgeBaseArticlesAction::run($article, $relatedArticle, sortOrder: 10);

    $feedback = RecordKnowledgeBaseArticleFeedbackAction::run(new RecordKnowledgeBaseArticleFeedbackData(
        article: $article,
        helpful: false,
        comment: 'Needs a log example.',
        visitorIdentifier: '192.0.2.10',
        userAgent: 'Example Browser',
    ));

    $publicArticle = BuildPublicKnowledgeBaseArticleDataAction::run($article->refresh());

    expect($feedback)->toBeInstanceOf(KnowledgeBaseArticleFeedback::class)
        ->and($feedback->visitor_hash)->not->toBe('192.0.2.10')
        ->and($feedback->user_agent_hash)->not->toBe('Example Browser')
        ->and($feedback->visitor_hash)->toHaveLength(64)
        ->and($feedback->user_agent_hash)->toHaveLength(64)
        ->and($publicArticle?->feedbackCount)->toBe(1)
        ->and($publicArticle?->helpfulFeedbackCount)->toBe(0)
        ->and($publicArticle?->helpfulFeedbackPercentage)->toBe(0)
        ->and($publicArticle?->relatedArticles)->toHaveCount(1)
        ->and($publicArticle?->relatedArticles[0]->title)->toBe('Check Logs')
        ->and($publicArticle?->relatedArticles[0]->body)->toBe('');
});

it('deduplicates repeat feedback from the same visitor for the same article version', function (): void {
    $collection = CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
        title: 'Troubleshooting',
    ));
    $article = CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $collection,
        title: 'Fix Cache',
        body: '<p>Clear cache safely.</p>',
        status: KnowledgeBaseArticleStatus::Published,
    ));

    $firstFeedback = RecordKnowledgeBaseArticleFeedbackAction::run(new RecordKnowledgeBaseArticleFeedbackData(
        article: $article,
        helpful: false,
        comment: 'Needs more detail.',
        visitorIdentifier: '192.0.2.10',
        userAgent: 'Example Browser',
    ));

    $secondFeedback = RecordKnowledgeBaseArticleFeedbackAction::run(new RecordKnowledgeBaseArticleFeedbackData(
        article: $article,
        helpful: true,
        comment: 'The update helped.',
        visitorIdentifier: '192.0.2.10',
        userAgent: 'Example Browser',
    ));

    expect($secondFeedback->getKey())->toBe($firstFeedback->getKey())
        ->and(KnowledgeBaseArticleFeedback::query()->count())->toBe(1)
        ->and($secondFeedback->helpful)->toBeTrue()
        ->and($secondFeedback->comment)->toBe('The update helped.');
});
