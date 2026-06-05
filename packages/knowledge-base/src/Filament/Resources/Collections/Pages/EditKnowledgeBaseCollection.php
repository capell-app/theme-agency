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

        $parent = isset($data['parent_id']) && $data['parent_id'] !== null && $data['parent_id'] !== ''
            ? KnowledgeBaseCollection::query()->find((int) $data['parent_id'])
            : null;

        return UpdateKnowledgeBaseCollectionAction::run($record, new UpdateKnowledgeBaseCollectionData(
            title: (string) ($data['title'] ?? ''),
            slug: isset($data['slug']) ? (string) $data['slug'] : null,
            key: isset($data['key']) ? (string) $data['key'] : null,
            description: isset($data['description']) ? (string) $data['description'] : null,
            parent: $parent,
            sortOrder: isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
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
}
