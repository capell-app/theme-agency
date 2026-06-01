<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Database\Factories;

use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleFeedback;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KnowledgeBaseArticleFeedback>
 */
final class KnowledgeBaseArticleFeedbackFactory extends Factory
{
    protected $model = KnowledgeBaseArticleFeedback::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_id' => KnowledgeBaseArticle::factory(),
            'article_version_id' => null,
            'helpful' => $this->faker->boolean(),
            'comment' => $this->faker->optional()->sentence(),
            'visitor_hash' => hash('sha256', $this->faker->uuid()),
            'user_agent_hash' => hash('sha256', $this->faker->userAgent()),
            'submitted_at' => now(),
        ];
    }
}
