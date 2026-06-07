<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Actions;

use Capell\KnowledgeBase\Data\PublicKnowledgeBaseArticleData;
use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleVersion;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Capell\KnowledgeBase\Models\KnowledgeBaseRelatedArticle;
use Capell\KnowledgeBase\Support\KnowledgeBasePublicPath;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * @method static PublicKnowledgeBaseArticleData|null run(KnowledgeBaseArticle $article)
 */
final class BuildPublicKnowledgeBaseArticleDataAction
{
    use AsObject;

    public function handle(KnowledgeBaseArticle $article): ?PublicKnowledgeBaseArticleData
    {
        $article->loadMissing([
            'collection',
            'currentVersion',
            'relatedArticleLinks.relatedArticle.collection',
            'relatedArticleLinks.relatedArticle.currentVersion',
        ]);

        $collection = $article->collection;
        $currentVersion = $article->currentVersion;

        if (! $collection instanceof KnowledgeBaseCollection || ! $currentVersion instanceof KnowledgeBaseArticleVersion) {
            return null;
        }

        if (! $article->status->isPubliclyVisible() || ! $collection->is_public) {
            return null;
        }

        $feedbackCount = $this->feedbackCount($article);
        $helpfulFeedbackCount = $this->helpfulFeedbackCount($article);

        return new PublicKnowledgeBaseArticleData(
            title: $currentVersion->title,
            slug: $article->slug,
            collectionTitle: $collection->title,
            collectionSlug: $collection->slug,
            publicPath: KnowledgeBasePublicPath::forArticle($article),
            summary: $currentVersion->summary,
            body: SanitizeKnowledgeBaseArticleHtmlAction::run($currentVersion->body),
            version: $currentVersion->version,
            lastModified: $currentVersion->published_at ?? $article->published_at,
            feedbackCount: $feedbackCount,
            helpfulFeedbackCount: $helpfulFeedbackCount,
            helpfulFeedbackPercentage: $this->helpfulFeedbackPercentage($feedbackCount, $helpfulFeedbackCount),
            relatedArticles: $this->relatedArticles($article),
        );
    }

    /**
     * @return list<PublicKnowledgeBaseArticleData>
     */
    private function relatedArticles(KnowledgeBaseArticle $article): array
    {
        return array_values($article->relatedArticleLinks
            ->sortBy('sort_order')
            ->map(function (KnowledgeBaseRelatedArticle $relatedArticleLink): ?PublicKnowledgeBaseArticleData {
                $relatedArticle = $relatedArticleLink->relatedArticle;

                if (! $relatedArticle instanceof KnowledgeBaseArticle) {
                    return null;
                }

                $collection = $relatedArticle->collection;
                $currentVersion = $relatedArticle->currentVersion;

                if ($relatedArticle->status !== KnowledgeBaseArticleStatus::Published || ! $currentVersion instanceof KnowledgeBaseArticleVersion) {
                    return null;
                }

                if (! $collection instanceof KnowledgeBaseCollection || ! $collection->is_public) {
                    return null;
                }

                return new PublicKnowledgeBaseArticleData(
                    title: $currentVersion->title,
                    slug: $relatedArticle->slug,
                    collectionTitle: $collection->title,
                    collectionSlug: $collection->slug,
                    publicPath: KnowledgeBasePublicPath::forArticle($relatedArticle),
                    summary: $currentVersion->summary,
                    body: '',
                    version: $currentVersion->version,
                    lastModified: $currentVersion->published_at ?? $relatedArticle->published_at,
                    relatedArticles: [],
                );
            })
            ->filter(static fn (?PublicKnowledgeBaseArticleData $articleData): bool => $articleData instanceof PublicKnowledgeBaseArticleData)
            ->values()
            ->all());
    }

    private function feedbackCount(KnowledgeBaseArticle $article): int
    {
        return $article->feedback()->count();
    }

    private function helpfulFeedbackCount(KnowledgeBaseArticle $article): int
    {
        return $article->feedback()->where('helpful', true)->count();
    }

    private function helpfulFeedbackPercentage(int $feedbackCount, int $helpfulFeedbackCount): ?int
    {
        if ($feedbackCount === 0) {
            return null;
        }

        return (int) round(($helpfulFeedbackCount / $feedbackCount) * 100);
    }
}
