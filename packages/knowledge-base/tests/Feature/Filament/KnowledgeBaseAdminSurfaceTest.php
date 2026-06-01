<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\RegistersExtensionRoute;
use Capell\KnowledgeBase\Actions\BuildAiReadableKnowledgeBaseOutputAction;
use Capell\KnowledgeBase\Actions\BuildKnowledgeBaseSearchDocumentsAction;
use Capell\KnowledgeBase\Enums\ResourceEnum;
use Capell\KnowledgeBase\Filament\Resources\Articles\KnowledgeBaseArticleResource;
use Capell\KnowledgeBase\Filament\Resources\Articles\Pages\CreateKnowledgeBaseArticle;
use Capell\KnowledgeBase\Filament\Resources\Articles\Pages\ListKnowledgeBaseArticles;
use Capell\KnowledgeBase\Filament\Resources\Collections\KnowledgeBaseCollectionResource;
use Capell\KnowledgeBase\Filament\Resources\Collections\Pages\CreateKnowledgeBaseCollection;
use Capell\KnowledgeBase\Filament\Resources\Collections\Pages\ListKnowledgeBaseCollections;
use Capell\KnowledgeBase\Manifest\KnowledgeBaseArticleResourceContribution;
use Capell\KnowledgeBase\Manifest\KnowledgeBaseCollectionResourceContribution;
use Capell\KnowledgeBase\Manifest\KnowledgeBaseFrontendRoutesContribution;
use Capell\KnowledgeBase\Manifest\KnowledgeBaseModelsContribution;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Capell\KnowledgeBase\Policies\KnowledgeBaseArticlePolicy;
use Capell\KnowledgeBase\Policies\KnowledgeBaseCollectionPolicy;
use Capell\KnowledgeBase\Providers\AdminServiceProvider;
use Capell\KnowledgeBase\Tests\KnowledgeBaseTestCase;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Support\Facades\File;

require_once dirname(__DIR__, 2) . '/KnowledgeBaseTestCase.php';

uses(KnowledgeBaseTestCase::class);

it('exposes translated collection and article admin resources', function (): void {
    $collectionPages = KnowledgeBaseCollectionResource::getPages();
    $articlePages = KnowledgeBaseArticleResource::getPages();

    expect(KnowledgeBaseCollectionResource::getModel())->toBe(KnowledgeBaseCollection::class)
        ->and(KnowledgeBaseCollectionResource::shouldRegisterNavigation())->toBeTrue()
        ->and(KnowledgeBaseCollectionResource::getNavigationGroup())->toBe('Knowledge base')
        ->and(KnowledgeBaseCollectionResource::getNavigationLabel())->toBe('Collections')
        ->and(KnowledgeBaseCollectionResource::getModelLabel())->toBe('knowledge base collection')
        ->and(array_keys($collectionPages))->toBe(['index', 'create'])
        ->and($collectionPages['index']->getPage())->toBe(ListKnowledgeBaseCollections::class)
        ->and($collectionPages['create']->getPage())->toBe(CreateKnowledgeBaseCollection::class)
        ->and(KnowledgeBaseArticleResource::getModel())->toBe(KnowledgeBaseArticle::class)
        ->and(KnowledgeBaseArticleResource::shouldRegisterNavigation())->toBeTrue()
        ->and(KnowledgeBaseArticleResource::getNavigationGroup())->toBe('Knowledge base')
        ->and(KnowledgeBaseArticleResource::getNavigationLabel())->toBe('Articles')
        ->and(KnowledgeBaseArticleResource::getModelLabel())->toBe('knowledge base article')
        ->and(array_keys($articlePages))->toBe(['index', 'create'])
        ->and($articlePages['index']->getPage())->toBe(ListKnowledgeBaseArticles::class)
        ->and($articlePages['create']->getPage())->toBe(CreateKnowledgeBaseArticle::class)
        ->and(ResourceEnum::Collections->value)->toBe(KnowledgeBaseCollectionResource::class)
        ->and(ResourceEnum::Articles->value)->toBe(KnowledgeBaseArticleResource::class);
});

it('declares admin providers, resources, and owned tables in the manifest', function (): void {
    $manifest = json_decode(
        File::get(__DIR__ . '/../../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['dependencies']['requires'])->toContain('capell-app/admin', 'capell-app/core')
        ->and($manifest['providers']['admin'])->toContain(AdminServiceProvider::class)
        ->and($manifest['database']['requiredTables'])->toBe([
            'knowledge_base_collections',
            'knowledge_base_articles',
            'knowledge_base_article_versions',
            'knowledge_base_article_feedback',
            'knowledge_base_related_articles',
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'key' => 'knowledge-base.collections',
            'class' => KnowledgeBaseCollectionResourceContribution::class,
            'resourceClass' => KnowledgeBaseCollectionResource::class,
            'group' => 'KnowledgeBaseCollection',
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'admin-resource',
            'key' => 'knowledge-base.articles',
            'class' => KnowledgeBaseArticleResourceContribution::class,
            'resourceClass' => KnowledgeBaseArticleResource::class,
            'group' => 'KnowledgeBaseArticle',
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'model',
            'class' => KnowledgeBaseModelsContribution::class,
        ])
        ->and($manifest['contributes'])->toContain([
            'type' => 'route',
            'class' => KnowledgeBaseFrontendRoutesContribution::class,
        ])
        ->and(class_implements(KnowledgeBaseFrontendRoutesContribution::class))->toContain(RegistersExtensionRoute::class)
        ->and($manifest['actions']['buildKnowledgeBaseSearchDocuments'])
        ->toBe(BuildKnowledgeBaseSearchDocumentsAction::class)
        ->and($manifest['actions']['buildAiReadableKnowledgeBaseOutput'])
        ->toBe(BuildAiReadableKnowledgeBaseOutputAction::class)
        ->and($manifest['capabilities'])->toContain(
            'knowledge-base-related-articles',
            'knowledge-base-versioned-articles',
            'knowledge-base-public-navigation',
            'knowledge-base-search-weighting',
            'knowledge-base-ai-output',
        )
        ->and($manifest['contributionTraceability']['deferredContributions'])->not->toContain(
            'ai-discovery-output',
            'migration',
            'model',
            'search-index',
        )
        ->and($manifest['contributionTraceability']['deferredContributions'])->toBe([]);
});

it('allows admin authoring but keeps destructive deletes disabled by default', function (): void {
    $user = new class implements AuthenticatableContract
    {
        use Authenticatable;
    };

    $collection = KnowledgeBaseCollection::factory()->create();
    $article = KnowledgeBaseArticle::factory()->create([
        'collection_id' => $collection->getKey(),
    ]);

    $collectionPolicy = new KnowledgeBaseCollectionPolicy;
    $articlePolicy = new KnowledgeBaseArticlePolicy;

    expect($collectionPolicy->viewAny())->toBeTrue()
        ->and($collectionPolicy->create())->toBeTrue()
        ->and($collectionPolicy->update())->toBeTrue()
        ->and($collectionPolicy->delete())->toBeFalse()
        ->and($articlePolicy->viewAny())->toBeTrue()
        ->and($articlePolicy->create())->toBeTrue()
        ->and($articlePolicy->update())->toBeTrue()
        ->and($articlePolicy->delete())->toBeFalse();
});
