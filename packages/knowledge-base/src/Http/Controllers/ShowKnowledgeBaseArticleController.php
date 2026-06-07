<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Http\Controllers;

use Capell\KnowledgeBase\Actions\BuildPublicKnowledgeBaseArticleDataAction;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;

final class ShowKnowledgeBaseArticleController
{
    public function __invoke(string $collectionSlug, string $articleSlug): Response
    {
        /** @var KnowledgeBaseArticle|null $article */
        $article = KnowledgeBaseArticle::query()
            ->where('slug', $articleSlug)
            ->whereHas('collection', static function (Builder $query) use ($collectionSlug): void {
                $query->where('slug', $collectionSlug);
            })
            ->with(['collection', 'currentVersion', 'relatedArticleLinks.relatedArticle.collection', 'relatedArticleLinks.relatedArticle.currentVersion'])
            ->first();

        abort_if($article === null, 404);

        $articleData = BuildPublicKnowledgeBaseArticleDataAction::run($article);

        abort_if($articleData === null, 404);

        return $this->cacheable(response()->view($this->viewName(), [
            'article' => $articleData->toArray(),
        ]));
    }

    private function viewName(): string
    {
        return view()->exists('capell-theme-knowledge::knowledge-base.article')
            ? 'capell-theme-knowledge::knowledge-base.article'
            : 'capell-knowledge-base::article';
    }

    private function cacheable(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'public, max-age=300, stale-while-revalidate=300');

        return $response;
    }
}
