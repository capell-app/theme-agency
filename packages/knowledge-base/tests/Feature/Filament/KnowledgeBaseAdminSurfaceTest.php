<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\RegistersExtensionRoute;
use Capell\KnowledgeBase\Actions\BuildAiReadableKnowledgeBaseOutputAction;
use Capell\KnowledgeBase\Actions\BuildKnowledgeBaseSearchDocumentsAction;
use Capell\KnowledgeBase\Actions\CreateKnowledgeBaseArticleAction;
use Capell\KnowledgeBase\Data\CreateKnowledgeBaseArticleData;
use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Enums\ResourceEnum;
use Capell\KnowledgeBase\Filament\Resources\Articles\KnowledgeBaseArticleResource;
use Capell\KnowledgeBase\Filament\Resources\Articles\Pages\CreateKnowledgeBaseArticle;
use Capell\KnowledgeBase\Filament\Resources\Articles\Pages\EditKnowledgeBaseArticle;
use Capell\KnowledgeBase\Filament\Resources\Articles\Pages\ListKnowledgeBaseArticles;
use Capell\KnowledgeBase\Filament\Resources\Articles\RelationManagers\ArticleVersionsRelationManager;
use Capell\KnowledgeBase\Filament\Resources\Articles\RelationManagers\RelatedArticlesRelationManager;
use Capell\KnowledgeBase\Filament\Resources\Collections\KnowledgeBaseCollectionResource;
use Capell\KnowledgeBase\Filament\Resources\Collections\Pages\CreateKnowledgeBaseCollection;
use Capell\KnowledgeBase\Filament\Resources\Collections\Pages\EditKnowledgeBaseCollection;
use Capell\KnowledgeBase\Filament\Resources\Collections\Pages\ListKnowledgeBaseCollections;
use Capell\KnowledgeBase\Manifest\KnowledgeBaseArticleResourceContribution;
use Capell\KnowledgeBase\Manifest\KnowledgeBaseCollectionResourceContribution;
use Capell\KnowledgeBase\Manifest\KnowledgeBaseFrontendRoutesContribution;
use Capell\KnowledgeBase\Manifest\KnowledgeBaseModelsContribution;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleVersion;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Capell\KnowledgeBase\Providers\AdminServiceProvider;
use Capell\KnowledgeBase\Tests\KnowledgeBaseTestCase;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
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
        ->and(array_keys($collectionPages))->toBe(['index', 'create', 'edit'])
        ->and($collectionPages['index']->getPage())->toBe(ListKnowledgeBaseCollections::class)
        ->and($collectionPages['create']->getPage())->toBe(CreateKnowledgeBaseCollection::class)
        ->and($collectionPages['edit']->getPage())->toBe(EditKnowledgeBaseCollection::class)
        ->and(KnowledgeBaseArticleResource::getModel())->toBe(KnowledgeBaseArticle::class)
        ->and(KnowledgeBaseArticleResource::shouldRegisterNavigation())->toBeTrue()
        ->and(KnowledgeBaseArticleResource::getNavigationGroup())->toBe('Knowledge base')
        ->and(KnowledgeBaseArticleResource::getNavigationLabel())->toBe('Articles')
        ->and(KnowledgeBaseArticleResource::getModelLabel())->toBe('knowledge base article')
        ->and(array_keys($articlePages))->toBe(['index', 'create', 'edit'])
        ->and($articlePages['index']->getPage())->toBe(ListKnowledgeBaseArticles::class)
        ->and($articlePages['create']->getPage())->toBe(CreateKnowledgeBaseArticle::class)
        ->and($articlePages['edit']->getPage())->toBe(EditKnowledgeBaseArticle::class)
        ->and(KnowledgeBaseArticleResource::getRelations())->toBe([
            ArticleVersionsRelationManager::class,
            RelatedArticlesRelationManager::class,
        ])
        ->and(ResourceEnum::Collections->value)->toBe(KnowledgeBaseCollectionResource::class)
        ->and(ResourceEnum::Articles->value)->toBe(KnowledgeBaseArticleResource::class);
});

