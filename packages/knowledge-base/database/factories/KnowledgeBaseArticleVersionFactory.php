<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Database\Factories;

use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KnowledgeBaseArticleVersion>
 */
final class KnowledgeBaseArticleVersionFactory extends Factory
{
    protected $model = KnowledgeBaseArticleVersion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_id' => KnowledgeBaseArticle::factory(),
            'version' => 'v' . $this->faker->unique()->numberBetween(1, 1000),
            'title' => $this->faker->sentence(4),
            'summary' => $this->faker->sentence(),
            'body' => '<p>' . e($this->faker->paragraph()) . '</p>',
            'author_type' => null,
            'author_id' => null,
            'published_at' => null,
        ];
    }

    public function published(): self
    {
        return $this->state([
            'published_at' => now(),
        ]);
    }
}
