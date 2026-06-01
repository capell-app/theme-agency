<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Actions;

use Capell\KnowledgeBase\Enums\KnowledgeBaseRelatedArticleType;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseRelatedArticle;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsObject;

final class RelateKnowledgeBaseArticlesAction
{
    use AsObject;

    public function handle(
        KnowledgeBaseArticle $article,
        KnowledgeBaseArticle $relatedArticle,
        KnowledgeBaseRelatedArticleType $relationType = KnowledgeBaseRelatedArticleType::Related,
        int $sortOrder = 0,
    ): KnowledgeBaseRelatedArticle {
        if ($article->is($relatedArticle)) {
            throw ValidationException::withMessages([
                'related_article_id' => __('capell-knowledge-base::generic.validation.slug_required'),
            ]);
        }

        return KnowledgeBaseRelatedArticle::query()->updateOrCreate([
            'article_id' => $article->getKey(),
            'related_article_id' => $relatedArticle->getKey(),
        ], [
            'relation_type' => $relationType,
            'sort_order' => max(0, $sortOrder),
        ]);
    }
}
