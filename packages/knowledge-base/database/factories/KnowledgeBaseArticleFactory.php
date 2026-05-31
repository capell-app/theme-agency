<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Database\Factories;

use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<KnowledgeBaseArticle>
 */
final class KnowledgeBaseArticleFactory extends Factory
{
    protected $model = KnowledgeBaseArticle::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(4);

        return [
            'collection_id' => KnowledgeBaseCollection::factory(),
            'current_version_id' => null,
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => $this->faker->sentence(),
            'status' => KnowledgeBaseArticleStatus::Draft,
            'search_weight' => 50,
            'is_ai_readable' => true,
            'published_at' => null,
        ];
    }

    public function published(): self
    {
        return $this->state([
            'status' => KnowledgeBaseArticleStatus::Published,
            'published_at' => now(),
        ]);
    }
}
