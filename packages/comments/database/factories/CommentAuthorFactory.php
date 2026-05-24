<?php

declare(strict_types=1);

namespace Capell\Comments\Database\Factories;

use Capell\Comments\Models\CommentAuthor;
use Capell\Core\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommentAuthor>
 */
class CommentAuthorFactory extends Factory
{
    protected $model = CommentAuthor::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $email = $this->faker->unique()->safeEmail();

        return [
            'site_id' => Site::factory(),
            'name' => $this->faker->name(),
            'email' => $email,
            'email_hash' => CommentAuthor::emailHash($email),
            'email_verified_at' => now(),
            'trusted_at' => null,
            'blocked_at' => null,
            'internal_notes' => null,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (): array => [
            'email_verified_at' => null,
        ]);
    }

    public function trusted(): static
    {
        return $this->state(fn (): array => [
            'trusted_at' => now(),
        ]);
    }

    public function blocked(): static
    {
        return $this->state(fn (): array => [
            'blocked_at' => now(),
        ]);
    }
}
