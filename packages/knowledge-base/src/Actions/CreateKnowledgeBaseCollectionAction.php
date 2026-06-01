<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Actions;

use Capell\KnowledgeBase\Data\CreateKnowledgeBaseCollectionData;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsObject;

final class CreateKnowledgeBaseCollectionAction
{
    use AsObject;

    public function handle(CreateKnowledgeBaseCollectionData $data): KnowledgeBaseCollection
    {
        $title = trim($data->title);
        $slug = $this->normalizeSlug($data->slug ?? $title);
        $key = $this->normalizeSlug($data->key ?? $slug);

        if ($title === '') {
            throw ValidationException::withMessages([
                'title' => __('capell-knowledge-base::generic.validation.collection_title_required'),
            ]);
        }

        if ($slug === '') {
            throw ValidationException::withMessages([
                'slug' => __('capell-knowledge-base::generic.validation.slug_required'),
            ]);
        }

        return KnowledgeBaseCollection::query()->create([
            'parent_id' => $data->parent?->getKey(),
            'key' => $key,
            'title' => $title,
            'slug' => $slug,
            'description' => $data->description === null ? null : trim($data->description),
            'sort_order' => max(0, $data->sortOrder),
            'is_public' => $data->isPublic,
        ]);
    }

    private function normalizeSlug(string $value): string
    {
        return Str::slug(trim($value));
    }
}
