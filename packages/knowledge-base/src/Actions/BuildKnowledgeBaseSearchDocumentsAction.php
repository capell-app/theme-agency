<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Actions;

use Capell\KnowledgeBase\Data\KnowledgeBaseSearchDocumentData;
use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleVersion;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Capell\KnowledgeBase\Support\KnowledgeBasePublicPath;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;
use RuntimeException;

/**
 * @method static Collection<int, KnowledgeBaseSearchDocumentData> run()
 */
final class BuildKnowledgeBaseSearchDocumentsAction
{
    use AsObject;

    /**
     * @return Collection<int, KnowledgeBaseSearchDocumentData>
     */
    public function handle(): Collection
    {
        return KnowledgeBaseArticle::query()
            ->where('status', KnowledgeBaseArticleStatus::Published->value)
            ->whereNotNull('published_at')
            ->whereNotNull('current_version_id')
            ->whereHas('collection', fn (Builder $query): Builder => $query->where('is_public', true))
            ->with(['collection', 'currentVersion'])
            ->orderByDesc('search_weight')
            ->orderBy('title')
            ->get()
            ->filter(fn (KnowledgeBaseArticle $article): bool => $article->collection instanceof KnowledgeBaseCollection && $article->currentVersion instanceof KnowledgeBaseArticleVersion)
            ->map(function (KnowledgeBaseArticle $article): KnowledgeBaseSearchDocumentData {
                $currentVersion = $article->currentVersion;

                throw_unless($currentVersion instanceof KnowledgeBaseArticleVersion, RuntimeException::class, 'Published knowledge base article is missing its current version.');

                return new KnowledgeBaseSearchDocumentData(
                    title: $currentVersion->title,
                    publicPath: KnowledgeBasePublicPath::forArticle($article),
                    summary: $currentVersion->summary,
                    body: strip_tags($currentVersion->body),
                    weight: $article->search_weight,
                    lastModified: $currentVersion->published_at ?? $article->published_at,
                );
            });
    }
}
