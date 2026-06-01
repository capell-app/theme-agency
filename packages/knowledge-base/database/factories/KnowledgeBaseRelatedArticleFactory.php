<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Database\Factories;

use Capell\KnowledgeBase\Enums\KnowledgeBaseRelatedArticleType;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseRelatedArticle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KnowledgeBaseRelatedArticle>
 */
final class KnowledgeBaseRelatedArticleFactory extends Factory
{
    protected $model = KnowledgeBaseRelatedArticle::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_id' => KnowledgeBaseArticle::factory(),
            'related_article_id' => KnowledgeBaseArticle::factory(),
            'relation_type' => KnowledgeBaseRelatedArticleType::Related,
            'sort_order' => 0,
        ];
    }
}
