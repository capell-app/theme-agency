<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Filament\Resources\Collections\Pages;

use Capell\KnowledgeBase\Actions\UpdateKnowledgeBaseCollectionAction;
use Capell\KnowledgeBase\Data\UpdateKnowledgeBaseCollectionData;
use Capell\KnowledgeBase\Filament\Resources\Collections\KnowledgeBaseCollectionResource;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

final class EditKnowledgeBaseCollection extends EditRecord
{
    protected static string $resource = KnowledgeBaseCollectionResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    #[Override]
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (! $record instanceof KnowledgeBaseCollection) {
            return $record;
        }

        $parentId = $data['parent_id'] ?? null;
        $parent = is_numeric($parentId)
            ? KnowledgeBaseCollection::query()->find((int) $parentId)
            : null;

        return UpdateKnowledgeBaseCollectionAction::run($record, new UpdateKnowledgeBaseCollectionData(
            title: $this->stringFromForm($data['title'] ?? null, ''),
            slug: $this->nullableStringFromForm($data['slug'] ?? null),
            key: $this->nullableStringFromForm($data['key'] ?? null),
            description: $this->nullableStringFromForm($data['description'] ?? null),
            parent: $parent,
            sortOrder: $this->integerFromForm($data['sort_order'] ?? null),
            isPublic: $this->booleanFromForm($data['is_public'] ?? true),
        ));
    }

    #[Override]
    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('capell-knowledge-base::generic.admin.actions.save_collection'));
    }

    private function booleanFromForm(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
    }

    private function integerFromForm(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    private function nullableStringFromForm(mixed $value): ?string
    {
        return is_scalar($value) ? trim((string) $value) : null;
    }

    private function stringFromForm(mixed $value, string $default): string
    {
        return $this->nullableStringFromForm($value) ?? $default;
    }
}
