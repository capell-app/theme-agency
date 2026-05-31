<?php

declare(strict_types=1);

namespace Capell\Comments\Database\Factories;

use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Core\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => fn (array $attributes): int => CommentAuthor::query()->findOrFail((int) $attributes['comment_author_id'])->site_id,
            'language_id' => null,
            'comment_author_id' => CommentAuthor::factory(),
            'commentable_type' => 'page',
            'commentable_id' => Page::factory(),
            'parent_id' => null,
            'root_id' => null,
            'depth' => 0,
            'status' => CommentStatus::Approved,
            'body' => $this->faker->sentence(),
            'visitor_ip_hash' => hash('sha256', '127.0.0.1'),
            'visitor_user_agent_hash' => hash('sha256', 'comments-test'),
            'link_count' => 0,
            'spam_reasons' => [],
            'submitted_at' => now(),
            'email_verified_at' => now(),
            'approved_at' => now(),
        ];
    }

    public function pendingApproval(): static
    {
        return $this->state(fn (): array => [
            'status' => CommentStatus::PendingApproval,
            'approved_at' => null,
        ]);
    }

    public function pendingEmailVerification(): static
    {
        return $this->state(fn (): array => [
            'status' => CommentStatus::PendingEmailVerification,
            'approved_at' => null,
            'email_verified_at' => null,
        ]);
    }

    public function spam(): static
    {
        return $this->state(fn (): array => [
            'status' => CommentStatus::Spam,
            'approved_at' => null,
            'marked_spam_at' => now(),
        ]);
    }
}
