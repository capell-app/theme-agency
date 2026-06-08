<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Actions;

use Capell\KnowledgeBase\Data\PublicKnowledgeBaseNavigationItemData;
use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Capell\KnowledgeBase\Support\KnowledgeBasePublicPath;
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
        $collections = KnowledgeBaseCollection::query()
            ->public()
            ->with(['articles' => function (Relation $query): void {
                $query->where('status', KnowledgeBaseArticleStatus::Published->value)
                    ->whereNotNull('published_at')
                    ->whereNotNull('current_version_id')
                    ->with('currentVersion')
                    ->orderBy('title');
            }])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        /** @var Collection<int|string, Collection<int, KnowledgeBaseCollection>> $collectionsByParent */
        $collectionsByParent = $collections->groupBy(
            static fn (KnowledgeBaseCollection $collection): int => $collection->parent_id ?? 0,
        );

        return $this->navigationItems($collectionsByParent, 0);
    }

    /**
     * @param  Collection<int|string, Collection<int, KnowledgeBaseCollection>>  $collectionsByParent
     * @return Collection<int, PublicKnowledgeBaseNavigationItemData>
     */
    private function navigationItems(Collection $collectionsByParent, int $parentId): Collection
    {
        return ($collectionsByParent->get($parentId) ?? collect())
            ->map(fn (KnowledgeBaseCollection $collection): ?PublicKnowledgeBaseNavigationItemData => $this->navigationItem($collectionsByParent, $collection))
            ->filter(static fn (?PublicKnowledgeBaseNavigationItemData $item): bool => $item instanceof PublicKnowledgeBaseNavigationItemData)
            ->values();
    }

    /**
     * @param  Collection<int|string, Collection<int, KnowledgeBaseCollection>>  $collectionsByParent
     */
    private function navigationItem(Collection $collectionsByParent, KnowledgeBaseCollection $collection): ?PublicKnowledgeBaseNavigationItemData
    {
        $articles = $this->articles($collection);
        $children = $this->listFromCollection($this->navigationItems($collectionsByParent, $this->integerKey($collection)));

        if ($articles === [] && $children === []) {
            return null;
        }

        return new PublicKnowledgeBaseNavigationItemData(
            title: $collection->title,
            slug: $collection->slug,
            description: $collection->description,
            articles: $articles,
            children: $children,
        );
    }

    /**
     * @return list<array{title: string, slug: string, publicPath: string, summary: string|null}>
     */
    private function articles(KnowledgeBaseCollection $collection): array
    {
        return array_values($collection->articles
            ->map(fn (KnowledgeBaseArticle $article): array => [
                'title' => $article->title,
                'slug' => $article->slug,
                'publicPath' => KnowledgeBasePublicPath::forArticle($article),
                'summary' => $article->summary,
            ])
            ->values()
            ->all());
    }

    private function integerKey(KnowledgeBaseCollection $collection): int
    {
        $key = $collection->getKey();

        return is_numeric($key) ? (int) $key : 0;
    }

    /**
     * @param  Collection<int, PublicKnowledgeBaseNavigationItemData>  $items
     * @return list<PublicKnowledgeBaseNavigationItemData>
     */
    private function listFromCollection(Collection $items): array
    {
        $list = [];

        foreach ($items->values() as $item) {
            $list[] = $item;
        }

        return $list;
    }
}