it('declares admin providers, resources, and owned tables in the manifest', function (): void {
    $manifest = json_decode(
        File::get(__DIR__ . '/../../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['dependencies']['requires'])->toContain('capell-app/admin', 'capell-app/core', 'capell-app/frontend')
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
        ->and($manifest['performance']['cacheSafety']['variesBy'])->toBe([])
        ->and(array_column($manifest['performance']['cacheSafety']['invalidationSources'], 'model'))->toBe([
            KnowledgeBaseCollection::class,
            KnowledgeBaseArticle::class,
            KnowledgeBaseArticleVersion::class,
        ])
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

it('builds knowledge base resource forms and tables with configured controls', function (): void {
    expect(knowledgeBaseAdminFormComponentClasses(KnowledgeBaseCollectionResource::form(Schema::make())))->toBe([
        TextInput::class,
        TextInput::class,
        TextInput::class,
        Select::class,
        Textarea::class,
        TextInput::class,
        Select::class,
    ])
        ->and(knowledgeBaseAdminFormComponentClasses(KnowledgeBaseArticleResource::form(Schema::make())))->toBe([
            Select::class,
            Select::class,
            TextInput::class,
            TextInput::class,
            Textarea::class,
            Textarea::class,
            TextInput::class,
            TextInput::class,
            Select::class,
        ])
        ->and(array_keys(KnowledgeBaseCollectionResource::table(knowledgeBaseAdminTableForCoverage())->getColumns()))->toBe([
            'title',
            'slug',
            'is_public',
            'sort_order',
            'articles_count',
        ])
        ->and(array_map(
            static fn (object $column): string => $column::class,
            array_values(KnowledgeBaseCollectionResource::table(knowledgeBaseAdminTableForCoverage())->getColumns()),
        ))->toBe([
            TextColumn::class,
            TextColumn::class,
            IconColumn::class,
            TextColumn::class,
            TextColumn::class,
        ])
        ->and(array_keys(KnowledgeBaseArticleResource::table(knowledgeBaseAdminTableForCoverage())->getColumns()))->toBe([
            'title',
            'collection.title',
            'status',
            'is_ai_readable',
            'search_weight',
            'feedback_count',
            'helpful_feedback_rate',
            'published_at',
        ])
        ->and(array_keys(KnowledgeBaseArticleResource::table(knowledgeBaseAdminTableForCoverage())->getFilters()))->toBe([
            'status',
            'collection_id',
        ])
        ->and(KnowledgeBaseArticleResource::table(knowledgeBaseAdminTableForCoverage())->getFilters()['status'])->toBeInstanceOf(SelectFilter::class)
        ->and(KnowledgeBaseArticleStatus::Archived->getLabel())->toBe(__('capell-knowledge-base::generic.article_status.archived'));
});

it('exposes article version history in the article edit surface', function (): void {
    $relationManager = new ArticleVersionsRelationManager;
    $table = $relationManager->table(knowledgeBaseAdminTableForCoverage());

    expect(ArticleVersionsRelationManager::getTitle(KnowledgeBaseArticle::factory()->make(), EditKnowledgeBaseArticle::class))
        ->toBe(__('capell-knowledge-base::generic.admin.relations.versions'))
        ->and(array_keys($table->getColumns()))->toBe([
            'version',
            'title',
            'current_version',
            'published_at',
            'created_at',
        ])
        ->and(array_map(
            static fn (object $column): string => $column::class,
            array_values($table->getColumns()),
        ))->toBe([
            TextColumn::class,
            TextColumn::class,
            IconColumn::class,
            TextColumn::class,
            TextColumn::class,
        ]);
});

it('exposes related article editing in the article edit surface', function (): void {
    $relationManager = new RelatedArticlesRelationManager;
    $table = $relationManager->table(knowledgeBaseAdminTableForCoverage());

    expect(RelatedArticlesRelationManager::getTitle(KnowledgeBaseArticle::factory()->make(), EditKnowledgeBaseArticle::class))
        ->toBe(__('capell-knowledge-base::generic.admin.relations.related_articles'))
        ->and(array_keys($table->getColumns()))->toBe([
            'relatedArticle.title',
            'relatedArticle.collection.title',
            'relation_type',
            'sort_order',
            'updated_at',
        ])
        ->and(array_map(
            static fn (object $column): string => $column::class,
            array_values($table->getColumns()),
        ))->toBe([
            TextColumn::class,
            TextColumn::class,
            TextColumn::class,
            TextColumn::class,
            TextColumn::class,
        ])
        ->and(array_keys($table->getHeaderActions()))->toBe(['relate_article'])
        ->and(array_keys($table->getRecordActions()))->toBe(['update_relation']);
});

it('saves article edits as published versions through the edit page adapter', function (): void {
    $collection = KnowledgeBaseCollection::factory()->create([
        'title' => 'Support Docs',
        'slug' => 'support-docs',
        'key' => 'support-docs',
    ]);

    $article = CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
        collection: $collection,
        title: 'Install Capell',
        body: '<p>Install v1.</p>',
        summary: 'Initial install guide.',
        status: KnowledgeBaseArticleStatus::Published,
    ));

    $updated = knowledgeBaseInvokeEditRecordUpdate(new EditKnowledgeBaseArticle, $article, [
        'collection_id' => $collection->getKey(),
        'status' => KnowledgeBaseArticleStatus::Published->value,
        'title' => 'Install Capell Updated',
        'slug' => 'install-capell-updated',
        'summary' => 'Updated install guide.',
        'body' => '<p>Install v2.</p>',
        'version' => 'v2',
        'search_weight' => 120,
        'is_ai_readable' => '0',
    ]);

    $article->refresh()->load('currentVersion');

    expect($updated)->toBeInstanceOf(KnowledgeBaseArticle::class)
        ->and($article->title)->toBe('Install Capell Updated')
        ->and($article->slug)->toBe('install-capell-updated')
        ->and($article->status)->toBe(KnowledgeBaseArticleStatus::Published)
        ->and($article->search_weight)->toBe(120)
        ->and($article->is_ai_readable)->toBeFalse()
        ->and($article->versions()->count())->toBe(2)
        ->and($article->currentVersion?->version)->toBe('v2')
        ->and($article->currentVersion?->body)->toBe('<p>Install v2.</p>')
        ->and($article->currentVersion?->published_at)->not->toBeNull();
});

it('builds knowledge base list page create actions with translated labels', function (): void {
    $collectionActions = knowledgeBaseAdminListPageHeaderActions(new ListKnowledgeBaseCollections);
    $articleActions = knowledgeBaseAdminListPageHeaderActions(new ListKnowledgeBaseArticles);

    expect($collectionActions)->toHaveCount(1)
        ->and($collectionActions[0])->toBeInstanceOf(CreateAction::class)
        ->and($collectionActions[0]->getName())->toBe('create')
        ->and($collectionActions[0]->getLabel())->toBe(__('capell-knowledge-base::generic.admin.actions.create_collection'))
        ->and($articleActions)->toHaveCount(1)
        ->and($articleActions[0])->toBeInstanceOf(CreateAction::class)
        ->and($articleActions[0]->getName())->toBe('create')
        ->and($articleActions[0]->getLabel())->toBe(__('capell-knowledge-base::generic.admin.actions.create_article'));
});

/**
 * @return list<class-string>
 */
function knowledgeBaseAdminFormComponentClasses(Schema $schema): array
{
    $components = $schema->getComponents();

    expect($components)->toHaveCount(1)
        ->and($components[0])->toBeInstanceOf(Section::class);

    return array_map(
        static fn (object $component): string => $component::class,
        knowledgeBaseAdminChildComponents($components[0]),
    );
}

/**
 * @return list<object>
 */
function knowledgeBaseAdminChildComponents(object $component): array
{
    if (method_exists($component, 'getDefaultChildComponents')) {
        $components = $component->getDefaultChildComponents();

        return is_array($components) ? array_values($components) : [];
    }

    $reflectionProperty = new ReflectionProperty($component, 'childComponents');
    $childComponents = $reflectionProperty->getValue($component);

    return array_values($childComponents['default'] ?? []);
}

function knowledgeBaseAdminTableForCoverage(): Table
{
    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldIgnoreMissing();
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null)->byDefault();
    $livewire->shouldReceive('getTableFilterState')->andReturn([])->byDefault();
    $livewire->shouldReceive('isTableLoaded')->andReturnTrue()->byDefault();
    $livewire->shouldReceive('getTableArguments')->andReturn([])->byDefault();

    return Table::make($livewire);
}

/**
 * @param  array<string, mixed>  $data
 */
function knowledgeBaseInvokeEditRecordUpdate(object $page, KnowledgeBaseArticle $article, array $data): Model
{
    $reflectionMethod = new ReflectionMethod($page, 'handleRecordUpdate');

    $updated = $reflectionMethod->invoke($page, $article, $data);

    expect($updated)->toBeInstanceOf(Model::class);
    throw_unless($updated instanceof Model, RuntimeException::class, 'Expected edit record update to return a model.');

    return $updated;
}

/**
 * @return array<int, CreateAction>
 */
function knowledgeBaseAdminListPageHeaderActions(object $page): array
{
    $reflectionMethod = new ReflectionMethod($page, 'getHeaderActions');

    return $reflectionMethod->invoke($page);
}
