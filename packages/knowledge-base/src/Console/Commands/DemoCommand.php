<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Console\Commands;

use Capell\KnowledgeBase\Actions\CreateKnowledgeBaseArticleAction;
use Capell\KnowledgeBase\Actions\CreateKnowledgeBaseCollectionAction;
use Capell\KnowledgeBase\Actions\RecordKnowledgeBaseArticleFeedbackAction;
use Capell\KnowledgeBase\Actions\RelateKnowledgeBaseArticlesAction;
use Capell\KnowledgeBase\Data\CreateKnowledgeBaseArticleData;
use Capell\KnowledgeBase\Data\CreateKnowledgeBaseCollectionData;
use Capell\KnowledgeBase\Data\RecordKnowledgeBaseArticleFeedbackData;
use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Enums\KnowledgeBaseRelatedArticleType;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Illuminate\Console\Command;

final class DemoCommand extends Command
{
    protected $signature = 'capell:knowledge-base-demo';

    protected $description = 'Seed Knowledge Base demo collections, articles, relations, and feedback.';

    public function handle(): int
    {
        $gettingStarted = $this->collection(
            title: 'Getting Started',
            slug: 'getting-started',
            description: 'Install, configure, and launch Capell with confidence.',
            sortOrder: 10,
        );
        $operations = $this->collection(
            title: 'Operations',
            slug: 'operations',
            description: 'Keep your Capell site healthy after launch.',
            sortOrder: 20,
        );

        $installingCapell = $this->article(
            collection: $gettingStarted,
            title: 'Installing Capell',
            slug: 'installing-capell',
            summary: 'Prepare the environment, install Capell, and verify the first admin login.',
            body: '<h2>Before you start</h2><p>Confirm PHP, database, queue, and storage requirements before installing Capell.</p><h2>Install</h2><p>Run the installer, create the first admin account, and open the admin dashboard.</p>',
            searchWeight: 100,
        );
        $cacheChecklist = $this->article(
            collection: $operations,
            title: 'Cache checklist',
            slug: 'cache-checklist',
            summary: 'Review the cache, queue, and invalidation checks that keep public pages fresh.',
            body: '<h2>Review cache health</h2><p>Check frontend cache tags, queue workers, and model-event invalidation before launch.</p>',
            searchWeight: 80,
        );

        RelateKnowledgeBaseArticlesAction::run(
            article: $installingCapell,
            relatedArticle: $cacheChecklist,
            relationType: KnowledgeBaseRelatedArticleType::NextStep,
            sortOrder: 10,
        );

        RecordKnowledgeBaseArticleFeedbackAction::run(new RecordKnowledgeBaseArticleFeedbackData(
            article: $installingCapell,
            helpful: true,
            comment: 'Clear installation checklist.',
            visitorIdentifier: 'knowledge-base-demo-positive',
            userAgent: 'Capell Knowledge Base Demo',
        ));
        RecordKnowledgeBaseArticleFeedbackAction::run(new RecordKnowledgeBaseArticleFeedbackData(
            article: $installingCapell,
            helpful: false,
            comment: 'Add deployment screenshots.',
            visitorIdentifier: 'knowledge-base-demo-negative',
            userAgent: 'Capell Knowledge Base Demo',
        ));

        $this->components->info('Knowledge Base demo content is ready.');

        return self::SUCCESS;
    }

    private function collection(string $title, string $slug, string $description, int $sortOrder): KnowledgeBaseCollection
    {
        $collection = KnowledgeBaseCollection::query()->where('slug', $slug)->first();

        if ($collection instanceof KnowledgeBaseCollection) {
            return $collection;
        }

        return CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
            title: $title,
            slug: $slug,
            key: $slug,
            description: $description,
            sortOrder: $sortOrder,
        ));
    }

    private function article(
        KnowledgeBaseCollection $collection,
        string $title,
        string $slug,
        string $summary,
        string $body,
        int $searchWeight,
    ): KnowledgeBaseArticle {
        $article = KnowledgeBaseArticle::query()
            ->whereBelongsTo($collection, 'collection')
            ->where('slug', $slug)
            ->first();

        if ($article instanceof KnowledgeBaseArticle) {
            return $article;
        }

        return CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
            collection: $collection,
            title: $title,
            body: $body,
            slug: $slug,
            summary: $summary,
            status: KnowledgeBaseArticleStatus::Published,
            searchWeight: $searchWeight,
        ));
    }
}
