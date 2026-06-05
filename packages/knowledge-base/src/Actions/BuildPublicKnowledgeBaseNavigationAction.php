<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Actions;

use Capell\KnowledgeBase\Data\PublicKnowledgeBaseNavigationItemData;
use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Capell\KnowledgeBase\Support\KnowledgeBasePublicPath;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * @method static Collection<int, PublicKnowledgeBaseNavigationItemData> run()
 */
final class BuildPublicKnowledgeBaseNavigationAction
{
    use AsObject;

    /**
     * @return Collection<int, PublicKnowledgeBaseNavigationItemData>
     */
    public function handle(): Collection
    {
        return KnowledgeBaseCollection::query()
            ->public()
            ->whereHas('articles', function (Builder $query): void {
                $query->where('status', KnowledgeBaseArticleStatus::Published->value)
                    ->whereNotNull('published_at')
                    ->whereNotNull('current_version_id');
            })
            ->with(['articles' => function (Relation $query): void {
                $query->where('status', KnowledgeBaseArticleStatus::Published->value)
                    ->whereNotNull('published_at')
                    ->whereNotNull('current_version_id')
                    ->with('currentVersion')
                    ->orderBy('title');
            }])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn (KnowledgeBaseCollection $collection): PublicKnowledgeBaseNavigationItemData => new PublicKnowledgeBaseNavigationItemData(
                title: $collection->title,
                slug: $collection->slug,
                description: $collection->description,
                articles: array_values($collection->articles
                    ->map(fn (KnowledgeBaseArticle $article): array => [
                        'title' => $article->title,
                        'slug' => $article->slug,
                        'publicPath' => KnowledgeBasePublicPath::forArticle($article),
                        'summary' => $article->summary,
                    ])
                    ->values()
                    ->all()),
            ));
    }
}
