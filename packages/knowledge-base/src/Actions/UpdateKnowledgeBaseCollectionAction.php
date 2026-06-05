<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Actions;

use Capell\KnowledgeBase\Data\UpdateKnowledgeBaseCollectionData;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsObject;

final class UpdateKnowledgeBaseCollectionAction
{
    use AsObject;

    public function handle(KnowledgeBaseCollection $collection, UpdateKnowledgeBaseCollectionData $data): KnowledgeBaseCollection
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

        if ($this->slugExists($collection, $slug)) {
            throw ValidationException::withMessages([
                'slug' => __('capell-knowledge-base::generic.validation.collection_slug_unique'),
            ]);
        }

        if ($this->keyExists($collection, $key)) {
            throw ValidationException::withMessages([
                'key' => __('capell-knowledge-base::generic.validation.collection_key_unique'),
            ]);
        }

        $collection->forceFill([
            'parent_id' => $data->parent?->is($collection) ? null : $data->parent?->getKey(),
            'key' => $key,
            'title' => $title,
            'slug' => $slug,
            'description' => $data->description === null ? null : trim($data->description),
            'sort_order' => max(0, $data->sortOrder),
            'is_public' => $data->isPublic,
        ])->save();

        return $collection->refresh()->load('parent');
    }

    private function normalizeSlug(string $value): string
    {
        return Str::slug(trim($value));
    }

    private function slugExists(KnowledgeBaseCollection $collection, string $slug): bool
    {
        return KnowledgeBaseCollection::query()
            ->where('slug', $slug)
            ->whereKeyNot($collection->getKey())
            ->exists();
    }

    private function keyExists(KnowledgeBaseCollection $collection, string $key): bool
    {
        return KnowledgeBaseCollection::query()
            ->where('key', $key)
            ->whereKeyNot($collection->getKey())
            ->exists();
    }
}
