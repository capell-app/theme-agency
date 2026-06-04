<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Database\Factories;

use Capell\DocumentLifecycle\Enums\DocumentStatusEnum;
use Capell\DocumentLifecycle\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'key' => Str::slug($title, '_'),
            'title' => Str::headline($title),
            'status' => DocumentStatusEnum::Draft,
            'metadata' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => DocumentStatusEnum::Draft]);
    }

    public function active(): static
    {
        return $this->state(['status' => DocumentStatusEnum::Active]);
    }

    public function archived(): static
    {
        return $this->state(['status' => DocumentStatusEnum::Archived]);
    }

    public function documentable(Model $documentable): static
    {
        return $this->state([
            'documentable_type' => $documentable->getMorphClass(),
            'documentable_id' => $documentable->getKey(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function metadata(array $metadata): static
    {
        return $this->state(['metadata' => $metadata]);
    }
}
