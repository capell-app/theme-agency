<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Database\Factories;

use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<KnowledgeBaseCollection>
 */
final class KnowledgeBaseCollectionFactory extends Factory
{
    protected $model = KnowledgeBaseCollection::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->words(3, true);

        if (is_array($title)) {
            $title = implode(' ', $title);
        }

        return [
            'parent_id' => null,
            'key' => Str::slug($title),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->sentence(),
            'sort_order' => $this->faker->numberBetween(1, 50),
            'is_public' => true,
        ];
    }
}
